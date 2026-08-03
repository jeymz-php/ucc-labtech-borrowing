<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class StaffRegistrationController extends Controller
{
    private const CAMPUSES = [
        'Main Campus',
        'Congressional Extension Campus',
        'Camarin Extension Campus',
        'Bagong Silang Extension Campus',
    ];

    public function create(string $token): View
    {
        $this->assertValidToken($token);

        return view('staff-registration.create', [
            'registrationToken' => $token,
            'campuses' => self::CAMPUSES,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $this->assertValidToken($token);

        $data = $request->validate([
            'id_number' => ['required', 'string', 'min:4', 'max:30', 'unique:users,id_number'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'campus' => ['required', Rule::in(self::CAMPUSES)],
            'department' => ['required', 'string', 'max:180'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'user_code' => null,
                'id_number' => trim($data['id_number']),
                'first_name' => trim($data['first_name']),
                'middle_name' => $this->nullableTrim($data['middle_name'] ?? null),
                'last_name' => trim($data['last_name']),
                'suffix' => $this->nullableTrim($data['suffix'] ?? null),
                'email' => Str::lower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'campus' => $data['campus'],
                'department' => trim($data['department']),
                'program' => null,
                'year_level' => null,
                'section' => null,
                'contact_number' => $this->nullableTrim($data['contact_number'] ?? null),
                'profile_picture' => null,
                'account_status' => 'active',
                'must_change_password' => false,
                'terms_accepted_at' => now(),
                'privacy_policy_accepted_at' => now(),
            ]);

            $user->forceFill([
                'user_code' => sprintf('USR-%06d', $user->id),
                'email_verified_at' => now(),
            ])->save();

            $user->syncRoles(['admin']);
        });

        return redirect()
            ->route('login')
            ->with('status', 'Your staff administrator account was created and approved. You may now sign in.');
    }

    public function manage(Request $request): View
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        return view('staff-registration.manage', [
            'registrationUrl' => $this->registrationUrl(),
            'enabled' => (bool) config('staff_registration.enabled'),
            'configured' => $this->configuredToken() !== null,
        ]);
    }

    public function qr(Request $request): Response
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        $url = $this->registrationUrl();

        $svg = QrCode::format('svg')
            ->size(800)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($url);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="ucc-labtech-private-staff-registration-qr.svg"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function assertValidToken(string $token): void
    {
        abort_unless((bool) config('staff_registration.enabled'), 404);

        $configured = $this->configuredToken();

        abort_unless(
            $configured !== null && hash_equals($configured, $token),
            404
        );
    }

    private function registrationUrl(): string
    {
        $token = $this->configuredToken();

        abort_unless($token !== null, 503, 'STAFF_REGISTRATION_TOKEN is not configured.');

        return route('staff-registration.create', ['token' => $token]);
    }

    private function configuredToken(): ?string
    {
        $token = trim((string) config('staff_registration.token'));

        return strlen($token) >= 32 ? $token : null;
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
