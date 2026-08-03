<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('view reports'), 403);
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfMonth();

        $range = Borrowing::whereBetween('created_at', [$from, $to]);
        $summary = [
            'total' => (clone $range)->count(),
            'active' => (clone $range)->whereIn('status', ['approved','released','overdue'])->count(),
            'returned' => (clone $range)->where('status', 'returned')->count(),
            'overdue' => Borrowing::where('status', 'overdue')->count(),
            'available_units' => ItemUnit::where('availability_status', 'available')->count(),
            'maintenance_units' => ItemUnit::where('availability_status', 'maintenance')->count(),
            'lost_units' => ItemUnit::where('availability_status', 'lost')->count(),
        ];

        $statusBreakdown = (clone $range)->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->orderByDesc('total')->get();
        $topItems = Item::query()->select('items.id', 'items.name', DB::raw('COUNT(borrowing_items.id) as borrow_count'))
            ->join('item_units', 'item_units.item_id', '=', 'items.id')
            ->join('borrowing_items', 'borrowing_items.item_unit_id', '=', 'item_units.id')
            ->join('borrowings', 'borrowings.id', '=', 'borrowing_items.borrowing_id')
            ->whereBetween('borrowings.created_at', [$from, $to])
            ->groupBy('items.id', 'items.name')->orderByDesc('borrow_count')->limit(10)->get();
        $topBorrowers = Borrowing::query()
            ->with(['user.roles', 'guestBorrower'])
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy(fn (Borrowing $borrowing) => $borrowing->is_guest
                ? 'guest-'.mb_strtolower((string) $borrowing->borrower_email)
                : 'user-'.$borrowing->user_id)
            ->map(function ($group) {
                $first = $group->first();

                return (object) [
                    'full_name' => $first->borrower_name,
                    'borrow_count' => $group->count(),
                ];
            })
            ->sortByDesc('borrow_count')
            ->take(10)
            ->values();

        $recent = Borrowing::with(['user.roles','guestBorrower'])
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->limit(10)
            ->get();

        return view('reports.index', compact('from','to','summary','statusBreakdown','topItems','topBorrowers','recent'));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('export reports'), 403);
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfMonth();
        $rows = Borrowing::with(['user.roles','guestBorrower','items.itemUnit.item'])->whereBetween('created_at', [$from, $to])->oldest()->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Code','Borrower','ID Number','Purpose','Status','Borrow Date','Expected Return','Returned At','Equipment']);
            foreach ($rows as $borrowing) {
                fputcsv($out, [
                    $borrowing->borrowing_code,
                    $borrowing->borrower_name,
                    $borrowing->borrower_identifier,
                    $borrowing->purpose,
                    $borrowing->status,
                    optional($borrowing->borrow_at)->format('Y-m-d H:i'),
                    optional($borrowing->expected_return_at)->format('Y-m-d H:i'),
                    optional($borrowing->returned_at)->format('Y-m-d H:i'),
                    $borrowing->items->map(fn ($line) => $line->itemUnit->item->name.' ('.$line->itemUnit->asset_number.')')->join('; '),
                ]);
            }
            fclose($out);
        }, 'borrowing-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request)
    {
        abort_unless($request->user()->can('export reports'), 403);
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfMonth();
        $rows = Borrowing::with(['user.roles','guestBorrower','items.itemUnit.item'])
            ->whereBetween('created_at', [$from, $to])->oldest()->get();

        $summary = [
            'total' => $rows->count(),
            'active' => $rows->whereIn('status', ['approved', 'released', 'overdue'])->count(),
            'returned' => $rows->where('status', 'returned')->count(),
            'overdue' => $rows->where('status', 'overdue')->count(),
        ];

        return Pdf::loadView('reports.pdf', compact('from', 'to', 'rows', 'summary') + [
            'preparedBy' => $request->user(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')
          ->download('borrowing-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.pdf');
    }

}
