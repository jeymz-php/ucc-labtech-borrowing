<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Category;
use App\Models\Item;
use App\Services\InventoryService;
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

        $items = Item::query()
            ->with('category')
            ->search($search)
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->when($stock === 'low', function ($query) {
                $query->whereColumn('quantity_available', '<=', 'minimum_stock');
            })
            ->when($stock === 'available', function ($query) {
                $query->where('quantity_available', '>', 0);
            })
            ->when($stock === 'out', function ($query) {
                $query->where('quantity_available', 0);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        $summary = [
            'total' => Item::count(),
            'active' => Item::where('status', 'active')->count(),
            'available_units' => Item::sum('quantity_available'),
            'low_stock' => Item::whereColumn('quantity_available', '<=', 'minimum_stock')->count(),
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

    public function create(): View
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('items.create', compact('categories'));
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

    public function show(Item $item): View
    {
        $item->load(['category', 'creator', 'updater']);

        $units = $item->units()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $unitSummary = $item->units()
            ->selectRaw('availability_status, COUNT(*) as total')
            ->groupBy('availability_status')
            ->pluck('total', 'availability_status');

        return view('items.show', compact('item', 'units', 'unitSummary'));
    }

    public function edit(Item $item): View
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('items.edit', compact('item', 'categories'));
    }

    public function update(
        UpdateItemRequest $request,
        Item $item
    ): RedirectResponse {
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
        $item->update([
            'status' => $item->status === 'active' ? 'inactive' : 'active',
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Item status updated successfully.');
    }

    public function destroy(Request $request, Item $item): RedirectResponse
    {
        $hasUnavailableUnits = $item->units()
            ->whereIn('availability_status', ['reserved', 'borrowed'])
            ->exists();

        if ($hasUnavailableUnits) {
            return back()->with(
                'error',
                'This item cannot be archived while one or more units are reserved or borrowed.'
            );
        }

        DB::transaction(function () use ($request, $item) {
            $item->units()->get()->each->delete();

            $item->update([
                'status' => 'archived',
                'quantity_total' => 0,
                'quantity_available' => 0,
                'updated_by' => $request->user()->id,
            ]);

            $item->delete();
        });

        return redirect()
            ->route('items.index')
            ->with('success', 'Item archived successfully.');
    }

    public function archived(Request $request): View
    {
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
}
