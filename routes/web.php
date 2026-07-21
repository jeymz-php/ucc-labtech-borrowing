<?php

use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemUnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ReservationCalendarController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\ScannerController;

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

    Route::get('/borrowings', [BorrowingController::class, 'index'])
        ->name('borrowings.index');
    Route::get('/borrowings/create', [BorrowingController::class, 'create'])
        ->name('borrowings.create');
    Route::post('/borrowings', [BorrowingController::class, 'store'])
        ->name('borrowings.store');
    Route::get('/borrowings/{borrowing}', [BorrowingController::class, 'show'])
        ->whereNumber('borrowing')->name('borrowings.show');
    Route::patch('/borrowings/{borrowing}/approve', [BorrowingController::class, 'approve'])
        ->whereNumber('borrowing')->name('borrowings.approve');
    Route::patch('/borrowings/{borrowing}/reject', [BorrowingController::class, 'reject'])
        ->whereNumber('borrowing')->name('borrowings.reject');
    Route::patch('/borrowings/{borrowing}/release', [BorrowingController::class, 'release'])
        ->whereNumber('borrowing')->name('borrowings.release');
    Route::patch('/borrowings/{borrowing}/receive', [BorrowingController::class, 'receive'])
        ->whereNumber('borrowing')->name('borrowings.receive');
    Route::patch('/borrowings/{borrowing}/cancel', [BorrowingController::class, 'cancel'])
        ->whereNumber('borrowing')->name('borrowings.cancel');
    Route::patch('/borrowings/{borrowing}/extend', [BorrowingController::class, 'extend'])->whereNumber('borrowing')->name('borrowings.extend');
    Route::get('/borrowings/{borrowing}/receipt', [BorrowingController::class, 'receipt'])->whereNumber('borrowing')->name('borrowings.receipt');

    Route::middleware('permission:use scanner')
    ->prefix('scanner')
    ->name('scanner.')
    ->group(function () {

        Route::get(
            '/',
            [ScannerController::class, 'index']
        )->name('index');

        Route::post(
            '/borrowing',
            [ScannerController::class, 'borrowing']
        )->name('borrowing');

        Route::post(
            '/unit',
            [ScannerController::class, 'unit']
        )->name('unit');
        
        Route::post(
            '/finish-release',
            [ScannerController::class, 'finishRelease']
        )->name('finish-release');

        Route::post(
            '/finish-return',
            [ScannerController::class, 'finishReturn']
        )->name('finish-return');

    });

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/reservation-calendar', [ReservationCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/reservation-calendar/events', [ReservationCalendarController::class, 'events'])->name('calendar.events');
    Route::get('/reservation-calendar/availability', [ReservationCalendarController::class, 'availability'])->name('calendar.availability');
    Route::patch('/reservation-calendar/{borrowing}/reschedule', [ReservationCalendarController::class, 'reschedule'])->whereNumber('borrowing')->name('calendar.reschedule');
    Route::get('/reservation-calendar/export/pdf', [ReservationCalendarController::class, 'pdf'])->name('calendar.pdf');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/export/csv', [AuditLogController::class, 'exportCsv'])->name('audit-logs.export.csv');
    Route::get('/audit-logs/export/pdf', [AuditLogController::class, 'exportPdf'])->name('audit-logs.export.pdf');

    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('/maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('/maintenance/{maintenance}', [MaintenanceController::class, 'show'])->whereNumber('maintenance')->name('maintenance.show');
    Route::patch('/maintenance/{maintenance}/assign', [MaintenanceController::class, 'assign'])->whereNumber('maintenance')->name('maintenance.assign');
    Route::patch('/maintenance/{maintenance}/start', [MaintenanceController::class, 'start'])->whereNumber('maintenance')->name('maintenance.start');
    Route::patch('/maintenance/{maintenance}/complete', [MaintenanceController::class, 'complete'])->whereNumber('maintenance')->name('maintenance.complete');
    Route::patch('/maintenance/{maintenance}/cancel', [MaintenanceController::class, 'cancel'])->whereNumber('maintenance')->name('maintenance.cancel');
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