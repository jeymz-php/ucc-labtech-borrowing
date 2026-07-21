<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBorrowingRequest;
use App\Models\Borrowing;
use App\Models\MaintenanceRecord;
use App\Models\BorrowingItem;
use App\Models\ItemUnit;
use App\Notifications\BorrowingStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BorrowingController extends Controller
{
    public function index(Request $request): View
    {
        $borrowings = Borrowing::query()
            ->visibleTo($request->user())
            ->with(['user','items.itemUnit.item'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('borrowing_code', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('first_name','like',"%{$search}%")->orWhere('last_name','like',"%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('borrowings.index', compact('borrowings'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('create borrowing requests'), 403);
        $units = ItemUnit::query()->borrowable()->with(['item.category'])->orderBy('asset_number')->get();
        return view('borrowings.create', compact('units'));
    }

    public function store(StoreBorrowingRequest $request): RedirectResponse
    {
        $borrowing = DB::transaction(function () use ($request) {
            $units = ItemUnit::query()->whereIn('id', $request->validated('item_unit_ids'))->lockForUpdate()->get();
            if ($units->count() !== count($request->validated('item_unit_ids')) || $units->contains(fn ($unit) => ! $unit->isBorrowable())) {
                throw ValidationException::withMessages(['item_unit_ids' => 'One or more selected units are no longer available. Refresh and try again.']);
            }

            $hasConflict = DB::table('borrowing_items')
                ->join('borrowings', 'borrowings.id', '=', 'borrowing_items.borrowing_id')
                ->whereIn('borrowing_items.item_unit_id', $units->pluck('id'))
                ->whereIn('borrowings.status', ['pending','approved','released','overdue'])
                ->where('borrowings.borrow_at', '<', $request->validated('expected_return_at'))
                ->where('borrowings.expected_return_at', '>', $request->validated('borrow_at'))
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages(['item_unit_ids' => 'One or more selected units already have an overlapping reservation.']);
            }

            $borrowing = Borrowing::create([
                'borrowing_code' => $this->nextCode(),
                'user_id' => $request->user()->id,
                'purpose' => $request->validated('purpose'),
                'borrow_at' => $request->validated('borrow_at'),
                'expected_return_at' => $request->validated('expected_return_at'),
                'request_notes' => $request->validated('request_notes'),
                'status' => 'pending',
            ]);

            foreach ($units as $unit) {
                BorrowingItem::create(['borrowing_id' => $borrowing->id, 'item_unit_id' => $unit->id]);
                $unit->update(['availability_status' => 'reserved', 'updated_by' => $request->user()->id]);
                $unit->item->refreshQuantities();
            }
            return $borrowing;
        });

        return redirect()->route('borrowings.show', $borrowing)->with('success', 'Borrowing request submitted successfully.');
    }

    public function show(Request $request, Borrowing $borrowing): View
    {
        abort_unless($request->user()->can('view all borrowings') || $borrowing->user_id === $request->user()->id, 403);
        $borrowing->load(['user','items.itemUnit.item.category','approver','releaser','receiver']);
        return view('borrowings.show', compact('borrowing'));
    }

    public function approve(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('approve borrowings'), 403);
        abort_unless($borrowing->status === 'pending', 422);
        $borrowing->update(['status'=>'approved','approved_by'=>$request->user()->id,'approved_at'=>now(),'admin_notes'=>$request->input('admin_notes')]);
        $borrowing->user?->notify(new BorrowingStatusNotification($borrowing, 'Borrowing approved', $borrowing->borrowing_code.' has been approved.'));
        return back()->with('success', 'Borrowing request approved.');
    }

    public function reject(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('reject borrowings'), 403);
        $data = $request->validate(['rejection_reason'=>['required','string','max:1500']]);
        abort_unless(in_array($borrowing->status, ['pending','approved'], true), 422);
        DB::transaction(function () use ($borrowing, $request, $data) {
            $borrowing->load('items.itemUnit.item');
            foreach ($borrowing->items as $line) {
                if ($line->itemUnit->availability_status === 'reserved') {
                    $line->itemUnit->update(['availability_status'=>'available','updated_by'=>$request->user()->id]);
                    $line->itemUnit->item->refreshQuantities();
                }
            }
            $borrowing->update(['status'=>'rejected','rejection_reason'=>$data['rejection_reason']]);
        });
        $borrowing->user?->notify(new BorrowingStatusNotification($borrowing, 'Borrowing rejected', $borrowing->borrowing_code.' was rejected: '.$data['rejection_reason']));
        return back()->with('success', 'Borrowing request rejected.');
    }

    public function release(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('release borrowings'), 403);
        abort_unless($borrowing->status === 'approved', 422);
        DB::transaction(function () use ($borrowing, $request) {
            $borrowing->load('items.itemUnit.item');
            foreach ($borrowing->items as $line) {
                $line->update(['condition_out'=>$line->itemUnit->condition]);
                $line->itemUnit->update(['availability_status'=>'borrowed','updated_by'=>$request->user()->id]);
                $line->itemUnit->item->refreshQuantities();
            }
            $borrowing->update(['status'=>'released','released_by'=>$request->user()->id,'released_at'=>now()]);
        });
        $borrowing->user?->notify(new BorrowingStatusNotification($borrowing, 'Equipment released', 'The equipment for '.$borrowing->borrowing_code.' has been released.'));
        return back()->with('success', 'Equipment released to borrower.');
    }

    public function receive(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('receive returns'), 403);
        abort_unless(in_array($borrowing->status, ['released','overdue'], true), 422);
        $data = $request->validate(['conditions'=>['required','array'],'conditions.*'=>['required','in:excellent,good,fair,damaged,for_repair,unserviceable'],'remarks'=>['nullable','array']]);
        DB::transaction(function () use ($borrowing, $request, $data) {
            $borrowing->load('items.itemUnit.item');
            foreach ($borrowing->items as $line) {
                $condition = $data['conditions'][$line->id] ?? $line->itemUnit->condition;
                $availability = in_array($condition, ['excellent','good','fair'], true) ? 'available' : 'maintenance';
                $line->update(['condition_in'=>$condition,'remarks_in'=>$data['remarks'][$line->id] ?? null]);
                $line->itemUnit->update(['condition'=>$condition,'availability_status'=>$availability,'updated_by'=>$request->user()->id]);

                if ($availability === 'maintenance' && ! MaintenanceRecord::where('item_unit_id', $line->itemUnit->id)->whereIn('status', ['reported','assigned','in_progress'])->exists()) {
                    $prefix = 'MNT-' . now()->format('Ym') . '-';
                    $last = MaintenanceRecord::where('maintenance_code', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('id')->value('maintenance_code');
                    $number = $last ? ((int) substr($last, -5)) + 1 : 1;

                    MaintenanceRecord::create([
                        'maintenance_code' => $prefix . str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                        'item_unit_id' => $line->itemUnit->id,
                        'borrowing_id' => $borrowing->id,
                        'reported_by' => $request->user()->id,
                        'priority' => in_array($condition, ['unserviceable','damaged'], true) ? 'high' : 'medium',
                        'issue_title' => 'Issue found during equipment return',
                        'issue_description' => $data['remarks'][$line->id] ?? 'The unit was returned with a condition requiring inspection or repair.',
                        'condition_before' => $condition,
                    ]);
                }

                $line->itemUnit->item->refreshQuantities();
            }
            $borrowing->update(['status'=>'returned','received_by'=>$request->user()->id,'returned_at'=>now()]);
        });
        $borrowing->user?->notify(new BorrowingStatusNotification($borrowing, 'Return completed', $borrowing->borrowing_code.' was returned successfully.'));
        return back()->with('success', 'Return processed successfully.');
    }

    public function cancel(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($borrowing->canBeCancelledBy($request->user()), 403);
        DB::transaction(function () use ($borrowing, $request) {
            $borrowing->load('items.itemUnit.item');
            foreach ($borrowing->items as $line) {
                if ($line->itemUnit->availability_status === 'reserved') {
                    $line->itemUnit->update(['availability_status'=>'available','updated_by'=>$request->user()->id]);
                    $line->itemUnit->item->refreshQuantities();
                }
            }
            $borrowing->update(['status'=>'cancelled']);
        });
        return redirect()->route('borrowings.index')->with('success', 'Borrowing request cancelled.');
    }


    public function extend(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('extend borrowing due dates'), 403);
        abort_unless(in_array($borrowing->status, ['approved','released','overdue'], true), 422);
        $data = $request->validate([
            'expected_return_at' => ['required','date','after:now'],
            'extension_reason' => ['required','string','max:1500'],
        ]);
        $borrowing->update([
            'expected_return_at' => $data['expected_return_at'],
            'extension_reason' => $data['extension_reason'],
            'extended_by' => $request->user()->id,
            'extended_at' => now(),
            'status' => $borrowing->status === 'overdue' ? 'released' : $borrowing->status,
        ]);
        $borrowing->user?->notify(new BorrowingStatusNotification($borrowing, 'Due date extended', $borrowing->borrowing_code.' is now due on '.$borrowing->expected_return_at->format('M d, Y h:i A').'.'));
        return back()->with('success', 'Expected return date extended.');
    }

    public function receipt(Request $request, Borrowing $borrowing): View
    {
        abort_unless($request->user()->can('view all borrowings') || $borrowing->user_id === $request->user()->id, 403);
        $borrowing->load(['user','items.itemUnit.item.category','approver','releaser','receiver']);
        return view('borrowings.receipt', compact('borrowing'));
    }

    private function nextCode(): string
    {
        $prefix = 'BRW-' . now()->format('Ym') . '-';
        $last = Borrowing::where('borrowing_code', 'like', $prefix.'%')->lockForUpdate()->orderByDesc('id')->value('borrowing_code');
        $number = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $prefix . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
