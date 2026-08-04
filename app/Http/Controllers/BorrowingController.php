<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBorrowingRequest;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\ItemUnit;
use App\Models\MaintenanceRecord;
use App\Notifications\BorrowingStatusNotification;
use App\Support\CampusAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BorrowingController extends Controller
{
    public function index(Request $request): View
    {
        $borrowings = $this->paginatedBorrowings($request);

        return view('borrowings.index', [
            'borrowings' => $borrowings,
            'liveSignature' => $this->liveSignature($request),
        ]);
    }

    public function liveTable(Request $request): JsonResponse
    {
        $borrowings = $this->paginatedBorrowings($request);

        return response()->json([
            'html' => view('borrowings.partials.table', compact('borrowings'))->render(),
            'signature' => $this->liveSignature($request),
            'pending_count' => Borrowing::query()->visibleTo($request->user())->where('status', 'pending')->count(),
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function liveShow(Request $request, Borrowing $borrowing): JsonResponse
    {
        $this->ensureBorrowingVisible($request, $borrowing);

        return response()->json([
            'status' => $borrowing->status,
            'updated_at' => $borrowing->updated_at?->toIso8601String(),
            'approved_at' => $borrowing->approved_at?->toIso8601String(),
            'released_at' => $borrowing->released_at?->toIso8601String(),
            'returned_at' => $borrowing->returned_at?->toIso8601String(),
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();

        abort_unless($user->can('create borrowing requests'), 403);

        $campus = CampusAccess::userCampus($user);

        $units = ItemUnit::query()
            ->where('campus', $campus)
            ->borrowable()
            ->with(['item.category'])
            ->orderBy('asset_number')
            ->get();

        return view('borrowings.create', compact('units', 'campus'));
    }

    public function store(StoreBorrowingRequest $request): RedirectResponse
    {
        $borrowing = DB::transaction(function () use ($request) {
            $campus = CampusAccess::userCampus($request->user());

            $units = ItemUnit::query()
                ->where('campus', $campus)
                ->whereIn('id', $request->validated('item_unit_ids'))
                ->lockForUpdate()
                ->get();

            if (
                $units->count() !== count($request->validated('item_unit_ids'))
                || $units->contains(fn ($unit) => ! $unit->isBorrowable())
            ) {
                throw ValidationException::withMessages([
                    'item_unit_ids' => 'One or more selected units are no longer available. Refresh and try again.',
                ]);
            }

            $hasConflict = DB::table('borrowing_items')
                ->join('borrowings', 'borrowings.id', '=', 'borrowing_items.borrowing_id')
                ->whereIn('borrowing_items.item_unit_id', $units->pluck('id'))
                ->where('borrowings.campus', $campus)
                ->whereIn('borrowings.status', ['pending', 'approved', 'released', 'overdue'])
                ->where('borrowings.borrow_at', '<', $request->validated('expected_return_at'))
                ->where('borrowings.expected_return_at', '>', $request->validated('borrow_at'))
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'item_unit_ids' => 'One or more selected units already have an overlapping reservation.',
                ]);
            }

            $borrowing = Borrowing::create([
                'borrowing_code' => $this->nextCode(),
                'user_id' => $request->user()->id,
                'source' => 'account',
                'campus' => $campus,
                'purpose' => $request->validated('purpose'),
                'borrow_at' => $request->validated('borrow_at'),
                'expected_return_at' => $request->validated('expected_return_at'),
                'request_notes' => $request->validated('request_notes'),
                'status' => 'pending',
            ]);

            foreach ($units as $unit) {
                BorrowingItem::create([
                    'borrowing_id' => $borrowing->id,
                    'item_unit_id' => $unit->id,
                ]);

                $unit->update([
                    'availability_status' => 'reserved',
                    'updated_by' => $request->user()->id,
                ]);

                $unit->item->refreshQuantities();
            }

            return $borrowing;
        });

        return redirect()
            ->route('borrowings.show', $borrowing)
            ->with('success', 'Borrowing request submitted successfully.');
    }

    public function show(Request $request, Borrowing $borrowing): View
    {
        $this->ensureBorrowingVisible($request, $borrowing);

        $borrowing->load([
            'user.roles',
            'guestBorrower',
            'items.itemUnit.item.category',
            'approver',
            'releaser',
            'receiver',
        ]);

        return view('borrowings.show', compact('borrowing'));
    }

    public function approve(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('approve borrowings'), 403);
        $this->ensureBorrowingCampus($request, $borrowing);
        abort_unless($borrowing->status === 'pending', 422);

        $borrowing->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        $borrowing->user?->notify(new BorrowingStatusNotification(
            $borrowing,
            'Borrowing approved',
            $borrowing->borrowing_code.' has been approved.'
        ));

        return back()->with('success', 'Borrowing request approved.');
    }

    public function reject(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('reject borrowings'), 403);
        $this->ensureBorrowingCampus($request, $borrowing);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1500'],
        ]);

        abort_unless(in_array($borrowing->status, ['pending', 'approved'], true), 422);

        DB::transaction(function () use ($borrowing, $request, $data) {
            $borrowing->load('items.itemUnit.item');

            foreach ($borrowing->items as $line) {
                if ($line->itemUnit->availability_status === 'reserved') {
                    $line->itemUnit->update([
                        'availability_status' => 'available',
                        'updated_by' => $request->user()->id,
                    ]);
                    $line->itemUnit->item->refreshQuantities();
                }
            }

            $borrowing->update([
                'status' => 'rejected',
                'rejection_reason' => $data['rejection_reason'],
            ]);
        });

        $borrowing->user?->notify(new BorrowingStatusNotification(
            $borrowing,
            'Borrowing rejected',
            $borrowing->borrowing_code.' was rejected: '.$data['rejection_reason']
        ));

        return back()->with('success', 'Borrowing request rejected.');
    }

    public function release(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('release borrowings'), 403);
        $this->ensureBorrowingCampus($request, $borrowing);
        abort_unless($borrowing->status === 'approved', 422);

        DB::transaction(function () use ($borrowing, $request) {
            $borrowing->load('items.itemUnit.item');

            foreach ($borrowing->items as $line) {
                $line->update([
                    'condition_out' => $line->itemUnit->condition,
                ]);

                $line->itemUnit->update([
                    'availability_status' => 'borrowed',
                    'updated_by' => $request->user()->id,
                ]);

                $line->itemUnit->item->refreshQuantities();
            }

            $borrowing->update([
                'status' => 'released',
                'released_by' => $request->user()->id,
                'released_at' => now(),
            ]);
        });

        $borrowing->user?->notify(new BorrowingStatusNotification(
            $borrowing,
            'Equipment released',
            'The equipment for '.$borrowing->borrowing_code.' has been released.'
        ));

        return back()->with('success', 'Equipment released to borrower.');
    }

    public function receive(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('receive returns'), 403);
        $this->ensureBorrowingCampus($request, $borrowing);
        abort_unless(in_array($borrowing->status, ['released', 'overdue'], true), 422);

        $data = $request->validate([
            'conditions' => ['required', 'array'],
            'conditions.*' => ['required', 'in:excellent,good,fair,damaged,for_repair,unserviceable'],
            'remarks' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($borrowing, $request, $data) {
            $borrowing->load('items.itemUnit.item');

            foreach ($borrowing->items as $line) {
                $condition = $data['conditions'][$line->id] ?? $line->itemUnit->condition;
                $availability = in_array($condition, ['excellent', 'good', 'fair'], true)
                    ? 'available'
                    : 'maintenance';

                $line->update([
                    'condition_in' => $condition,
                    'remarks_in' => $data['remarks'][$line->id] ?? null,
                ]);

                $line->itemUnit->update([
                    'condition' => $condition,
                    'availability_status' => $availability,
                    'updated_by' => $request->user()->id,
                ]);

                if (
                    $availability === 'maintenance'
                    && ! MaintenanceRecord::where('item_unit_id', $line->itemUnit->id)
                        ->whereIn('status', ['reported', 'assigned', 'in_progress'])
                        ->exists()
                ) {
                    $prefix = 'MNT-'.now()->format('Ym').'-';
                    $last = MaintenanceRecord::where('maintenance_code', 'like', $prefix.'%')
                        ->lockForUpdate()
                        ->orderByDesc('id')
                        ->value('maintenance_code');
                    $number = $last ? ((int) substr($last, -5)) + 1 : 1;

                    MaintenanceRecord::create([
                        'maintenance_code' => $prefix.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                        'item_unit_id' => $line->itemUnit->id,
                        'borrowing_id' => $borrowing->id,
                        'reported_by' => $request->user()->id,
                        'priority' => in_array($condition, ['unserviceable', 'damaged'], true) ? 'high' : 'medium',
                        'issue_title' => 'Issue found during equipment return',
                        'issue_description' => $data['remarks'][$line->id]
                            ?? 'The unit was returned with a condition requiring inspection or repair.',
                        'condition_before' => $condition,
                    ]);
                }

                $line->itemUnit->item->refreshQuantities();
            }

            $borrowing->update([
                'status' => 'returned',
                'received_by' => $request->user()->id,
                'returned_at' => now(),
            ]);
        });

        $borrowing->user?->notify(new BorrowingStatusNotification(
            $borrowing,
            'Return completed',
            $borrowing->borrowing_code.' was returned successfully.'
        ));

        return back()->with('success', 'Return processed successfully.');
    }

    public function cancel(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($borrowing->canBeCancelledBy($request->user()), 403);
        $this->ensureBorrowingCampus($request, $borrowing);

        DB::transaction(function () use ($borrowing, $request) {
            $borrowing->load('items.itemUnit.item');

            foreach ($borrowing->items as $line) {
                if ($line->itemUnit->availability_status === 'reserved') {
                    $line->itemUnit->update([
                        'availability_status' => 'available',
                        'updated_by' => $request->user()->id,
                    ]);
                    $line->itemUnit->item->refreshQuantities();
                }
            }

            $borrowing->update(['status' => 'cancelled']);
        });

        return redirect()
            ->route('borrowings.index')
            ->with('success', 'Borrowing request cancelled.');
    }

    public function extend(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('extend borrowing due dates'), 403);
        $this->ensureBorrowingCampus($request, $borrowing);
        abort_unless(in_array($borrowing->status, ['approved', 'released', 'overdue'], true), 422);

        $data = $request->validate([
            'expected_return_at' => ['required', 'date', 'after:now'],
            'extension_reason' => ['required', 'string', 'max:1500'],
        ]);

        $borrowing->update([
            'expected_return_at' => $data['expected_return_at'],
            'extension_reason' => $data['extension_reason'],
            'extended_by' => $request->user()->id,
            'extended_at' => now(),
            'status' => $borrowing->status === 'overdue' ? 'released' : $borrowing->status,
        ]);

        $borrowing->user?->notify(new BorrowingStatusNotification(
            $borrowing,
            'Due date extended',
            $borrowing->borrowing_code.' is now due on '
                .$borrowing->expected_return_at->format('M d, Y h:i A').'.'
        ));

        return back()->with('success', 'Expected return date extended.');
    }

    public function receipt(Request $request, Borrowing $borrowing): View
    {
        $this->ensureBorrowingVisible($request, $borrowing);

        $borrowing->load([
            'user.roles',
            'guestBorrower',
            'items.itemUnit.item.category',
            'approver',
            'releaser',
            'receiver',
        ]);

        return view('borrowings.receipt', compact('borrowing'));
    }

    private function filteredQuery(Request $request): Builder
    {
        return Borrowing::query()
            ->visibleTo($request->user())
            ->with(['user.roles', 'guestBorrower', 'items.itemUnit.item'])
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = trim($request->string('search')->toString());

                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery
                        ->where('borrowing_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                            $userQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('id_number', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('guestBorrower', function (Builder $guestQuery) use ($search) {
                            $guestQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('id_number', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('reference_code', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function paginatedBorrowings(Request $request)
    {
        return $this->filteredQuery($request)
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    private function liveSignature(Request $request): string
    {
        $query = $this->filteredQuery($request);
        $count = (clone $query)->count();
        $latest = (clone $query)->max('updated_at');

        return sha1($count.'|'.$latest.'|'.$request->integer('page', 1));
    }

    private function ensureBorrowingVisible(Request $request, Borrowing $borrowing): void
    {
        if ($request->user()->can('view all borrowings')) {
            $this->ensureBorrowingCampus($request, $borrowing);
            return;
        }

        abort_unless($borrowing->user_id === $request->user()->id, 403);
    }

    private function ensureBorrowingCampus(Request $request, Borrowing $borrowing): void
    {
        CampusAccess::ensureCanAccess($request->user(), $borrowing->campus);
    }

    private function nextCode(): string
    {
        $prefix = 'BRW-'.now()->format('Ym').'-';
        $last = Borrowing::where('borrowing_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('borrowing_code');
        $number = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
