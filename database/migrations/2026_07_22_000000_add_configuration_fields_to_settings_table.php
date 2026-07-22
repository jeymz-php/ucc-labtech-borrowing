<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('group', 50)
                ->default('general')
                ->after('id');

            $table->string('key', 100)
                ->unique()
                ->after('group');

            $table->text('value')
                ->nullable()
                ->after('key');

            $table->string('type', 30)
                ->default('string')
                ->after('value');

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex(['group']);
            $table->dropUnique(['key']);
            $table->dropColumn([
                'group',
                'key',
                'value',
                'type',
            ]);
        });
    }
};
