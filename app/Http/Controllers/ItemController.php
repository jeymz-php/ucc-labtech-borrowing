<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Services\InventoryService;
use App\Support\CampusAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ItemController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {
        $this->middleware('permission:view items')->only(['index', 'show']);
        $this->middleware('permission:create items')->only(['create', 'store']);
        $this->middleware('permission:edit items')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:archive items')->only('destroy');
        $this->middleware('permission:restore items')->only(['archived', 'restore']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $categoryId = $request->integer('category');
        $status = $request->input('status');
        $stock = $request->input('stock');
        $campus = CampusAccess::canViewAllCampuses($request->user())
            ? null
            : CampusAccess::userCampus($request->user());

        $items = Item::query()
            ->with('category')
            ->withCount([
                'units as campus_quantity_total' => fn ($query) => $query
                    ->when($campus, fn ($unitQuery) => $unitQuery->where('campus', $campus))
                    ->where('availability_status', '!=', 'archived'),
                'units as campus_quantity_available' => fn ($query) => $query
                    ->when($campus, fn ($unitQuery) => $unitQuery->where('campus', $campus))
                    ->where('availability_status', 'available'),
            ])
            ->when($campus, fn ($query) => $query->whereHas(
                'units',
                fn ($unitQuery) => $unitQuery->where('campus', $campus)
            ))
            ->search($search)
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->when($stock === 'low', function ($query) {
                $query->havingRaw('campus_quantity_available <= minimum_stock');
            })
            ->when($stock === 'available', function ($query) {
                $query->having('campus_quantity_available', '>', 0);
            })
            ->when($stock === 'out', function ($query) {
                $query->having('campus_quantity_available', '=', 0);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        $campusItems = Item::query()
            ->when($campus, fn ($query) => $query->whereHas(
                'units',
                fn ($unitQuery) => $unitQuery->where('campus', $campus)
            ));

        $lowStockCount = Item::query()
            ->when($campus, fn ($query) => $query->whereHas(
                'units',
                fn ($unitQuery) => $unitQuery->where('campus', $campus)
            ))
            ->withCount([
                'units as campus_available_count' => fn ($query) => $query
                    ->when($campus, fn ($unitQuery) => $unitQuery->where('campus', $campus))
                    ->where('availability_status', 'available'),
            ])
            ->get()
            ->filter(fn (Item $item) => $item->campus_available_count <= $item->minimum_stock)
            ->count();

        $summary = [
            'total' => (clone $campusItems)->count(),
            'active' => (clone $campusItems)->where('status', 'active')->count(),
            'available_units' => ItemUnit::query()
                ->when($campus, fn ($query) => $query->where('campus', $campus))
                ->where('availability_status', 'available')
                ->count(),
            'low_stock' => $lowStockCount,
        ];

        return view('items.index', compact(
            'items',
            'categories',
            'summary',
            'search',
            'categoryId',
            'status',
            'stock'
        ));
    }

    public function create(Request $request): View
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('items.create', [
            'categories' => $categories,
            'campuses' => CampusAccess::options(),
            'selectedCampus' => CampusAccess::userCampus($request->user()),
            'canSelectCampus' => CampusAccess::canViewAllCampuses($request->user()),
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('items', 'public');
        }

        $itemData = [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? null,
            'model' => $validated['model'] ?? null,
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'minimum_stock' => $validated['minimum_stock'],
            'location' => $validated['location'] ?? null,
            'status' => $validated['status'],
        ];

        $unitsData = [];
        $unitCount = (int) $validated['initial_units_count'];

        for ($index = 0; $index < $unitCount; $index++) {
            $unitsData[] = [
                'campus' => CampusAccess::campusForWrite(
                    $request->user(),
                    $validated['campus'] ?? null
                ),
                'condition' => $validated['initial_condition'],
                'availability_status' => 'available',
                'acquisition_date' => $validated['acquisition_date'] ?? null,
                'acquisition_cost' => $validated['acquisition_cost'] ?? null,
                'remarks' => $validated['unit_remarks'] ?? null,
            ];
        }

        try {
            $item = $this->inventoryService->createItem(
                $itemData,
                $unitsData,
                $request->user()
            );
        } catch (Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            report($exception);

            return back()
                ->withInput()
                ->with('error', 'The item could not be created. Please try again.');
        }

        return redirect()
            ->route('items.show', $item)
            ->with('success', 'Item created successfully.');
    }

    public function show(Request $request, Item $item): View
    {
        $item->load(['category', 'creator', 'updater']);

        $campus = CampusAccess::canViewAllCampuses($request->user())
            ? ($request->filled('campus') && CampusAccess::isValid($request->input('campus'))
                ? $request->input('campus')
                : null)
            : CampusAccess::userCampus($request->user());

        $this->ensureItemVisible($request, $item);

        $units = $item->units()
            ->when($campus, fn ($query) => $query->where('campus', $campus))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $unitSummary = $item->units()
            ->when($campus, fn ($query) => $query->where('campus', $campus))
            ->selectRaw('availability_status, COUNT(*) as total')
            ->groupBy('availability_status')
            ->pluck('total', 'availability_status');

        return view('items.show', [
            'item' => $item,
            'units' => $units,
            'unitSummary' => $unitSummary,
            'campus' => $campus,
            'campuses' => CampusAccess::options(),
            'canSelectCampus' => CampusAccess::canViewAllCampuses($request->user()),
            'campusTotal' => $unitSummary->sum(),
            'campusAvailable' => (int) ($unitSummary['available'] ?? 0),
        ]);
    }

    public function edit(Request $request, Item $item): View
    {
        $this->ensureItemVisible($request, $item);

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('items.edit', compact('item', 'categories'));
    }

    public function update(
        UpdateItemRequest $request,
        Item $item
    ): RedirectResponse {
        $this->ensureItemVisible($request, $item);

        $validated = $request->validated();
        $oldImage = $item->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('items', 'public');
        }

        try {
            DB::transaction(function () use ($request, $validated, $item, $newImage) {
                $data = [
                    'category_id' => $validated['category_id'],
                    'name' => $validated['name'],
                    'brand' => $validated['brand'] ?? null,
                    'model' => $validated['model'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'minimum_stock' => $validated['minimum_stock'],
                    'location' => $validated['location'] ?? null,
                    'status' => $validated['status'],
                    'updated_by' => $request->user()->id,
                ];

                if ($newImage) {
                    $data['image'] = $newImage;
                } elseif ($validated['remove_image'] ?? false) {
                    $data['image'] = null;
                }

                $item->update($data);
            });
        } catch (Throwable $exception) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            report($exception);

            return back()->withInput()->with('error', 'The item could not be updated.');
        }

        if (($newImage || ($validated['remove_image'] ?? false)) && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()
            ->route('items.show', $item)
            ->with('success', 'Item updated successfully.');
    }

    public function toggleStatus(Request $request, Item $item): RedirectResponse
    {
        $this->ensureItemVisible($request, $item);

        $item->update([
            'status' => $item->status === 'active' ? 'inactive' : 'active',
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Item status updated successfully.');
    }

    public function destroy(Request $request, Item $item): RedirectResponse
    {
        $this->ensureItemVisible($request, $item);

        $targetUnits = $item->units()
            ->when(
                ! CampusAccess::canViewAllCampuses($request->user()),
                fn ($query) => $query->where(
                    'campus',
                    CampusAccess::userCampus($request->user())
                )
            );

        if ((clone $targetUnits)->whereIn('availability_status', ['reserved', 'borrowed'])->exists()) {
            return back()->with(
                'error',
                'This item cannot be archived while one or more units in your campus are reserved or borrowed.'
            );
        }

        DB::transaction(function () use ($request, $item, $targetUnits) {
            $targetUnits->get()->each(function (ItemUnit $unit) use ($request) {
                $unit->update([
                    'availability_status' => 'archived',
                    'updated_by' => $request->user()->id,
                ]);
                $unit->delete();
            });

            $item->refreshQuantities();

            if (! $item->units()->exists()) {
                $item->update([
                    'status' => 'archived',
                    'updated_by' => $request->user()->id,
                ]);
                $item->delete();
            }
        });

        return redirect()
            ->route('items.index')
            ->with('success', CampusAccess::canViewAllCampuses($request->user())
                ? 'Item archived successfully.'
                : 'The item units assigned to your campus were archived successfully.');
    }

    public function archived(Request $request): View
    {
        abort_unless(CampusAccess::canViewAllCampuses($request->user()), 403);

        $search = trim((string) $request->input('search'));

        $items = Item::onlyTrashed()
            ->with('category')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('item_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(12)
            ->withQueryString();

        return view('items.archived', compact('items', 'search'));
    }

    public function restore(Request $request, int $item): RedirectResponse
    {
        abort_unless(CampusAccess::canViewAllCampuses($request->user()), 403);

        $item = Item::onlyTrashed()->findOrFail($item);

        DB::transaction(function () use ($request, $item) {
            $item->restore();
            $item->units()->onlyTrashed()->restore();

            $item->update([
                'status' => 'inactive',
                'updated_by' => $request->user()->id,
            ]);

            $item->refreshQuantities();
        });

        return redirect()
            ->route('items.archived')
            ->with('success', 'Item restored successfully and set to inactive.');
    }

    private function ensureItemVisible(Request $request, Item $item): void
    {
        if (CampusAccess::canViewAllCampuses($request->user())) {
            return;
        }

        abort_unless(
            $item->units()
                ->where('campus', CampusAccess::userCampus($request->user()))
                ->exists(),
            403,
            'This item has no equipment units assigned to your campus.'
        );
    }
}
