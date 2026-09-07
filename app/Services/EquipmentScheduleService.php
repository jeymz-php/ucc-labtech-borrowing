<?php

namespace App\Services;

use App\Models\ItemUnit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EquipmentScheduleService
{
    private const ACTIVE_STATUSES = [
        'pending',
        'approved',
        'released',
        'overdue',
    ];

    public function isPhysicallyBorrowable(ItemUnit $unit): bool
    {
        return in_array($unit->availability_status, ['available', 'reserved', 'borrowed'], true)
            && in_array($unit->condition, ['excellent', 'good', 'fair'], true);
    }

    public function conflictExists(
        Collection|array $unitIds,
        string $campus,
        Carbon|string $borrowAt,
        Carbon|string $expectedReturnAt,
        ?int $ignoreBorrowingId = null
    ): bool {
        $unitIds = collect($unitIds)->map(fn ($id) => (int) $id)->unique()->values();

        if ($unitIds->isEmpty()) {
            return false;
        }

        $borrowAt = $borrowAt instanceof Carbon ? $borrowAt->copy() : Carbon::parse($borrowAt);
        $expectedReturnAt = $expectedReturnAt instanceof Carbon
            ? $expectedReturnAt->copy()
            : Carbon::parse($expectedReturnAt);

        $rows = DB::table('borrowing_items')
            ->join('borrowings', 'borrowings.id', '=', 'borrowing_items.borrowing_id')
            ->whereIn('borrowing_items.item_unit_id', $unitIds)
            ->where('borrowings.campus', $campus)
            ->whereIn('borrowings.status', self::ACTIVE_STATUSES)
            ->when($ignoreBorrowingId, fn ($query) => $query->where('borrowings.id', '!=', $ignoreBorrowingId))
            ->get([
                'borrowing_items.item_unit_id',
                'borrowings.id as borrowing_id',
                'borrowings.borrowing_code',
                'borrowings.status',
                'borrowings.borrow_at',
                'borrowings.expected_return_at',
            ]);

        return $rows->contains(
            fn ($row) => $this->rowConflictsWithSchedule($row, $borrowAt, $expectedReturnAt)
        );
    }

    public function states(
        Collection $units,
        string $campus,
        ?Carbon $borrowAt = null,
        ?Carbon $expectedReturnAt = null,
        ?int $ignoreBorrowingId = null
    ): Collection {
        if ($units->isEmpty()) {
            return collect();
        }

        $rows = DB::table('borrowing_items')
            ->join('borrowings', 'borrowings.id', '=', 'borrowing_items.borrowing_id')
            ->whereIn('borrowing_items.item_unit_id', $units->pluck('id'))
            ->where('borrowings.campus', $campus)
            ->whereIn('borrowings.status', self::ACTIVE_STATUSES)
            ->when($ignoreBorrowingId, fn ($query) => $query->where('borrowings.id', '!=', $ignoreBorrowingId))
            ->orderBy('borrowings.borrow_at')
            ->get([
                'borrowing_items.item_unit_id',
                'borrowings.id as borrowing_id',
                'borrowings.borrowing_code',
                'borrowings.status',
                'borrowings.borrow_at',
                'borrowings.expected_return_at',
            ])
            ->groupBy('item_unit_id');

        return $units->mapWithKeys(function (ItemUnit $unit) use ($rows, $borrowAt, $expectedReturnAt) {
            $reservations = collect($rows->get($unit->id, collect()))
                ->map(function ($row) {
                    $row->borrow_at = Carbon::parse($row->borrow_at);
                    $row->expected_return_at = Carbon::parse($row->expected_return_at);
                    return $row;
                });

            $activeLoan = $reservations->first(
                fn ($row) => in_array($row->status, ['released', 'overdue'], true)
            );

            $conditionAllowsReservation = $this->isPhysicallyBorrowable($unit);
            $hasCompleteSchedule = $borrowAt !== null && $expectedReturnAt !== null;

            $selectedConflict = $hasCompleteSchedule
                ? $reservations->first(
                    fn ($row) => $this->rowConflictsWithSchedule($row, $borrowAt, $expectedReturnAt)
                )
                : null;

            $nextReservation = $reservations
                ->filter(fn ($row) => in_array($row->status, ['pending', 'approved'], true))
                ->sortBy('borrow_at')
                ->first();

            if ($unit->availability_status === 'borrowed' || $activeLoan) {
                $displayStatus = 'borrowed';
            } elseif (! $conditionAllowsReservation) {
                $displayStatus = ! in_array($unit->condition, ['excellent', 'good', 'fair'], true)
                    ? 'maintenance'
                    : $unit->availability_status;
            } elseif ($selectedConflict) {
                $displayStatus = 'reserved';
            } else {
                $displayStatus = 'available';
            }

            $selectable = $conditionAllowsReservation
                && $hasCompleteSchedule
                && $selectedConflict === null;

            // Available units remain selectable before the schedule is filled out.
            // Borrowed units require a complete future schedule so the system can
            // prove that the reservation starts after the current borrowing window.
            if (! $hasCompleteSchedule && ! $activeLoan && $unit->availability_status !== 'borrowed') {
                $selectable = $conditionAllowsReservation;
            }

            $reservationNote = null;

            if ($activeLoan) {
                $effectiveLoanEnd = $this->effectiveLoanEnd($activeLoan);

                if (! $hasCompleteSchedule) {
                    $reservationNote = sprintf(
                        'Currently borrowed until %s. You may still reserve this item by selecting a future date and time after the current borrowing period.',
                        $effectiveLoanEnd->format('M d, Y h:i A')
                    );
                } elseif ($selectedConflict && $selectedConflict->borrowing_id === $activeLoan->borrowing_id) {
                    $reservationNote = sprintf(
                        'Currently borrowed until %s. Your selected date and time conflicts with the current borrowing period.',
                        $effectiveLoanEnd->format('M d, Y h:i A')
                    );
                } else {
                    $reservationNote = sprintf(
                        'Currently borrowed until %s. This item can still be reserved for your selected schedule because there is no date/time conflict. Availability is subject to the current borrower returning it on time.',
                        $effectiveLoanEnd->format('M d, Y h:i A')
                    );
                }
            } elseif ($selectedConflict && in_array($selectedConflict->status, ['pending', 'approved'], true)) {
                $reservationNote = sprintf(
                    'Reserved for %s to %s. This reservation conflicts with your selected schedule.',
                    $selectedConflict->borrow_at->format('M d, Y h:i A'),
                    $selectedConflict->expected_return_at->format('M d, Y h:i A')
                );
            } elseif ($nextReservation) {
                $reservationNote = sprintf(
                    'Reserved for %s to %s. It is still available for your selected schedule.',
                    $nextReservation->borrow_at->format('M d, Y h:i A'),
                    $nextReservation->expected_return_at->format('M d, Y h:i A')
                );
            }

            return [
                $unit->id => [
                    'selectable' => $selectable,
                    'display_status' => $displayStatus,
                    'reservation_note' => $reservationNote,
                ],
            ];
        });
    }

    private function rowConflictsWithSchedule(object $row, Carbon $borrowAt, Carbon $expectedReturnAt): bool
    {
        $existingStart = $row->borrow_at instanceof Carbon
            ? $row->borrow_at->copy()
            : Carbon::parse($row->borrow_at);
        $existingEnd = $row->expected_return_at instanceof Carbon
            ? $row->expected_return_at->copy()
            : Carbon::parse($row->expected_return_at);

        if (in_array($row->status, ['released', 'overdue'], true)) {
            $existingEnd = $this->effectiveLoanEnd((object) [
                'status' => $row->status,
                'expected_return_at' => $existingEnd,
            ]);

            return $existingStart->lt($expectedReturnAt)
                && $existingEnd->gt($borrowAt);
        }

        if (! in_array($row->status, ['pending', 'approved'], true)) {
            return false;
        }

        // Reservations only conflict when their actual date/time ranges overlap.
        // Two reservations on the same day are allowed when the first one has
        // already ended before the next one starts (or vice versa).
        return $existingStart->lt($expectedReturnAt)
            && $existingEnd->gt($borrowAt);
    }

    private function effectiveLoanEnd(object $row): Carbon
    {
        $expectedReturn = $row->expected_return_at instanceof Carbon
            ? $row->expected_return_at->copy()
            : Carbon::parse($row->expected_return_at);

        if ($row->status === 'overdue' && $expectedReturn->isPast()) {
            return now();
        }

        return $expectedReturn;
    }
}
