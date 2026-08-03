<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_borrowers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code', 40)->unique();
            $table->enum('role', ['student', 'professor', 'faculty_staff']);
            $table->string('full_name', 180);
            $table->string('id_number', 40)->nullable();
            $table->string('email');
            $table->string('program', 180)->nullable();
            $table->string('year_level', 40)->nullable();
            $table->string('section', 80)->nullable();
            $table->string('department', 180)->nullable();
            $table->timestamps();

            $table->index(['role', 'full_name']);
            $table->index('email');
            $table->index('id_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_borrowers');
    }
};
