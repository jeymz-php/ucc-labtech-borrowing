<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserGuideController extends Controller
{
    /**
     * Display the user manual inside the browser.
     */
    public function show(): BinaryFileResponse
    {
        return response()->file(
            $this->manualPath(),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="UCC-LabTech-User-Manual.pdf"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }

    /**
     * Download the user manual.
     */
    public function download(): BinaryFileResponse
    {
        return response()->download(
            $this->manualPath(),
            'UCC-LabTech-User-Manual.pdf',
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /**
     * Get the protected manual file path.
     */
    private function manualPath(): string
    {
        $path = resource_path('docs/user-manual.pdf');

        abort_unless(
            is_file($path),
            404,
            'The UCC LabTech user manual is currently unavailable.'
        );

        return $path;
    }
}
