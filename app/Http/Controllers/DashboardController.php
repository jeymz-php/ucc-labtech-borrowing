<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->hasAnyRole([
            'super_admin',
            'admin',
        ])) {
            return $this->adminDashboard();
        }

        return $this->userDashboard();
    }

    private function adminDashboard(): View
    {
        $statistics = [
            'users' => User::count(),
            'categories' => Category::count(),
            'items' => Item::count(),
            'units' => ItemUnit::count(),

            'available' => ItemUnit::where(
                'availability_status',
                'available'
            )->count(),

            'borrowed' => ItemUnit::where(
                'availability_status',
                'borrowed'
            )->count(),

            'maintenance' => ItemUnit::where(
                'availability_status',
                'maintenance'
            )->count(),

            'lost' => ItemUnit::where(
                'availability_status',
                'lost'
            )->count(),
        ];

        $recentItems = Item::with('category')
            ->latest()
            ->take(5)
            ->get();

        $recentUnits = ItemUnit::with('item')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'statistics',
            'recentItems',
            'recentUnits'
        ));
    }

    private function userDashboard(): View
    {
        $availableEquipment = ItemUnit::where(
            'availability_status',
            'available'
        )->count();

        $totalEquipment = Item::count();

        $recentItems = Item::with('category')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.user', compact(
            'availableEquipment',
            'totalEquipment',
            'recentItems'
        ));
    }
}