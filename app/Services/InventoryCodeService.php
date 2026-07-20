<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemUnit;

class InventoryCodeService
{
    public function generateItemCode(Item $item): string
    {
        return sprintf(
            'ITM-%06d',
            $item->id
        );
    }

    public function generateAssetNumber(
        ItemUnit $unit,
        ?string $categoryCode = null
    ): string {
        $prefix = $this->normalizePrefix(
            $categoryCode ?? 'AST'
        );

        return sprintf(
            'AST-%s-%06d',
            $prefix,
            $unit->id
        );
    }

    public function generateBarcodeValue(
        ItemUnit $unit
    ): string {
        return $unit->asset_number;
    }

    private function normalizePrefix(string $value): string
    {
        $value = strtoupper($value);

        $value = preg_replace(
            '/[^A-Z0-9]/',
            '',
            $value
        );

        return substr($value ?: 'GEN', 0, 6);
    }
}