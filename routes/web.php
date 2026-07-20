<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/dashboard',
    [DashboardController::class, 'index']
)->middleware([
    'auth',
    'verified',
    'permission:view dashboard',
])->name('dashboard');

Route::middleware([
    'auth',
    'verified',
])->group(function () {
    Route::get(
        '/categories/archived',
        [CategoryController::class, 'archived']
    )->name('categories.archived');

    Route::patch(
        '/categories/{category}/restore',
        [CategoryController::class, 'restore']
    )->name('categories.restore');

    Route::patch(
        '/categories/{category}/toggle-status',
        [CategoryController::class, 'toggleStatus']
    )->name('categories.toggle-status');

    Route::resource(
        'categories',
        CategoryController::class
    );
});

Route::middleware('auth')->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

require __DIR__ . '/auth.php';