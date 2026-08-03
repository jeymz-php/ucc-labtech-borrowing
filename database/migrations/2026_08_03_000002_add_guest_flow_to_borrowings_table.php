<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->foreignId('guest_borrower_id')
                ->nullable()
                ->after('user_id')
                ->constrained('guest_borrowers')
                ->nullOnDelete();

            $table->string('public_token', 64)
                ->nullable()
                ->unique()
                ->after('guest_borrower_id');

            $table->enum('source', ['account', 'guest'])
                ->default('account')
                ->after('public_token');

            $table->timestamp('terms_accepted_at')->nullable()->after('extension_reason');
            $table->timestamp('privacy_accepted_at')->nullable()->after('terms_accepted_at');
            $table->timestamp('liability_accepted_at')->nullable()->after('privacy_accepted_at');

            $table->index(['guest_borrower_id', 'status']);
            $table->index(['source', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropForeign(['guest_borrower_id']);
            $table->dropUnique(['public_token']);
            $table->dropIndex(['guest_borrower_id', 'status']);
            $table->dropIndex(['source', 'status']);
            $table->dropColumn([
                'guest_borrower_id',
                'public_token',
                'source',
                'terms_accepted_at',
                'privacy_accepted_at',
                'liability_accepted_at',
            ]);
        });
    }
};
