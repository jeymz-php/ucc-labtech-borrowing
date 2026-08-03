<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Notifications\BorrowingStatusNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReservationCalendarController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('view reservation calendar'), 403);

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $items = Item::query()->orderBy('name')->get(['id', 'name', 'category_id']);
        $units = ItemUnit::query()->with('item:id,name')->orderBy('asset_number')->get(['id','item_id','asset_number','availability_status']);

        return view('calendar.index', compact('categories', 'items', 'units'));
    }

    public function events(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('view reservation calendar'), 403);

        $data = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'status' => ['nullable', 'in:pending,approved,released,overdue,returned,rejected,cancelled'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'item_id' => ['nullable', 'integer', 'exists:items,id'],
        ]);

        $borrowings = Borrowing::query()
            ->visibleTo($request->user())
            ->with(['user.roles','guestBorrower','items.itemUnit.item.category'])
            ->where('borrow_at', '<', $data['end'])
            ->where('expected_return_at', '>', $data['start'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $data['status']))
            ->when($request->filled('category_id'), fn ($q) => $q->whereHas('items.itemUnit.item', fn ($i) => $i->where('category_id', $data['category_id'])))
            ->when($request->filled('item_id'), fn ($q) => $q->whereHas('items.itemUnit', fn ($i) => $i->where('item_id', $data['item_id'])))
            ->orderBy('borrow_at')
            ->get();

        return response()->json($borrowings->map(function (Borrowing $borrowing) use ($request) {
            return [
                'id' => $borrowing->id,
                'code' => $borrowing->borrowing_code,
                'title' => $borrowing->borrowing_code.' · '.$borrowing->borrower_name,
                'start' => $borrowing->borrow_at?->toIso8601String(),
                'end' => $borrowing->expected_return_at?->toIso8601String(),
                'status' => $borrowing->status,
                'borrower' => $borrowing->borrower_name,
                'purpose' => $borrowing->purpose,
                'units' => $borrowing->items->map(fn ($line) => [
                    'asset_number' => $line->itemUnit?->asset_number,
                    'item' => $line->itemUnit?->item?->name,
                ])->values(),
                'url' => route('borrowings.show', $borrowing),
                'can_reschedule' => $request->user()->can('reschedule borrowings') && in_array($borrowing->status, ['pending','approved'], true),
            ];
        })->values());
    }

    public function availability(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('view reservation calendar'), 403);

        $data = $request->validate([
            'borrow_at' => ['required', 'date', 'after_or_equal:today'],
            'expected_return_at' => ['required', 'date', 'after:borrow_at'],
            'item_id' => ['nullable', 'integer', 'exists:items,id'],
        ]);

        $conflictedIds = DB::table('borrowing_items')
            ->join('borrowings', 'borrowings.id', '=', 'borrowing_items.borrowing_id')
            ->whereIn('borrowings.status', ['pending','approved','released','overdue'])
            ->where('borrowings.borrow_at', '<', $data['expected_return_at'])
            ->where('borrowings.expected_return_at', '>', $data['borrow_at'])
            ->pluck('borrowing_items.item_unit_id');

        $units = ItemUnit::query()
            ->with('item:id,name')
            ->when($request->filled('item_id'), fn ($q) => $q->where('item_id', $data['item_id']))
            ->whereNotIn('id', $conflictedIds)
            ->whereNotIn('availability_status', ['maintenance','lost','archived'])
            ->orderBy('asset_number')
            ->get(['id','item_id','asset_number','availability_status']);

        return response()->json([
            'count' => $units->count(),
            'units' => $units->map(fn ($unit) => [
                'id' => $unit->id,
                'asset_number' => $unit->asset_number,
                'item' => $unit->item?->name,
                'current_status' => $unit->availability_status,
            ])->values(),
        ]);
    }

    public function reschedule(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($request->user()->can('reschedule borrowings'), 403);
        abort_unless(in_array($borrowing->status, ['pending','approved'], true), 422);

        $data = $request->validate([
            'borrow_at' => ['required', 'date', 'after_or_equal:today'],
            'expected_return_at' => ['required', 'date', 'after:borrow_at'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $borrowing->load('items');
        $unitIds = $borrowing->items->pluck('item_unit_id');

        $conflict = DB::table('borrowing_items')
            ->join('borrowings', 'borrowings.id', '=', 'borrowing_items.borrowing_id')
            ->where('borrowings.id', '!=', $borrowing->id)
            ->whereIn('borrowing_items.item_unit_id', $unitIds)
            ->whereIn('borrowings.status', ['pending','approved','released','overdue'])
            ->where('borrowings.borrow_at', '<', $data['expected_return_at'])
            ->where('borrowings.expected_return_at', '>', $data['borrow_at'])
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages(['borrow_at' => 'The new schedule conflicts with another active reservation for one or more selected units.']);
        }

        $borrowing->update([
            'borrow_at' => $data['borrow_at'],
            'expected_return_at' => $data['expected_return_at'],
            'admin_notes' => trim(($borrowing->admin_notes ? $borrowing->admin_notes."\n" : '').'Rescheduled: '.$data['reason']),
        ]);

        $borrowing->user?->notify(new BorrowingStatusNotification(
            $borrowing,
            'Reservation rescheduled',
            $borrowing->borrowing_code.' was rescheduled to '.$borrowing->borrow_at->format('M d, Y h:i A').' until '.$borrowing->expected_return_at->format('M d, Y h:i A').'.'
        ));

        return back()->with('success', 'Reservation schedule updated successfully.');
    }

    public function pdf(Request $request): Response
    {
        abort_unless($request->user()->can('export reservation calendar'), 403);

        $data = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'status' => ['nullable', 'in:pending,approved,released,overdue,returned,rejected,cancelled'],
        ]);

        $borrowings = Borrowing::query()
            ->visibleTo($request->user())
            ->with(['user.roles','guestBorrower','items.itemUnit.item'])
            ->where('borrow_at', '<', $data['end'])
            ->where('expected_return_at', '>', $data['start'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $data['status']))
            ->orderBy('borrow_at')
            ->get();

        return Pdf::loadView('calendar.pdf', [
            'borrowings' => $borrowings,
            'start' => $data['start'],
            'end' => $data['end'],
            'preparedBy' => $request->user(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download('reservation-schedule-'.now()->format('Ymd-His').'.pdf');
    }
}
