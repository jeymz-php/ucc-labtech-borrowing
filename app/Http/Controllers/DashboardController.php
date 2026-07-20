<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $statistics = [
            'total_users' => User::where(
                'account_status',
                'active'
            )->count(),

            'total_categories' => Category::where(
                'status',
                'active'
            )->count(),

            'total_items' => Item::where(
                'status',
                'active'
            )->count(),

            'total_units' => ItemUnit::where(
                'availability_status',
                '!=',
                'archived'
            )->count(),

            'available_units' => ItemUnit::where(
                'availability_status',
                'available'
            )->count(),

            'borrowed_units' => ItemUnit::where(
                'availability_status',
                'borrowed'
            )->count(),

            'maintenance_units' => ItemUnit::where(
                'availability_status',
                'maintenance'
            )->count(),

            'lost_units' => ItemUnit::where(
                'availability_status',
                'lost'
            )->count(),
        ];

        $recentItems = Item::with('category')
            ->latest()
            ->limit(5)
            ->get();

        $recentUnits = ItemUnit::with([
            'item.category',
        ])
            ->latest()
            ->limit(5)
            ->get();

        $lowStockItems = Item::with('category')
            ->where('status', 'active')
            ->whereColumn(
                'quantity_available',
                '<=',
                'minimum_stock'
            )
            ->orderBy('quantity_available')
            ->limit(5)
            ->get();

        $availabilityBreakdown = ItemUnit::query()
            ->select(
                'availability_status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('availability_status')
            ->pluck('total', 'availability_status');

        return view('dashboard', compact(
            'statistics',
            'recentItems',
            'recentUnits',
            'lowStockItems',
            'availabilityBreakdown'
        ));
    }
}