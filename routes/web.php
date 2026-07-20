<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemUnitController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get(
        'register',
        [RegisteredUserController::class, 'create']
    )->name('register');

    Route::post(
        'register',
        [RegisteredUserController::class, 'store']
    );
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

    Route::get(
        '/items/archived',
        [ItemController::class, 'archived']
    )->name('items.archived');

    Route::patch(
        '/items/{item}/restore',
        [ItemController::class, 'restore']
    )->whereNumber('item')->name('items.restore');

    Route::patch(
        '/items/{item}/toggle-status',
        [ItemController::class, 'toggleStatus']
    )->name('items.toggle-status');

    Route::get('/item-units/lookup', [ItemUnitController::class, 'lookup'])->name('item-units.lookup');
    Route::get('/item-units/archived', [ItemUnitController::class, 'archived'])->name('item-units.archived');
    Route::patch('/item-units/{itemUnit}/restore', [ItemUnitController::class, 'restore'])->whereNumber('itemUnit')->name('item-units.restore');
    Route::post('/item-units/print', [ItemUnitController::class, 'printBulk'])->name('item-units.print-bulk');
    Route::get('/item-units/{itemUnit}/print', [ItemUnitController::class, 'printOne'])->name('item-units.print');
    Route::get('/items/{item}/units/create', [ItemUnitController::class, 'create'])->name('items.units.create');
    Route::post('/items/{item}/units', [ItemUnitController::class, 'store'])->name('items.units.store');
    Route::get('/items/{item}/units/bulk-create', [ItemUnitController::class, 'bulkCreate'])->name('items.units.bulk-create');
    Route::post('/items/{item}/units/bulk', [ItemUnitController::class, 'bulkStore'])->name('items.units.bulk-store');
    Route::resource('item-units', ItemUnitController::class)->except(['create','store']);

    Route::resource('items', ItemController::class);
});

Route::middleware([
    'auth',
    'force.password.change',
])->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::patch(
        '/profile/photo',
        [ProfileController::class, 'updatePhoto']
    )->name('profile.photo.update');

    Route::delete(
        '/profile/photo',
        [ProfileController::class, 'destroyPhoto']
    )->name('profile.photo.destroy');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

require __DIR__ . '/auth.php';