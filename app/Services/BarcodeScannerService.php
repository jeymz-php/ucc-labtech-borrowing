<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\ItemUnit;

class BarcodeScannerService
{
    public function borrowing(string $code): ?Borrowing
    {
        return Borrowing::with([
            'user',
            'items.itemUnit.item.category',
            'approver',
            'releaser',
            'receiver',
        ])
        ->where('borrowing_code', trim($code))
        ->first();
    }

    public function itemUnit(string $barcode): ?ItemUnit
    {
        return ItemUnit::with('item.category')
            ->where('barcode_value', trim($barcode))
            ->first();
    }

    public function validateRelease(
        Borrowing $borrowing,
        ItemUnit $unit
    ): array {

        if ($borrowing->status !== 'approved') {
            return [
                'success' => false,
                'message' => 'Borrowing is not approved.'
            ];
        }

        if ($unit->availability_status !== 'reserved') {
            return [
                'success' => false,
                'message' => 'Equipment is not reserved.'
            ];
        }

        $exists = $borrowing->items
            ->contains(fn($i) => $i->item_unit_id == $unit->id);

        if (! $exists) {
            return [
                'success' => false,
                'message' => 'Wrong equipment scanned.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Equipment verified.'
        ];
    }

    public function validateReturn(
        Borrowing $borrowing,
        ItemUnit $unit
    ): array {

        if (! in_array($borrowing->status, [
            'released',
            'overdue'
        ])) {

            return [
                'success'=>false,
                'message'=>'Borrowing is not active.'
            ];
        }

        if ($unit->availability_status !== 'borrowed') {

            return [
                'success'=>false,
                'message'=>'Item is not currently borrowed.'
            ];
        }

        $exists = $borrowing->items
            ->contains(fn($i)=>$i->item_unit_id==$unit->id);

        if (! $exists) {

            return [
                'success'=>false,
                'message'=>'Wrong equipment scanned.'
            ];
        }

        return [
            'success'=>true,
            'message'=>'Equipment verified.'
        ];
    }
}