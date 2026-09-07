<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->timestamp('returned_to_office_at')
                ->nullable()
                ->after('returned_at');
        });

        // Older versions marked future reservations as physically "reserved".
        // Reservation conflicts are now schedule-based, so the physical unit can
        // remain Available until it is actually released to a borrower.
        DB::table('item_units')
            ->where('availability_status', 'reserved')
            ->update(['availability_status' => 'available']);

        DB::table('items')
            ->orderBy('id')
            ->chunkById(100, function ($items) {
                foreach ($items as $item) {
                    $available = DB::table('item_units')
                        ->where('item_id', $item->id)
                        ->whereNull('deleted_at')
                        ->where('availability_status', 'available')
                        ->count();

                    DB::table('items')
                        ->where('id', $item->id)
                        ->update(['quantity_available' => $available]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn('returned_to_office_at');
        });
    }
};
