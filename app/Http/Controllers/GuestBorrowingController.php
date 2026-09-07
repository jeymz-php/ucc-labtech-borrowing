<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestBorrowingRequest;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\GuestBorrower;
use App\Models\ItemUnit;
use App\Services\EquipmentScheduleService;
use App\Support\CampusAccess;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class GuestBorrowingController extends Controller
{
    public function __construct(
        private EquipmentScheduleService $equipmentSchedule
    ) {
    }

    public function create(Request $request): View
    {
        $campus = old('campus', $request->query('campus'));

        if (! CampusAccess::isValid($campus)) {
            $campus = CampusAccess::default();
        }

        $units = $campus ? $this->units($campus) : collect();
        $unitStates = $campus
            ? $this->equipmentSchedule->states($units, $campus)
            : collect();

        return view('guest-borrowings.create', [
            'campuses' => CampusAccess::options(),
            'selectedCampus' => $campus,
            'units' => $units,
            'unitStates' => $unitStates->all(),
        ]);
    }

    public function store(StoreGuestBorrowingRequest $request): RedirectResponse
    {
        $borrowing = DB::transaction(function () use ($request) {
            $campus = CampusAccess::validateRequestedCampus(
                $request->validated('campus')
            );

            $unitIds = collect($request->validated('item_unit_ids'))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $units = ItemUnit::query()
                ->with('item')
                ->where('campus', $campus)
                ->whereIn('id', $unitIds)
                ->lockForUpdate()
                ->get();

            if ($units->count() !== $unitIds->count()) {
                throw ValidationException::withMessages([
                    'item_unit_ids' => 'One or more selected units do not belong to the selected campus.',
                ]);
            }

            $unavailable = $units->first(
                fn (ItemUnit $unit) => ! $this->equipmentSchedule->isPhysicallyBorrowable($unit)
            );

            if ($unavailable) {
                throw ValidationException::withMessages([
                    'item_unit_ids' => ($unavailable->asset_number ?: $unavailable->item?->display_name)
                        .' is currently borrowed, under maintenance, lost, archived, or not in a borrowable condition.',
                ]);
            }

            if ($this->equipmentSchedule->conflictExists(
                $unitIds,
                $campus,
                $request->validated('borrow_at'),
                $request->validated('expected_return_at')
            )) {
                throw ValidationException::withMessages([
                    'item_unit_ids' => 'One or more selected units are reserved for the selected date or have an overlapping borrowing schedule.',
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
                'campus' => $campus,
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
                'campus' => $campus,
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
            'campus' => $borrowing->campus,
            'status' => $borrowing->status,
            'status_label' => ucfirst($borrowing->status),
            'admin_notes' => $borrowing->admin_notes,
            'rejection_reason' => $borrowing->rejection_reason,
            'approved_at' => $borrowing->approved_at?->toIso8601String(),
            'released_at' => $borrowing->released_at?->toIso8601String(),
            'returned_at' => $borrowing->returned_at?->toIso8601String(),
            'returned_to_office_at' => $borrowing->returned_to_office_at?->toIso8601String(),
            'updated_at' => $borrowing->updated_at?->toIso8601String(),
            'items' => $borrowing->items->map(fn ($line) => [
                'id' => $line->itemUnit?->id,
                'name' => $line->itemUnit?->item?->display_name,
                'asset_number' => $line->itemUnit?->asset_number,
                'campus' => $line->itemUnit?->campus,
                'availability_status' => $line->itemUnit?->availability_status,
            ])->values(),
        ]);
    }

    public function inventory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'campus' => ['required', Rule::in(CampusAccess::options())],
            'borrow_at' => ['nullable', 'date'],
            'expected_return_at' => ['nullable', 'date', 'after:borrow_at'],
        ]);

        $campus = $data['campus'];
        $borrowAt = filled($data['borrow_at'] ?? null)
            ? Carbon::parse($data['borrow_at'])
            : null;
        $expectedReturnAt = filled($data['expected_return_at'] ?? null)
            ? Carbon::parse($data['expected_return_at'])
            : null;

        $units = $this->units($campus);
        $states = $this->equipmentSchedule->states(
            $units,
            $campus,
            $borrowAt,
            $expectedReturnAt
        );

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'campus' => $campus,
            'units' => $units
                ->map(fn (ItemUnit $unit) => $this->unitPayload(
                    $unit,
                    $states->get($unit->id, [])
                ))
                ->values(),
        ]);
    }

    public function returnedToOffice(Request $request, string $token): RedirectResponse
    {
        $borrowing = $this->publicBorrowing($token);

        abort_unless(in_array($borrowing->status, ['released', 'overdue'], true), 422);

        if (! $borrowing->returned_to_office_at) {
            $borrowing->update([
                'returned_to_office_at' => now(),
            ]);
        }

        return back()->with(
            'success',
            'Return reported successfully. Please hand the equipment to LabTech staff. The item remains marked as Borrowed until staff completes the official return inspection.'
        );
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

    private function units(string $campus)
    {
        return ItemUnit::query()
            ->with(['item.category'])
            ->where('campus', $campus)
            ->where('availability_status', '!=', 'archived')
            ->whereHas('item', fn ($query) => $query->where('status', 'active'))
            ->orderByRaw("CASE availability_status WHEN 'available' THEN 1 WHEN 'reserved' THEN 2 WHEN 'borrowed' THEN 3 WHEN 'maintenance' THEN 4 WHEN 'lost' THEN 5 ELSE 6 END")
            ->orderBy('asset_number')
            ->get();
    }

    private function unitPayload(ItemUnit $unit, array $state = []): array
    {
        return [
            'id' => $unit->id,
            'name' => $unit->item?->display_name,
            'asset_number' => $unit->asset_number,
            'condition' => $unit->condition,
            'campus' => $unit->campus,
            'location' => $unit->location ?: $unit->item?->location,
            'availability_status' => $unit->availability_status,
            'display_status' => $state['display_status'] ?? $unit->availability_status,
            'reservation_note' => $state['reservation_note'] ?? null,
            'selectable' => (bool) ($state['selectable'] ?? $unit->isReservable()),
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
