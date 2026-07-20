<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TemporaryPasswordMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $allowedRoles = [
            'student',
            'professor',
            'faculty',
        ];

        $allowedCampuses = [
            'Main Campus',
            'Congressional Extension Campus',
            'Camarin Extension Campus',
            'Bagong Silang Extension Campus',
        ];

        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in($allowedRoles),
            ],

            'id_number' => [
                'required',
                'string',
                'size:8',
                'unique:users,id_number',
            ],

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'suffix' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'campus' => [
                'required',
                Rule::in($allowedCampuses),
            ],

            'department' => [
                'nullable',
                'required_if:role,professor,faculty',
                'string',
                'max:150',
            ],

            'program' => [
                'nullable',
                'required_if:role,student',
                'string',
                'max:150',
            ],

            'year_level' => [
                'nullable',
                'required_if:role,student',
                Rule::in(['1', '2', '3', '4', '5']),
            ],

            'section' => [
                'nullable',
                'required_if:role,student',
                'string',
                'max:50',
            ],

            'contact_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(09\d{9}|\+639\d{9})$/',
            ],

            'terms' => [
                'required',
                'accepted',
            ],

            'privacy_policy' => [
                'required',
                'accepted',
            ],
        ], [
            'id_number.size' =>
                'The ID number must contain exactly 8 characters.',

            'department.required_if' =>
                'The department is required for professors and faculty.',

            'program.required_if' =>
                'The program is required for students.',

            'year_level.required_if' =>
                'The year level is required for students.',

            'section.required_if' =>
                'The section is required for students.',

            'contact_number.regex' =>
                'Enter a valid Philippine mobile number.',

            'terms.accepted' =>
                'You must accept the Terms and Conditions.',

            'privacy_policy.accepted' =>
                'You must acknowledge the Privacy Policy.',
        ]);

        $temporaryPassword = $this->generateTemporaryPassword();

        try {
            $user = DB::transaction(function () use (
                $validated,
                $temporaryPassword
            ) {
                $role = $validated['role'];

                $user = User::create([
                    'user_code' => null,
                    'id_number' => trim($validated['id_number']),
                    'first_name' => trim($validated['first_name']),
                    'middle_name' => ! empty($validated['middle_name'])
                        ? trim($validated['middle_name'])
                        : null,
                    'last_name' => trim($validated['last_name']),
                    'suffix' => ! empty($validated['suffix'])
                        ? trim($validated['suffix'])
                        : null,
                    'email' => strtolower(trim($validated['email'])),
                    'password' => Hash::make($temporaryPassword),
                    'must_change_password' => true,
                    'temporary_password_sent_at' => now(),
                    'terms_accepted_at' => now(),
                    'privacy_policy_accepted_at' => now(),
                    'campus' => $validated['campus'],

                    'department' => in_array(
                        $role,
                        ['professor', 'faculty'],
                        true
                    )
                        ? trim($validated['department'])
                        : null,

                    'program' => $role === 'student'
                        ? trim($validated['program'])
                        : null,

                    'year_level' => $role === 'student'
                        ? $validated['year_level']
                        : null,

                    'section' => $role === 'student'
                        ? trim($validated['section'])
                        : null,

                    'contact_number' =>
                        $validated['contact_number'] ?? null,

                    'profile_picture' => null,

                    /*
                     * Keep this as active for immediate testing.
                     * Later, professor/faculty accounts can become pending.
                     */
                    'account_status' => 'active',
                ]);

                $user->update([
                    'user_code' => sprintf(
                        'USR-%06d',
                        $user->id
                    ),
                ]);

                $user->assignRole($role);

                Mail::to($user->email)->send(
                    new TemporaryPasswordMail(
                        $user,
                        $temporaryPassword
                    )
                );

                return $user;
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput($request->except([
                    'terms',
                    'privacy_policy',
                ]))
                ->withErrors([
                    'email' =>
                        'The account could not be created because the '
                        . 'temporary-password email could not be sent. '
                        . 'Please verify the email address or try again.',
                ]);
        }

        event(new Registered($user));

        return redirect()
            ->route('login')
            ->with(
                'status',
                'Registration successful. Your temporary password was '
                . 'sent to your email address.'
            );
    }

    private function generateTemporaryPassword(): string
    {
        $uppercase = Str::upper(Str::random(2));
        $lowercase = Str::lower(Str::random(4));
        $numbers = (string) random_int(100, 999);
        $symbols = ['!', '@', '#', '$', '%', '&'];

        return $uppercase
            . $lowercase
            . $numbers
            . $symbols[array_rand($symbols)];
    }
}