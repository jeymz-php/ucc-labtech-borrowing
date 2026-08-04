<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkStoreItemUnitRequest;
use App\Http\Requests\StoreItemUnitRequest;
use App\Http\Requests\UpdateItemUnitRequest;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Services\BarcodeService;
use App\Services\InventoryService;
use App\Support\CampusAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemUnitController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
        private BarcodeService $barcodeService
    ) {
        $this->middleware('permission:view items')->only(['index', 'show', 'lookup']);
        $this->middleware('permission:create items')->only(['create', 'store', 'bulkCreate', 'bulkStore']);
        $this->middleware('permission:edit items')->only(['edit', 'update']);
        $this->middleware('permission:archive items')->only('destroy');
        $this->middleware('permission:restore items')->only(['archived', 'restore']);
        $this->middleware('permission:print item barcodes')->only(['printOne', 'printBulk']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $condition = $request->input('condition');
        $status = $request->input('status');
        $itemId = $request->integer('item');
        $campus = $this->selectedCampus($request);

        $baseQuery = fn () => $this->campusQuery(ItemUnit::query(), $request, $campus);

        $units = $baseQuery()
            ->with(['item.category'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('asset_number', 'like', "%{$search}%")
                        ->orWhere('barcode_value', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('property_number', 'like', "%{$search}%")
                        ->orWhereHas('item', fn ($item) => $item
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%"));
                });
            })
            ->when($itemId, fn ($query) => $query->where('item_id', $itemId))
            ->when(
                in_array($condition, ['excellent', 'good', 'fair', 'damaged', 'for_repair', 'unserviceable'], true),
                fn ($query) => $query->where('condition', $condition)
            )
            ->when(
                in_array($status, ['available', 'reserved', 'borrowed', 'maintenance', 'lost'], true),
                fn ($query) => $query->where('availability_status', $status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $items = Item::active()
            ->whereHas('units', fn ($query) => $this->campusQuery($query, $request, $campus))
            ->orderBy('name')
            ->get(['id', 'item_code', 'name']);

        $summary = [
            'total' => $baseQuery()->count(),
            'available' => $baseQuery()->where('availability_status', 'available')->count(),
            'borrowed' => $baseQuery()->where('availability_status', 'borrowed')->count(),
            'attention' => $baseQuery()->whereIn('condition', ['damaged', 'for_repair', 'unserviceable'])->count(),
        ];

        return view('item-units.index', [
            'units' => $units,
            'items' => $items,
            'summary' => $summary,
            'search' => $search,
            'condition' => $condition,
            'status' => $status,
            'itemId' => $itemId,
            'campus' => $campus,
            'campuses' => CampusAccess::options(),
            'canSelectCampus' => CampusAccess::canViewAllCampuses($request->user()),
        ]);
    }

    public function create(Request $request, Item $item): View
    {
        $item->load('category');

        return view('item-units.create', [
            'item' => $item,
            'campuses' => CampusAccess::options(),
            'selectedCampus' => CampusAccess::userCampus($request->user()),
            'canSelectCampus' => CampusAccess::canViewAllCampuses($request->user()),
        ]);
    }

    public function store(StoreItemUnitRequest $request, Item $item): RedirectResponse
    {
        $data = $request->validated();
        $data['campus'] = CampusAccess::campusForWrite(
            $request->user(),
            $data['campus'] ?? null
        );

        $unit = $this->inventoryService->createUnit(
            $item->load('category'),
            $data,
            $request->user()
        );

        return redirect()
            ->route('item-units.show', $unit)
            ->with('success', 'Equipment unit added successfully.');
    }

    public function bulkCreate(Request $request, Item $item): View
    {
        $item->load('category');

        return view('item-units.bulk-create', [
            'item' => $item,
            'campuses' => CampusAccess::options(),
            'selectedCampus' => CampusAccess::userCampus($request->user()),
            'canSelectCampus' => CampusAccess::canViewAllCampuses($request->user()),
        ]);
    }

    public function bulkStore(BulkStoreItemUnitRequest $request, Item $item): RedirectResponse
    {
        $validated = $request->validated();
        $quantity = (int) $validated['quantity'];
        unset($validated['quantity']);

        $validated['campus'] = CampusAccess::campusForWrite(
            $request->user(),
            $validated['campus'] ?? null
        );

        DB::transaction(function () use ($item, $validated, $quantity, $request) {
            $item->load('category');

            for ($index = 0; $index < $quantity; $index++) {
                $this->inventoryService->createUnit(
                    $item,
                    $validated,
                    $request->user()
                );
            }
        });

        return redirect()
            ->route('items.show', $item)
            ->with('success', "{$quantity} equipment units added successfully for {$validated['campus']}.");
    }

    public function show(Request $request, ItemUnit $itemUnit): View
    {
        $this->ensureCampus($request, $itemUnit);

        $itemUnit->load(['item.category', 'creator', 'updater']);
        $barcodeSvg = $this->barcodeService->svg(
            $itemUnit->barcode_value ?: $itemUnit->asset_number
        );

        return view('item-units.show', compact('itemUnit', 'barcodeSvg'));
    }

    public function edit(Request $request, ItemUnit $itemUnit): View
    {
        $this->ensureCampus($request, $itemUnit);

        abort_if(
            in_array($itemUnit->availability_status, ['reserved', 'borrowed'], true),
            422,
            'Reserved or borrowed units cannot be edited.'
        );

        $itemUnit->load('item.category');

        return view('item-units.edit', [
            'itemUnit' => $itemUnit,
            'campuses' => CampusAccess::options(),
            'selectedCampus' => $itemUnit->campus,
            'canSelectCampus' => CampusAccess::canViewAllCampuses($request->user()),
        ]);
    }

    public function update(UpdateItemUnitRequest $request, ItemUnit $itemUnit): RedirectResponse
    {
        $this->ensureCampus($request, $itemUnit);

        if (in_array($itemUnit->availability_status, ['reserved', 'borrowed'], true)) {
            return back()->with('error', 'Reserved or borrowed units cannot be edited.');
        }

        $data = $request->validated();
        $data['campus'] = CampusAccess::campusForWrite(
            $request->user(),
            $data['campus'] ?? $itemUnit->campus
        );

        DB::transaction(function () use ($request, $itemUnit, $data) {
            $itemUnit->update($data + [
                'updated_by' => $request->user()->id,
            ]);

            $itemUnit->item->refreshQuantities();
        });

        return redirect()
            ->route('item-units.show', $itemUnit)
            ->with('success', 'Equipment unit updated successfully.');
    }

    public function destroy(Request $request, ItemUnit $itemUnit): RedirectResponse
    {
        $this->ensureCampus($request, $itemUnit);

        if (in_array($itemUnit->availability_status, ['reserved', 'borrowed'], true)) {
            return back()->with('error', 'Reserved or borrowed units cannot be archived.');
        }

        $item = $itemUnit->item;

        DB::transaction(function () use ($request, $itemUnit, $item) {
            $itemUnit->update([
                'availability_status' => 'archived',
                'updated_by' => $request->user()->id,
            ]);
            $itemUnit->delete();
            $item->refreshQuantities();
        });

        return redirect()
            ->route('items.show', $item)
            ->with('success', 'Equipment unit archived successfully.');
    }

    public function archived(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $campus = $this->selectedCampus($request);

        $units = $this->campusQuery(ItemUnit::onlyTrashed(), $request, $campus)
            ->with('item.category')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('asset_number', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('property_number', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('item-units.archived', [
            'units' => $units,
            'search' => $search,
            'campus' => $campus,
            'campuses' => CampusAccess::options(),
            'canSelectCampus' => CampusAccess::canViewAllCampuses($request->user()),
        ]);
    }

    public function restore(Request $request, int $itemUnit): RedirectResponse
    {
        $unit = ItemUnit::onlyTrashed()->findOrFail($itemUnit);
        $this->ensureCampus($request, $unit);

        DB::transaction(function () use ($request, $unit) {
            $unit->restore();
            $unit->update([
                'availability_status' => 'available',
                'updated_by' => $request->user()->id,
            ]);
            $unit->item->refreshQuantities();
        });

        return back()->with('success', 'Equipment unit restored successfully.');
    }

    public function lookup(Request $request): RedirectResponse|View
    {
        $value = strtoupper(trim((string) $request->input('barcode')));

        if ($value === '') {
            return view('item-units.lookup');
        }

        $unit = ItemUnit::query()
            ->visibleTo($request->user())
            ->where(function ($query) use ($value) {
                $query->where('barcode_value', $value)
                    ->orWhere('asset_number', $value);
            })
            ->first();

        if (! $unit) {
            return back()->withInput()->with(
                'error',
                'No equipment unit in your campus matched that barcode or asset number.'
            );
        }

        return redirect()->route('item-units.show', $unit);
    }

    public function printOne(Request $request, ItemUnit $itemUnit): View
    {
        $this->ensureCampus($request, $itemUnit);
        $itemUnit->load('item.category');

        $barcodes = [
            $itemUnit->id => $this->barcodeService->svg(
                $itemUnit->barcode_value ?: $itemUnit->asset_number,
                64,
                2
            ),
        ];

        return view('item-units.print.labels', [
            'units' => collect([$itemUnit]),
            'barcodes' => $barcodes,
        ]);
    }

    public function printBulk(Request $request): View
    {
        $validated = $request->validate([
            'units' => ['required', 'array', 'min:1', 'max:100'],
            'units.*' => ['integer', 'exists:item_units,id'],
        ]);

        $units = ItemUnit::query()
            ->visibleTo($request->user())
            ->with('item.category')
            ->whereIn('id', $validated['units'])
            ->get();

        abort_unless($units->count() === count(array_unique($validated['units'])), 403);

        $barcodes = $units->mapWithKeys(fn ($unit) => [
            $unit->id => $this->barcodeService->svg(
                $unit->barcode_value ?: $unit->asset_number,
                64,
                2
            ),
        ])->all();

        return view('item-units.print.labels', compact('units', 'barcodes'));
    }

    private function selectedCampus(Request $request): ?string
    {
        if (! CampusAccess::canViewAllCampuses($request->user())) {
            return CampusAccess::userCampus($request->user());
        }

        $requested = $request->string('campus')->toString();

        return CampusAccess::isValid($requested) ? $requested : null;
    }

    private function campusQuery(
        Builder $query,
        Request $request,
        ?string $campus
    ): Builder {
        if (CampusAccess::canViewAllCampuses($request->user())) {
            return $campus ? $query->where('campus', $campus) : $query;
        }

        return $query->where('campus', CampusAccess::userCampus($request->user()));
    }

    private function ensureCampus(Request $request, ItemUnit $unit): void
    {
        CampusAccess::ensureCanAccess($request->user(), $unit->campus);
    }
}
