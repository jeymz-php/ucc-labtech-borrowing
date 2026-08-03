<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\MaintenanceRecord;
use App\Notifications\BorrowingStatusNotification;
use App\Services\BarcodeScannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScannerController extends Controller
{
    public function __construct(
        protected BarcodeScannerService $scanner
    ) {
    }

    public function index(): View
    {
        return view('scanner.index');
    }

    public function borrowing(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $borrowing = $this->scanner->borrowing($data['code']);

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

                'borrow_at' => $borrowing->borrow_at?->toDateTimeString(),

                'expected_return_at' => $borrowing
                    ->expected_return_at
                    ?->toDateTimeString(),

                'user' => [
                    'id' => $borrowing->user?->id,
                    'id_number' => $borrowing->borrower_identifier,
                    'name' => $borrowing->borrower_name,
                    'email' => $borrowing->borrower_email,
                    'role' => $borrowing->borrower_role_label,
                    'is_guest' => $borrowing->is_guest,
                ],

                'items' => $borrowing
                    ->items
                    ->map(function ($line) {
                        return [
                            'borrowing_item_id' => $line->id,

                            'item_unit_id' => $line->item_unit_id,

                            'barcode' => $line
                                ->itemUnit
                                ->barcode_value,

                            'asset_number' => $line
                                ->itemUnit
                                ->asset_number,

                            'item_name' => $line
                                ->itemUnit
                                ->item
                                ->name,

                            'category' => $line
                                ->itemUnit
                                ->item
                                ->category
                                ?->name,

                            'condition' => $line
                                ->itemUnit
                                ->condition,

                            'condition_out' => $line
                                ->condition_out,

                            'condition_in' => $line
                                ->condition_in,

                            'remarks_out' => $line
                                ->remarks_out,

                            'remarks_in' => $line
                                ->remarks_in,

                            'availability_status' => $line
                                ->itemUnit
                                ->availability_status,
                        ];
                    })
                    ->values(),
            ],
        ]);
    }

    public function unit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'borrowing_id' => [
                'required',
                'integer',
                'exists:borrowings,id',
            ],

            'barcode' => [
                'required',
                'string',
                'max:255',
            ],

            'mode' => [
                'required',
                'in:release,return',
            ],
        ]);

        $borrowingRecord = Borrowing::query()
            ->findOrFail($data['borrowing_id']);

        $borrowing = $this->scanner->borrowing(
            $borrowingRecord->borrowing_code
        );

        if (! $borrowing) {
            return response()->json([
                'success' => false,
                'message' => 'Borrowing record not found.',
            ], 404);
        }

        $unit = $this->scanner->itemUnit(
            $data['barcode']
        );

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Equipment barcode not found.',
            ], 404);
        }

        $validation = $data['mode'] === 'release'
            ? $this->scanner->validateRelease(
                $borrowing,
                $unit
            )
            : $this->scanner->validateReturn(
                $borrowing,
                $unit
            );

        if (! $validation['success']) {
            return response()->json(
                $validation,
                422
            );
        }

        $borrowingItem = $borrowing
            ->items
            ->firstWhere(
                'item_unit_id',
                $unit->id
            );

        return response()->json([
            'success' => true,
            'message' => $validation['message'],

            'unit' => [
                'id' => $unit->id,

                'borrowing_item_id' => $borrowingItem
                    ?->id,

                'barcode' => $unit
                    ->barcode_value,

                'asset_number' => $unit
                    ->asset_number,

                'item_name' => $unit
                    ->item
                    ->name,

                'category' => $unit
                    ->item
                    ->category
                    ?->name,

                'condition' => $unit
                    ->condition,

                'availability_status' => $unit
                    ->availability_status,
            ],
        ]);
    }

    public function finishRelease(
        Request $request
    ): JsonResponse {
        abort_unless(
            $request
                ->user()
                ->can('release borrowings'),
            403
        );

        $data = $request->validate([
            'borrowing_id' => [
                'required',
                'integer',
                'exists:borrowings,id',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*' => [
                'required',
                'integer',
                'distinct',
            ],
        ]);

        $borrowing = DB::transaction(
            function () use ($request, $data) {
                $borrowing = Borrowing::query()
                    ->whereKey(
                        $data['borrowing_id']
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $borrowing->load([
                    'user',
                    'items.itemUnit.item',
                ]);

                if (
                    $borrowing->status
                    !== 'approved'
                ) {
                    throw ValidationException::withMessages([
                        'borrowing_id' =>
                            'Only approved borrowings can be released.',
                    ]);
                }

                $this->assertCompleteScan(
                    $borrowing,
                    $data['items']
                );

                foreach (
                    $borrowing->items as $line
                ) {
                    if (
                        $line
                            ->itemUnit
                            ->availability_status
                        !== 'reserved'
                    ) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'The unit '
                                .$line
                                    ->itemUnit
                                    ->asset_number
                                .' is no longer reserved.',
                        ]);
                    }

                    $line->update([
                        'condition_out' => $line
                            ->itemUnit
                            ->condition,
                    ]);

                    $line->itemUnit->update([
                        'availability_status' =>
                            'borrowed',

                        'updated_by' => $request
                            ->user()
                            ->id,
                    ]);

                    $line
                        ->itemUnit
                        ->item
                        ->refreshQuantities();
                }

                $borrowing->update([
                    'status' => 'released',

                    'released_by' => $request
                        ->user()
                        ->id,

                    'released_at' => now(),
                ]);

                return $borrowing;
            }
        );

        $borrowing->user?->notify(
            new BorrowingStatusNotification(
                $borrowing,
                'Equipment released',
                'The equipment for '
                .$borrowing->borrowing_code
                .' has been released.'
            )
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Equipment released to borrower successfully.',

            'borrowing' => [
                'id' => $borrowing->id,

                'code' => $borrowing
                    ->borrowing_code,

                'status' => $borrowing
                    ->status,

                'released_at' => $borrowing
                    ->released_at
                    ?->toDateTimeString(),
            ],
        ]);
    }

    public function finishReturn(
        Request $request
    ): JsonResponse {
        abort_unless(
            $request
                ->user()
                ->can('receive returns'),
            403
        );

        $data = $request->validate([
            'borrowing_id' => [
                'required',
                'integer',
                'exists:borrowings,id',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*' => [
                'required',
                'integer',
                'distinct',
            ],

            'conditions' => [
                'required',
                'array',
            ],

            'conditions.*' => [
                'required',
                'in:excellent,good,fair,damaged,for_repair,unserviceable',
            ],

            'remarks' => [
                'nullable',
                'array',
            ],

            'remarks.*' => [
                'nullable',
                'string',
                'max:1500',
            ],
        ]);

        $borrowing = DB::transaction(
            function () use ($request, $data) {
                $borrowing = Borrowing::query()
                    ->whereKey(
                        $data['borrowing_id']
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $borrowing->load([
                    'user',
                    'items.itemUnit.item',
                ]);

                if (
                    ! in_array(
                        $borrowing->status,
                        [
                            'released',
                            'overdue',
                        ],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'borrowing_id' =>
                            'Only released or overdue borrowings can be returned.',
                    ]);
                }

                $this->assertCompleteScan(
                    $borrowing,
                    $data['items']
                );

                $this->assertCompleteInspection(
                    $borrowing,
                    $data['conditions']
                );

                $remarks = $data['remarks'] ?? [];

                foreach (
                    $borrowing->items as $line
                ) {
                    $condition = $data[
                        'conditions'
                    ][$line->id];

                    $remark = $remarks[
                        $line->id
                    ] ?? null;

                    $availability = in_array(
                        $condition,
                        [
                            'excellent',
                            'good',
                            'fair',
                        ],
                        true
                    )
                        ? 'available'
                        : 'maintenance';

                    $line->update([
                        'condition_in' =>
                            $condition,

                        'remarks_in' =>
                            $remark,
                    ]);

                    $line->itemUnit->update([
                        'condition' =>
                            $condition,

                        'availability_status' =>
                            $availability,

                        'updated_by' => $request
                            ->user()
                            ->id,
                    ]);

                    if (
                        $availability === 'maintenance'
                        && ! MaintenanceRecord::query()
                            ->where(
                                'item_unit_id',
                                $line
                                    ->itemUnit
                                    ->id
                            )
                            ->whereIn(
                                'status',
                                [
                                    'reported',
                                    'assigned',
                                    'in_progress',
                                ]
                            )
                            ->exists()
                    ) {
                        MaintenanceRecord::create([
                            'maintenance_code' =>
                                $this
                                    ->nextMaintenanceCode(),

                            'item_unit_id' => $line
                                ->itemUnit
                                ->id,

                            'borrowing_id' =>
                                $borrowing->id,

                            'reported_by' => $request
                                ->user()
                                ->id,

                            'priority' => in_array(
                                $condition,
                                [
                                    'unserviceable',
                                    'damaged',
                                ],
                                true
                            )
                                ? 'high'
                                : 'medium',

                            'issue_title' =>
                                'Issue found during equipment return',

                            'issue_description' =>
                                $remark
                                ?: 'The unit was returned with a condition requiring inspection or repair.',

                            'condition_before' =>
                                $condition,
                        ]);
                    }

                    $line
                        ->itemUnit
                        ->item
                        ->refreshQuantities();
                }

                $borrowing->update([
                    'status' => 'returned',

                    'received_by' => $request
                        ->user()
                        ->id,

                    'returned_at' => now(),
                ]);

                return $borrowing;
            }
        );

        $borrowing->user?->notify(
            new BorrowingStatusNotification(
                $borrowing,
                'Return completed',
                $borrowing->borrowing_code
                .' was returned successfully.'
            )
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Return processed successfully.',

            'borrowing' => [
                'id' => $borrowing->id,

                'code' => $borrowing
                    ->borrowing_code,

                'status' => $borrowing
                    ->status,

                'returned_at' => $borrowing
                    ->returned_at
                    ?->toDateTimeString(),
            ],
        ]);
    }

    private function assertCompleteScan(
        Borrowing $borrowing,
        array $scannedItemUnitIds
    ): void {
        $expected = $borrowing
            ->items
            ->pluck('item_unit_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->sort()
            ->values()
            ->all();

        $received = collect(
            $scannedItemUnitIds
        )
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($expected !== $received) {
            throw ValidationException::withMessages([
                'items' =>
                    'Not all required equipment has been scanned.',
            ]);
        }
    }

    private function assertCompleteInspection(
        Borrowing $borrowing,
        array $conditions
    ): void {
        $missingItems = $borrowing
            ->items
            ->filter(
                fn ($line) =>
                    ! array_key_exists(
                        $line->id,
                        $conditions
                    )
            )
            ->map(
                fn ($line) =>
                    $line
                        ->itemUnit
                        ->asset_number
                    ?: $line
                        ->itemUnit
                        ->barcode_value
            )
            ->values();

        if ($missingItems->isNotEmpty()) {
            throw ValidationException::withMessages([
                'conditions' =>
                    'Select a return condition for every equipment unit.',
            ]);
        }
    }

    private function nextMaintenanceCode(): string
    {
        $prefix = 'MNT-'
            .now()->format('Ym')
            .'-';

        $lastCode = MaintenanceRecord::query()
            ->where(
                'maintenance_code',
                'like',
                $prefix.'%'
            )
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('maintenance_code');

        $number = $lastCode
            ? ((int) substr(
                $lastCode,
                -5
            )) + 1
            : 1;

        return $prefix.str_pad(
            (string) $number,
            5,
            '0',
            STR_PAD_LEFT
        );
    }
}