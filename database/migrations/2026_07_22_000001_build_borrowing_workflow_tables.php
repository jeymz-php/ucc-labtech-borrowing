<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->string('borrowing_code', 40)->nullable()->unique()->after('id');
            $table->foreignId('user_id')->nullable()->after('borrowing_code')->constrained('users')->nullOnDelete();
            $table->text('purpose')->nullable()->after('user_id');
            $table->dateTime('borrow_at')->nullable()->after('purpose');
            $table->dateTime('expected_return_at')->nullable()->after('borrow_at');
            $table->dateTime('released_at')->nullable()->after('expected_return_at');
            $table->dateTime('returned_at')->nullable()->after('released_at');
            $table->enum('status', ['pending','approved','rejected','released','returned','cancelled','overdue'])->default('pending')->after('returned_at');
            $table->text('request_notes')->nullable()->after('status');
            $table->text('admin_notes')->nullable()->after('request_notes');
            $table->text('rejection_reason')->nullable()->after('admin_notes');
            $table->foreignId('approved_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable()->after('approved_by');
            $table->foreignId('released_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->after('released_by')->constrained('users')->nullOnDelete();
            $table->index(['user_id', 'status']);
            $table->index(['status', 'expected_return_at']);
        });

        Schema::table('borrowing_items', function (Blueprint $table) {
            $table->foreignId('borrowing_id')->nullable()->after('id')->constrained('borrowings')->cascadeOnDelete();
            $table->foreignId('item_unit_id')->nullable()->after('borrowing_id')->constrained('item_units')->restrictOnDelete();
            $table->string('condition_out', 30)->nullable()->after('item_unit_id');
            $table->string('condition_in', 30)->nullable()->after('condition_out');
            $table->text('remarks_out')->nullable()->after('condition_in');
            $table->text('remarks_in')->nullable()->after('remarks_out');
            $table->unique(['borrowing_id', 'item_unit_id']);
            $table->index('item_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('borrowing_items', function (Blueprint $table) {
            $table->dropForeign(['borrowing_id']);
            $table->dropForeign(['item_unit_id']);
            $table->dropUnique(['borrowing_id', 'item_unit_id']);
            $table->dropIndex(['item_unit_id']);
            $table->dropColumn(['borrowing_id','item_unit_id','condition_out','condition_in','remarks_out','remarks_in']);
        });

        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['released_by']);
            $table->dropForeign(['received_by']);
            $table->dropUnique(['borrowing_code']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status', 'expected_return_at']);
            $table->dropColumn(['borrowing_code','user_id','purpose','borrow_at','expected_return_at','released_at','returned_at','status','request_notes','admin_notes','rejection_reason','approved_by','approved_at','released_by','received_by']);
        });
    }
};
