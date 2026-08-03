<?php

use App\Http\Controllers\BorrowingPortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Guest Borrower QR entry point
|--------------------------------------------------------------------------
|
| This URL is encoded in the topbar QR code. The short /borrow-now URL redirects directly to the public Guest Borrower form.
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
