<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->foreignId('extended_by')->nullable()->after('received_by')->constrained('users')->nullOnDelete();
            $table->dateTime('extended_at')->nullable()->after('extended_by');
            $table->text('extension_reason')->nullable()->after('extended_at');
        });

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropForeign(['extended_by']);
            $table->dropColumn(['extended_by', 'extended_at', 'extension_reason']);
        });
        Schema::dropIfExists('notifications');
    }
};
