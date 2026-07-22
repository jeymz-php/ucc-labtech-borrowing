<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class BorrowingPortalController extends Controller
{
    /**
     * Entry point encoded in the public Borrowing QR code.
     *
     * Guests are sent to sign in while Laravel remembers this URL.
     * After sign-in, password change, and email verification, the user
     * is sent here again and then forwarded to the borrowing form.
     */
    public function open(Request $request): RedirectResponse
    {
        if (! $request->user()) {
            $request->session()->put(
                'url.intended',
                route('borrow.access')
            );

            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'Sign in to create a borrowing request. '
                    .'New users may create an account first.'
                );
        }

        if ($request->user()->can('create borrowing requests')) {
            return redirect()->route('borrowings.create');
        }

        if (
            $request->user()->can('view own borrowings')
            || $request->user()->can('view all borrowings')
        ) {
            return redirect()
                ->route('borrowings.index')
                ->with(
                    'error',
                    'Your account can view borrowings but cannot create a borrowing request.'
                );
        }

        return redirect()
            ->route('dashboard')
            ->with(
                'error',
                'Your account does not have permission to create a borrowing request.'
            );
    }

    /**
     * Download a shareable SVG copy of the public Borrowing QR code.
     */
    public function download(Request $request): Response
    {
        abort_unless($request->user(), 403);

        $svg = QrCode::format('svg')
            ->size(800)
            ->margin(2)
            ->errorCorrection('H')
            ->generate(route('borrow.access'));

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' =>
                'attachment; filename="ucc-labtech-borrowing-qr.svg"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
