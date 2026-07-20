<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')
                ->default(false)
                ->after('password');

            $table->timestamp('temporary_password_sent_at')
                ->nullable()
                ->after('must_change_password');

            $table->timestamp('terms_accepted_at')
                ->nullable()
                ->after('temporary_password_sent_at');

            $table->timestamp('privacy_policy_accepted_at')
                ->nullable()
                ->after('terms_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'must_change_password',
                'temporary_password_sent_at',
                'terms_accepted_at',
                'privacy_policy_accepted_at',
            ]);
        });
    }
};