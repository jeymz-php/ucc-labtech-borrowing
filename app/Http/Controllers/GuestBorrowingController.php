<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestBorrowingRequest;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\GuestBorrower;
use App\Models\ItemUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class GuestBorrowingController extends Controller
{
    public function create(): View
    {
        return view('guest-borrowings.create', [
            'units' => $this->units(),
        ]);
    }

    public function store(StoreGuestBorrowingRequest $request): RedirectResponse
    {
        $borrowing = DB::transaction(function () use ($request) {
            $unitIds = collect($request->validated('item_unit_ids'))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $units = ItemUnit::query()
                ->with('item')
                ->whereIn('id', $unitIds)
                ->lockForUpdate()
                ->get();

            if ($units->count() !== $unitIds->count()) {
                throw ValidationException::withMessages([
                    'item_unit_ids' => 'One or more selected equipment units could not be found.',
                ]);
            }

            $unavailable = $units->first(fn (ItemUnit $unit) => ! $unit->isBorrowable());

            if ($unavailable) {
                throw ValidationException::withMessages([
                    'item_unit_ids' => ($unavailable->asset_number ?: $unavailable->item?->display_name)
                        .' is no longer available. The equipment list has been refreshed.',
                ]);
            }

            $hasConflict = DB::table('borrowing_items')
                ->join('borrowings', 'borrowings.id', '=', 'borrowing_items.borrowing_id')
                ->whereIn('borrowing_items.item_unit_id', $unitIds)
                ->whereIn('borrowings.status', ['pending', 'approved', 'released', 'overdue'])
                ->where('borrowings.borrow_at', '<', $request->validated('expected_return_at'))
                ->where('borrowings.expected_return_at', '>', $request->validated('borrow_at'))
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'item_unit_ids' => 'One or more selected units have an overlapping borrowing schedule.',
                ]);
            }

            $role = $request->validated('role');

            $guest = GuestBorrower::create([
                'reference_code' => $this->nextGuestCode(),
                'role' => $role,
                'full_name' => trim($request->validated('full_name')),
                'id_number' => in_array($role, ['student', 'faculty_staff'], true)
                    ? trim((string) $request->validated('id_number'))
                    : null,
                'email' => Str::lower(trim($request->validated('email'))),
                'room' => trim($request->validated('room')),
                'program' => $role === 'student'
                    ? trim((string) $request->validated('program'))
                    : null,
                'year_level' => $role === 'student'
                    ? trim((string) $request->validated('year_level'))
                    : null,
                'section' => $role === 'student'
                    ? trim((string) $request->validated('section'))
                    : null,
                'department' => $role === 'professor'
                    ? trim((string) $request->validated('department'))
                    : null,
            ]);

            $borrowing = Borrowing::create([
                'borrowing_code' => $this->nextBorrowingCode(),
                'user_id' => null,
                'guest_borrower_id' => $guest->id,
                'public_token' => Str::random(64),
                'source' => 'guest',
                'purpose' => $request->validated('purpose'),
                'borrow_at' => $request->validated('borrow_at'),
                'expected_return_at' => $request->validated('expected_return_at'),
                'request_notes' => $request->validated('request_notes'),
                'status' => 'pending',
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'liability_accepted_at' => now(),
            ]);

            foreach ($units as $unit) {
                BorrowingItem::create([
                    'borrowing_id' => $borrowing->id,
                    'item_unit_id' => $unit->id,
                ]);

                $unit->update([
                    'availability_status' => 'reserved',
                ]);

                $unit->item?->refreshQuantities();
            }

            return $borrowing;
        });

        return redirect()
            ->route('guest-borrowings.track', $borrowing->public_token)
            ->with('success', 'Your guest borrowing request was submitted successfully.');
    }

    public function track(string $token): View
    {
        $borrowing = $this->publicBorrowing($token);

        return view('guest-borrowings.track', compact('borrowing'));
    }

    public function status(string $token): JsonResponse
    {
        $borrowing = $this->publicBorrowing($token);

        return response()->json([
            'code' => $borrowing->borrowing_code,
            'status' => $borrowing->status,
            'status_label' => ucfirst($borrowing->status),
            'admin_notes' => $borrowing->admin_notes,
            'rejection_reason' => $borrowing->rejection_reason,
            'approved_at' => $borrowing->approved_at?->toIso8601String(),
            'released_at' => $borrowing->released_at?->toIso8601String(),
            'returned_at' => $borrowing->returned_at?->toIso8601String(),
            'updated_at' => $borrowing->updated_at?->toIso8601String(),
            'items' => $borrowing->items->map(fn ($line) => [
                'id' => $line->itemUnit?->id,
                'name' => $line->itemUnit?->item?->display_name,
                'asset_number' => $line->itemUnit?->asset_number,
                'availability_status' => $line->itemUnit?->availability_status,
            ])->values(),
        ]);
    }

    public function inventory(): JsonResponse
    {
        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'units' => $this->units()->map(fn (ItemUnit $unit) => $this->unitPayload($unit))->values(),
        ]);
    }

    public function qr(string $token): Response
    {
        $borrowing = $this->publicBorrowing($token);

        $svg = QrCode::format('svg')
            ->size(800)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($borrowing->borrowing_code);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$borrowing->borrowing_code.'-guest-qr.svg"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function publicBorrowing(string $token): Borrowing
    {
        return Borrowing::query()
            ->where('public_token', $token)
            ->where('source', 'guest')
            ->with([
                'guestBorrower',
                'items.itemUnit.item.category',
                'approver',
                'releaser',
                'receiver',
            ])
            ->firstOrFail();
    }

    private function units()
    {
        return ItemUnit::query()
            ->with(['item.category'])
            ->where('availability_status', '!=', 'archived')
            ->whereHas('item', fn ($query) => $query->where('status', 'active'))
            ->orderByRaw("CASE availability_status WHEN 'available' THEN 1 WHEN 'reserved' THEN 2 WHEN 'borrowed' THEN 3 WHEN 'maintenance' THEN 4 WHEN 'lost' THEN 5 ELSE 6 END")
            ->orderBy('asset_number')
            ->get();
    }

    private function unitPayload(ItemUnit $unit): array
    {
        return [
            'id' => $unit->id,
            'name' => $unit->item?->display_name,
            'asset_number' => $unit->asset_number,
            'condition' => $unit->condition,
            'location' => $unit->location ?: $unit->item?->location,
            'availability_status' => $unit->availability_status,
            'selectable' => $unit->isBorrowable(),
        ];
    }

    private function nextGuestCode(): string
    {
        $prefix = 'GST-'.now()->format('Ym').'-';
        $last = GuestBorrower::query()
            ->where('reference_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('reference_code');

        $number = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }

    private function nextBorrowingCode(): string
    {
        $prefix = 'BRW-'.now()->format('Ym').'-';
        $last = Borrowing::query()
            ->where('borrowing_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('borrowing_code');

        $number = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
