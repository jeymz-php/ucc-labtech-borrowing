<?php

namespace App\Http\Controllers;

use App\Services\BarcodeScannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function __construct(
        protected BarcodeScannerService $scanner
    ) {}

    /**
     * Scanner page.
     */
    public function index()
    {
        return view('scanner.index');
    }

    /**
     * Scan a borrowing QR code.
     */
    public function borrowing(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $borrowing = $this->scanner->borrowing($request->code);

        if (! $borrowing) {
            return response()->json([
                'success' => false,
                'message' => 'Borrowing record not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,

            'borrowing' => [
                'id' => $borrowing->id,
                'code' => $borrowing->borrowing_code,
                'status' => $borrowing->status,
                'purpose' => $borrowing->purpose,

                'borrow_at' => optional($borrowing->borrow_at)?->toDateTimeString(),
                'expected_return_at' => optional($borrowing->expected_return_at)?->toDateTimeString(),

                'user' => [
                    'id' => $borrowing->user->id,
                    'name' => trim(
                        collect([
                            $borrowing->user->first_name,
                            $borrowing->user->middle_name,
                            $borrowing->user->last_name,
                            $borrowing->user->suffix,
                        ])->filter()->implode(' ')
                    ),
                ],

                'items' => $borrowing->items->map(function ($item) {
                    return [
                        'item_unit_id' => $item->item_unit_id,

                        'barcode' => $item->itemUnit->barcode_value,

                        'asset_number' => $item->itemUnit->asset_number,

                        'item_name' => $item->itemUnit->item->name,

                        'category' => $item->itemUnit->item->category->name,

                        'condition_out' => $item->condition_out,
                        'condition_in' => $item->condition_in,

                        'remarks_out' => $item->remarks_out,
                        'remarks_in' => $item->remarks_in,

                        'availability_status'
                            => $item->itemUnit->availability_status,
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * Scan an equipment barcode.
     */
    public function unit(Request $request): JsonResponse
    {
        $request->validate([
            'borrowing_id' => ['required', 'integer'],
            'barcode' => ['required', 'string'],
            'mode' => ['required', 'in:release,return'],
        ]);

        $borrowing = $this->scanner->borrowing(
            \App\Models\Borrowing::findOrFail(
                $request->borrowing_id
            )->borrowing_code
        );

        $unit = $this->scanner->itemUnit($request->barcode);

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Equipment barcode not found.',
            ], 404);
        }

        $validation = $request->mode === 'release'
            ? $this->scanner->validateRelease($borrowing, $unit)
            : $this->scanner->validateReturn($borrowing, $unit);

        if (! $validation['success']) {
            return response()->json($validation, 422);
        }

        return response()->json([
            'success' => true,
            'message' => $validation['message'],

            'unit' => [
                'id' => $unit->id,
                'barcode' => $unit->barcode_value,
                'asset_number' => $unit->asset_number,
                'item_name' => $unit->item->name,
                'category' => $unit->item->category->name,
                'condition' => $unit->condition,
                'availability_status' => $unit->availability_status,
            ],
        ]);
    }

    public function finishRelease(Request $request): JsonResponse
    {
        $request->validate([
            'borrowing_id' => ['required','integer'],
            'items' => ['required','array']
        ]);

        $borrowing = Borrowing::with('items')
            ->findOrFail($request->borrowing_id);

        $expected = $borrowing->items
            ->pluck('item_unit_id')
            ->sort()
            ->values();

        $received = collect($request->items)
            ->sort()
            ->values();

        if (!$expected->values()->all() == $received->values()->all()) {
            return response()->json([
                'success'=>false,
                'message'=>'Not all equipment has been scanned.'
            ],422);
        }

        // We'll connect this to your existing release logic next.

        return response()->json([
            'success'=>true,
            'message'=>'Verification complete.'
        ]);
    }

    public function finishReturn(Request $request): JsonResponse
    {
        $request->validate([
            'borrowing_id'=>['required','integer'],
            'items'=>['required','array']
        ]);

        $borrowing = Borrowing::with('items')
            ->findOrFail($request->borrowing_id);

        $expected = $borrowing->items
            ->pluck('item_unit_id')
            ->sort()
            ->values();

        $received = collect($request->items)
            ->sort()
            ->values();

        if (!$expected->values()->all() == $received->values()->all()) {
            return response()->json([
                'success'=>false,
                'message'=>'Not all equipment has been scanned.'
            ],422);
        }

        return response()->json([
            'success'=>true,
            'message'=>'Verification complete.'
        ]);
    }
}