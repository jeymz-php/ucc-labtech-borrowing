<?php

namespace App\Console\Commands;

use App\Models\Borrowing;
use App\Notifications\BorrowingStatusNotification;
use Illuminate\Console\Command;

class MarkOverdueBorrowings extends Command
{
    protected $signature = 'borrowings:mark-overdue';
    protected $description = 'Mark released borrowings past their expected return time as overdue';

    public function handle(): int
    {
        $count = 0;
        Borrowing::with('user')->where('status', 'released')->where('expected_return_at', '<', now())
            ->chunkById(100, function ($borrowings) use (&$count) {
                foreach ($borrowings as $borrowing) {
                    $borrowing->update(['status' => 'overdue']);
                    $borrowing->user?->notify(new BorrowingStatusNotification($borrowing, 'Borrowing overdue', $borrowing->borrowing_code.' is now overdue. Please return the equipment as soon as possible.'));
                    $count++;
                }
            });
        $this->info("Marked {$count} borrowing(s) overdue.");
        return self::SUCCESS;
    }
}
