<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\User;
use App\Notifications\BorrowingStatusNotification;

class BorrowingNotificationService
{
    public function notifyStaffOfNewRequest(Borrowing $borrowing, ?int $excludeUserId = null): void
    {
        $borrowing->loadMissing(['user', 'guestBorrower']);

        $borrowerName = $borrowing->borrower_name ?: 'Guest borrower';
        $title = $borrowing->source === 'guest'
            ? 'New guest borrowing request'
            : 'New borrowing request';

        $message = sprintf(
            '%s from %s at %s is waiting for review.',
            $borrowing->borrowing_code,
            $borrowerName,
            $borrowing->campus ?: 'the assigned campus'
        );

        $this->notifyStaff($borrowing, $title, $message, $excludeUserId);
    }

    public function notifyStaff(
        Borrowing $borrowing,
        string $title,
        string $message,
        ?int $excludeUserId = null
    ): void {
        User::query()
            ->where('account_status', 'active')
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'super_admin']))
            ->with('roles')
            ->get()
            ->filter(function (User $user) use ($borrowing, $excludeUserId) {
                if ($excludeUserId && $user->id === $excludeUserId) {
                    return false;
                }

                return $user->hasRole('super_admin')
                    || ($user->hasRole('admin') && $user->campus === $borrowing->campus);
            })
            ->each(function (User $user) use ($borrowing, $title, $message) {
                $user->notify(new BorrowingStatusNotification(
                    $borrowing,
                    $title,
                    $message
                ));
            });
    }
}
