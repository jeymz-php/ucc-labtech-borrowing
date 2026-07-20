<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkStoreItemUnitRequest;
use App\Http\Requests\StoreItemUnitRequest;
use App\Http\Requests\UpdateItemUnitRequest;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Services\BarcodeService;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ItemUnitController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
        private BarcodeService $barcodeService
    ) {
        $this->middleware('permission:view items')->only(['index','show','lookup']);
        $this->middleware('permission:create items')->only(['create','store','bulkCreate','bulkStore']);
        $this->middleware('permission:edit items')->only(['edit','update']);
        $this->middleware('permission:archive items')->only('destroy');
        $this->middleware('permission:restore items')->only(['archived','restore']);
        $this->middleware('permission:print item barcodes')->only(['printOne','printBulk']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $condition = $request->input('condition');
        $status = $request->input('status');
        $itemId = $request->integer('item');

        $units = ItemUnit::query()
            ->with(['item.category'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('asset_number','like',"%{$search}%")
                        ->orWhere('barcode_value','like',"%{$search}%")
                        ->orWhere('serial_number','like',"%{$search}%")
                        ->orWhere('property_number','like',"%{$search}%")
                        ->orWhereHas('item', fn ($item) => $item->where('name','like',"%{$search}%")->orWhere('item_code','like',"%{$search}%"));
                });
            })
            ->when($itemId, fn ($query) => $query->where('item_id',$itemId))
            ->when(in_array($condition,['excellent','good','fair','damaged','for_repair','unserviceable'],true), fn ($query) => $query->where('condition',$condition))
            ->when(in_array($status,['available','reserved','borrowed','maintenance','lost'],true), fn ($query) => $query->where('availability_status',$status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $items = Item::active()->orderBy('name')->get(['id','item_code','name']);
        $summary = [
            'total' => ItemUnit::count(),
            'available' => ItemUnit::where('availability_status','available')->count(),
            'borrowed' => ItemUnit::where('availability_status','borrowed')->count(),
            'attention' => ItemUnit::whereIn('condition',['damaged','for_repair','unserviceable'])->count(),
        ];

        return view('item-units.index', compact('units','items','summary','search','condition','status','itemId'));
    }

    public function create(Item $item): View
    {
        $item->load('category');
        return view('item-units.create', compact('item'));
    }

    public function store(StoreItemUnitRequest $request, Item $item): RedirectResponse
    {
        $unit = $this->inventoryService->createUnit($item->load('category'), $request->validated(), $request->user());
        return redirect()->route('item-units.show',$unit)->with('success','Equipment unit added successfully.');
    }

    public function bulkCreate(Item $item): View
    {
        $item->load('category');
        return view('item-units.bulk-create', compact('item'));
    }

    public function bulkStore(BulkStoreItemUnitRequest $request, Item $item): RedirectResponse
    {
        $validated = $request->validated();
        $quantity = (int) $validated['quantity'];
        unset($validated['quantity']);

        DB::transaction(function () use ($item,$validated,$quantity,$request) {
            $item->load('category');
            for ($i=0; $i<$quantity; $i++) {
                $this->inventoryService->createUnit($item,$validated,$request->user());
            }
        });

        return redirect()->route('items.show',$item)->with('success',"{$quantity} equipment units added successfully.");
    }

    public function show(ItemUnit $itemUnit): View
    {
        $itemUnit->load(['item.category','creator','updater']);
        $barcodeSvg = $this->barcodeService->svg($itemUnit->barcode_value ?: $itemUnit->asset_number);
        return view('item-units.show', compact('itemUnit','barcodeSvg'));
    }

    public function edit(ItemUnit $itemUnit): View
    {
        abort_if(in_array($itemUnit->availability_status,['reserved','borrowed'],true), 422, 'Reserved or borrowed units cannot be edited.');
        $itemUnit->load('item.category');
        return view('item-units.edit', compact('itemUnit'));
    }

    public function update(UpdateItemUnitRequest $request, ItemUnit $itemUnit): RedirectResponse
    {
        if (in_array($itemUnit->availability_status,['reserved','borrowed'],true)) {
            return back()->with('error','Reserved or borrowed units cannot be edited.');
        }

        DB::transaction(function () use ($request,$itemUnit) {
            $itemUnit->update(array_merge($request->validated(), ['updated_by'=>$request->user()->id]));
            $itemUnit->item->refreshQuantities();
        });

        return redirect()->route('item-units.show',$itemUnit)->with('success','Equipment unit updated successfully.');
    }

    public function destroy(Request $request, ItemUnit $itemUnit): RedirectResponse
    {
        if (in_array($itemUnit->availability_status,['reserved','borrowed'],true)) {
            return back()->with('error','Reserved or borrowed units cannot be archived.');
        }

        $item = $itemUnit->item;
        DB::transaction(function () use ($request,$itemUnit,$item) {
            $itemUnit->update(['availability_status'=>'archived','updated_by'=>$request->user()->id]);
            $itemUnit->delete();
            $item->refreshQuantities();
        });

        return redirect()->route('items.show',$item)->with('success','Equipment unit archived successfully.');
    }

    public function archived(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $units = ItemUnit::onlyTrashed()->with('item.category')
            ->when($search, fn ($query) => $query->where('asset_number','like',"%{$search}%")->orWhere('serial_number','like',"%{$search}%")->orWhere('property_number','like',"%{$search}%"))
            ->latest('deleted_at')->paginate(15)->withQueryString();
        return view('item-units.archived', compact('units','search'));
    }

    public function restore(Request $request, int $itemUnit): RedirectResponse
    {
        $unit = ItemUnit::onlyTrashed()->findOrFail($itemUnit);
        DB::transaction(function () use ($request,$unit) {
            $unit->restore();
            $unit->update(['availability_status'=>'available','updated_by'=>$request->user()->id]);
            $unit->item->refreshQuantities();
        });
        return back()->with('success','Equipment unit restored successfully.');
    }

    public function lookup(Request $request): RedirectResponse|View
    {
        $value = strtoupper(trim((string) $request->input('barcode')));
        if ($value === '') return view('item-units.lookup');
        $unit = ItemUnit::where('barcode_value',$value)->orWhere('asset_number',$value)->first();
        if (! $unit) return back()->withInput()->with('error','No equipment unit matched that barcode or asset number.');
        return redirect()->route('item-units.show',$unit);
    }

    public function printOne(ItemUnit $itemUnit): View
    {
        $itemUnit->load('item.category');
        $barcodes = [$itemUnit->id => $this->barcodeService->svg($itemUnit->barcode_value ?: $itemUnit->asset_number, 64, 2)];
        return view('item-units.print.labels', ['units'=>collect([$itemUnit]),'barcodes'=>$barcodes]);
    }

    public function printBulk(Request $request): View
    {
        $validated = $request->validate(['units'=>['required','array','min:1','max:100'],'units.*'=>['integer','exists:item_units,id']]);
        $units = ItemUnit::with('item.category')->whereIn('id',$validated['units'])->get();
        $barcodes = $units->mapWithKeys(fn ($unit) => [$unit->id=>$this->barcodeService->svg($unit->barcode_value ?: $unit->asset_number,64,2)])->all();
        return view('item-units.print.labels', compact('units','barcodes'));
    }
}
