<?php

use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
])->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->name('index');

        Route::get('/archived', [UserController::class, 'archived'])
            ->name('archived');

        Route::get('/create', [UserController::class, 'create'])
            ->name('create');

        Route::post('/', [UserController::class, 'store'])
            ->name('store');

        Route::get('/{user}/edit', [UserController::class, 'edit'])
            ->whereNumber('user')
            ->name('edit');

        Route::put('/{user}', [UserController::class, 'update'])
            ->whereNumber('user')
            ->name('update');

        Route::patch('/{user}/activate', [UserController::class, 'activate'])
            ->whereNumber('user')
            ->name('activate');

        Route::patch('/{user}/suspend', [UserController::class, 'suspend'])
            ->whereNumber('user')
            ->name('suspend');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->whereNumber('user')
            ->name('destroy');

        Route::patch('/{user}/restore', [UserController::class, 'restore'])
            ->whereNumber('user')
            ->name('restore');
    });

    Route::get('/settings', [SettingController::class, 'index'])
        ->name('settings.index');

    Route::put('/settings', [SettingController::class, 'update'])
        ->name('settings.update');
});
