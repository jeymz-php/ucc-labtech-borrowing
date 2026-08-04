<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_units', function (Blueprint $table) {
            $table->string('campus', 100)
                ->default('Main Campus')
                ->after('item_id');

            $table->index(['campus', 'availability_status']);
        });

        Schema::table('guest_borrowers', function (Blueprint $table) {
            $table->string('campus', 100)
                ->default('Main Campus')
                ->after('email');

            $table->index('campus');
        });

        Schema::table('borrowings', function (Blueprint $table) {
            $table->string('campus', 100)
                ->default('Main Campus')
                ->after('source');

            $table->index(['campus', 'status']);
        });


        // Preserve campus ownership for records that already existed before
        // the intercampus migration. Inventory without a creator remains at
        // Main Campus and can be reassigned by a Super Admin.
        DB::statement(<<<'SQL'
            UPDATE item_units AS units
            INNER JOIN users ON users.id = units.created_by
            SET units.campus = users.campus
            WHERE users.campus IS NOT NULL
              AND users.campus <> ''
        SQL);

        DB::statement(<<<'SQL'
            UPDATE borrowings
            INNER JOIN users ON users.id = borrowings.user_id
            SET borrowings.campus = users.campus
            WHERE borrowings.user_id IS NOT NULL
              AND users.campus IS NOT NULL
              AND users.campus <> ''
        SQL);

        DB::statement(<<<'SQL'
            UPDATE borrowings
            INNER JOIN guest_borrowers
                ON guest_borrowers.id = borrowings.guest_borrower_id
            SET borrowings.campus = guest_borrowers.campus
            WHERE borrowings.guest_borrower_id IS NOT NULL
        SQL);
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropIndex(['campus', 'status']);
            $table->dropColumn('campus');
        });

        Schema::table('guest_borrowers', function (Blueprint $table) {
            $table->dropIndex(['campus']);
            $table->dropColumn('campus');
        });

        Schema::table('item_units', function (Blueprint $table) {
            $table->dropIndex(['campus', 'availability_status']);
            $table->dropColumn('campus');
        });
    }
};
