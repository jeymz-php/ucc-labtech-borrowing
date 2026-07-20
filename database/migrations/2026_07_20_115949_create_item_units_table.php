<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')
                ->constrained('items')
                ->cascadeOnDelete();

            $table->string('asset_number', 50)
                ->nullable()
                ->unique();

            $table->string('barcode_value', 100)
                ->nullable()
                ->unique();

            $table->string('barcode_path')
                ->nullable();

            $table->string('serial_number', 100)
                ->nullable()
                ->unique();

            $table->string('property_number', 100)
                ->nullable()
                ->unique();

            $table->date('acquisition_date')->nullable();

            $table->decimal('acquisition_cost', 12, 2)
                ->nullable();

            $table->enum('condition', [
                'excellent',
                'good',
                'fair',
                'damaged',
                'for_repair',
                'unserviceable',
            ])->default('good');

            $table->enum('availability_status', [
                'available',
                'reserved',
                'borrowed',
                'maintenance',
                'lost',
                'archived',
            ])->default('available');

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('item_id');
            $table->index('condition');
            $table->index('availability_status');
            $table->index([
                'item_id',
                'availability_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};