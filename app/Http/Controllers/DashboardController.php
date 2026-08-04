<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\User;
use App\Support\CampusAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $this->adminDashboard($user);
        }

        return $this->userDashboard($user);
    }

    private function adminDashboard(User $user): View
    {
        $campus = CampusAccess::canViewAllCampuses($user)
            ? null
            : CampusAccess::userCampus($user);

        $unitQuery = fn () => ItemUnit::query()
            ->when(
                $campus,
                fn (Builder $query) => $query->where('campus', $campus)
            );

        $userQuery = fn () => User::query()
            ->when(
                $campus,
                fn (Builder $query) => $query->where('campus', $campus)
            );

        $borrowingQuery = fn () => Borrowing::query()
            ->when(
                $campus,
                fn (Builder $query) => $query->where('campus', $campus)
            );

        $statistics = [
            'users' => $userQuery()->count(),
            'categories' => Category::count(),
            'items' => Item::whereHas(
                'units',
                fn ($query) => $query->when(
                    $campus,
                    fn ($unitQuery) => $unitQuery->where('campus', $campus)
                )
            )->count(),
            'units' => $unitQuery()->count(),
            'available' => $unitQuery()
                ->where('availability_status', 'available')
                ->count(),
            'borrowed' => $unitQuery()
                ->where('availability_status', 'borrowed')
                ->count(),
            'maintenance' => $unitQuery()
                ->where('availability_status', 'maintenance')
                ->count(),
            'lost' => $unitQuery()
                ->where('availability_status', 'lost')
                ->count(),
            'total_users' => $userQuery()->count(),
            'total_items' => Item::whereHas(
                'units',
                fn ($query) => $query->when(
                    $campus,
                    fn ($unitQuery) => $unitQuery->where('campus', $campus)
                )
            )->count(),
            'total_units' => $unitQuery()->count(),
            'total_categories' => Category::count(),
            'available_units' => $unitQuery()
                ->where('availability_status', 'available')
                ->count(),
            'borrowed_units' => $unitQuery()
                ->where('availability_status', 'borrowed')
                ->count(),
            'reserved_units' => $unitQuery()
                ->where('availability_status', 'reserved')
                ->count(),
            'maintenance_units' => $unitQuery()
                ->where('availability_status', 'maintenance')
                ->count(),
            'lost_units' => $unitQuery()
                ->where('availability_status', 'lost')
                ->count(),
            'pending_borrowings' => $borrowingQuery()
                ->where('status', 'pending')
                ->count(),
        ];

        $recentItems = Item::with('category')
            ->whereHas(
                'units',
                fn ($query) => $query->when(
                    $campus,
                    fn ($unitQuery) => $unitQuery->where('campus', $campus)
                )
            )
            ->latest()
            ->take(5)
            ->get();

        $recentUnits = $unitQuery()
            ->with('item')
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Campus-aware low-stock items
        |--------------------------------------------------------------------------
        |
        | MySQL with ONLY_FULL_GROUP_BY enabled rejects a HAVING comparison between
        | the generated withCount alias and items.minimum_stock. We retrieve the
        | campus-scoped counts first, then compare them in the collection. This
        | preserves the same low-stock behavior without an invalid HAVING clause.
        |
        */
        $lowStockItems = Item::query()
            ->with('category')
            ->when(
                $campus,
                fn (Builder $query) => $query->whereHas(
                    'units',
                    fn (Builder $unitQuery) => $unitQuery
                        ->where('campus', $campus)
                )
            )
            ->withCount([
                'units as campus_available_count' => fn (Builder $query) => $query
                    ->when(
                        $campus,
                        fn (Builder $unitQuery) => $unitQuery
                            ->where('campus', $campus)
                    )
                    ->where('availability_status', 'available'),
            ])
            ->get()
            ->filter(
                fn (Item $item) =>
                    (int) $item->campus_available_count
                    <= (int) $item->minimum_stock
            )
            ->sortBy('campus_available_count')
            ->take(5)
            ->values();

        return view('dashboard', compact(
            'statistics',
            'recentItems',
            'recentUnits',
            'lowStockItems',
            'campus'
        ));
    }

    private function userDashboard(User $user): View
    {
        $campus = CampusAccess::userCampus($user);

        $availableEquipment = ItemUnit::query()
            ->where('campus', $campus)
            ->where('availability_status', 'available')
            ->count();

        $totalEquipment = Item::whereHas(
            'units',
            fn ($query) => $query->where('campus', $campus)
        )->count();

        $recentItems = Item::with('category')
            ->whereHas(
                'units',
                fn ($query) => $query->where('campus', $campus)
            )
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.user', compact(
            'availableEquipment',
            'totalEquipment',
            'recentItems',
            'campus'
        ));
    }
}
