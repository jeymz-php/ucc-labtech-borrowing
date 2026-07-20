<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private InventoryCodeService $codeService
    ) {
    }

    public function createItem(
        array $itemData,
        array $unitsData,
        User $user
    ): Item {
        return DB::transaction(function () use (
            $itemData,
            $unitsData,
            $user
        ) {
            $itemData['created_by'] = $user->id;
            $itemData['updated_by'] = $user->id;
            $itemData['quantity_total'] = 0;
            $itemData['quantity_available'] = 0;

            $item = Item::create($itemData);

            $item->update([
                'item_code' => $this->codeService
                    ->generateItemCode($item),
            ]);

            foreach ($unitsData as $unitData) {
                $this->createUnit(
                    $item,
                    $unitData,
                    $user
                );
            }

            $item->refreshQuantities();

            return $item->fresh([
                'category',
                'units',
            ]);
        });
    }

    public function createUnit(
        Item $item,
        array $unitData,
        User $user
    ): ItemUnit {
        $unitData['item_id'] = $item->id;
        $unitData['created_by'] = $user->id;
        $unitData['updated_by'] = $user->id;

        $unit = ItemUnit::create($unitData);

        $assetNumber = $this->codeService
            ->generateAssetNumber(
                $unit,
                $item->category->asset_prefix
            );

        $unit->update([
            'asset_number' => $assetNumber,
            'barcode_value' => $assetNumber,
        ]);

        $item->refreshQuantities();

        return $unit->fresh();
    }

    public function updateUnitStatus(
        ItemUnit $unit,
        string $status,
        User $user
    ): ItemUnit {
        return DB::transaction(function () use (
            $unit,
            $status,
            $user
        ) {
            $unit->update([
                'availability_status' => $status,
                'updated_by' => $user->id,
            ]);

            $unit->item->refreshQuantities();

            return $unit->fresh();
        });
    }
}