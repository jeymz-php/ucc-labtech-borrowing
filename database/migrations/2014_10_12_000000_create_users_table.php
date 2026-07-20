<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('user_code', 30)->nullable()->unique();
            $table->string('id_number', 30)->unique();

            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 20)->nullable();

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('campus', 100)->nullable();
            $table->string('college', 150)->nullable();
            $table->string('department', 150)->nullable();
            $table->string('program', 150)->nullable();
            $table->string('year_level', 30)->nullable();
            $table->string('section', 50)->nullable();

            $table->string('contact_number', 30)->nullable();
            $table->string('profile_picture')->nullable();

            $table->enum('account_status', [
                'pending',
                'active',
                'suspended',
                'inactive',
                'archived',
            ])->default('pending');

            $table->timestamp('last_login_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('account_status');
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};