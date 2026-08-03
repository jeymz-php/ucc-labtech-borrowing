<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class BorrowingPortalController extends Controller
{
    /**
     * Public QR entry point for the Guest Borrower portal.
     */
    public function open(): RedirectResponse
    {
        return redirect()->route('guest-borrowings.create');
    }

    /**
     * Download a shareable SVG copy of the Guest Borrower portal QR code.
     */
    public function download(Request $request): Response
    {
        abort_unless($request->user(), 403);

        $svg = QrCode::format('svg')
            ->size(800)
            ->margin(2)
            ->errorCorrection('H')
            ->generate(route('guest-borrowings.create'));

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="ucc-labtech-guest-borrower-qr.svg"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
