<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('guest_borrowers', 'room')) {
            Schema::table('guest_borrowers', function (Blueprint $table) {
                $table->string('room', 120)
                    ->nullable()
                    ->after('department');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('guest_borrowers', 'room')) {
            Schema::table('guest_borrowers', function (Blueprint $table) {
                $table->dropColumn('room');
            });
        }
    }
};
