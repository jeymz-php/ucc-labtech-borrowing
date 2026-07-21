<?php

namespace App\Notifications;

use App\Models\Borrowing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BorrowingStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Borrowing $borrowing,
        private string $title,
        private string $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'borrowing_id' => $this->borrowing->id,
            'borrowing_code' => $this->borrowing->borrowing_code,
            'status' => $this->borrowing->status,
            'url' => route('borrowings.show', $this->borrowing),
        ];
    }
}
