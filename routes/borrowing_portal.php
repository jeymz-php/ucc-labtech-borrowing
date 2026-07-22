<?php

use App\Http\Controllers\BorrowingPortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Borrowing QR entry point
|--------------------------------------------------------------------------
|
| This URL is encoded in the topbar QR code. Guests are redirected to the
| login page while the intended destination is retained in the session.
|
*/
Route::get(
    '/borrow-now',
    [BorrowingPortalController::class, 'open']
)->name('borrow.access');

Route::get(
    '/borrow-now/qr/download',
    [BorrowingPortalController::class, 'download']
)
    ->middleware('auth')
    ->name('borrow.qr.download');
