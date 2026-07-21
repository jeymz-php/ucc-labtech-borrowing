<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_code', 30)->unique();
            $table->foreignId('item_unit_id')->constrained('item_units')->cascadeOnDelete();
            $table->foreignId('borrowing_id')->nullable()->constrained('borrowings')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['reported','assigned','in_progress','completed','cancelled'])->default('reported');
            $table->enum('priority', ['low','medium','high','critical'])->default('medium');
            $table->string('issue_title');
            $table->text('issue_description')->nullable();
            $table->enum('condition_before', ['excellent','good','fair','damaged','for_repair','unserviceable'])->nullable();
            $table->enum('condition_after', ['excellent','good','fair','damaged','for_repair','unserviceable'])->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('repair_action')->nullable();
            $table->decimal('repair_cost', 12, 2)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();
            $table->index(['status','priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
