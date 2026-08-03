<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Private Staff Registration</h1>
            <p class="mt-1 text-sm text-gray-500">Share the protected link or QR code only with authorized LabTech staff.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        @if (! $configured)
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
                <strong>STAFF_REGISTRATION_TOKEN is not configured.</strong> Add a random token with at least 32 characters to the server <code>.env</code>, then run <code>php artisan config:cache</code>.
            </div>
        @elseif (! $enabled)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
                Private staff registration is currently disabled through <code>STAFF_REGISTRATION_ENABLED=false</code>.
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-lg font-bold text-gray-900">Private Registration Link</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500">Anyone who possesses this private URL can create an automatically approved Admin account. Rotate the token immediately when the link is exposed to an unauthorized person.</p>

                    <div class="mt-5 break-all rounded-2xl bg-gray-50 p-4 font-mono text-xs text-gray-700">{{ $registrationUrl }}</div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <button type="button" onclick="navigator.clipboard.writeText(@js($registrationUrl)); this.textContent='Copied';" class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">Copy Private Link</button>
                        <a href="{{ $registrationUrl }}" target="_blank" rel="noopener" class="rounded-xl bg-green-700 px-5 py-3 text-center text-sm font-bold text-white hover:bg-green-800">Open Registration Page</a>
                    </div>
                </section>

                <section class="rounded-3xl border border-gray-200 bg-white p-6 text-center shadow-sm sm:p-8">
                    <h2 class="text-lg font-bold text-gray-900">Staff Registration QR Code</h2>
                    <p class="mt-2 text-sm text-gray-500">Scan this QR code to open the protected registration page.</p>
                    <div class="mx-auto mt-5 w-fit rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(280)->margin(1)->errorCorrection('H')->generate($registrationUrl) !!}
                    </div>
                    <a href="{{ route('staff-registration.qr') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-gray-900 px-5 py-3 text-sm font-bold text-white hover:bg-gray-800">Download Staff Registration QR</a>
                </section>
            </div>

            <section class="rounded-3xl border border-red-200 bg-red-50 p-6 text-sm leading-6 text-red-900">
                <h2 class="font-bold">Security Reminder</h2>
                <p class="mt-2">This QR code is not intended for students, professors, faculty borrowers, or public posting. Guest borrowers must use the separate Guest Borrower QR code. Staff accounts created here receive administrator permissions immediately.</p>
            </section>
        @endif
    </div>
</x-app-layout>
