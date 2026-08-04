<?php

namespace App\Http\Controllers;

use App\Mail\TemporaryPasswordMail;
use App\Models\Setting;
use App\Models\User;
use App\Support\CampusAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    private const CAMPUSES = [
        'Main Campus',
        'Congressional Extension Campus',
        'Camarin Extension Campus',
        'Bagong Silang Extension Campus',
    ];

    private const ACCOUNT_STATUSES = [
        'pending',
        'active',
        'suspended',
        'inactive',
    ];

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('view users'), 403);

        $users = $this->filteredUsers($request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(),
            'campuses' => $this->campusOptions($request->user()),
            'statistics' => $this->statistics($request->user()),
            'archivedMode' => false,
        ]);
    }

    public function archived(Request $request): View
    {
        abort_unless($request->user()->can('view users'), 403);

        $users = User::onlyTrashed()
            ->with('roles');

        CampusAccess::scopeForUser($users, $request->user());

        $users = $users
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('user_code', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(),
            'campuses' => $this->campusOptions($request->user()),
            'statistics' => $this->statistics($request->user()),
            'archivedMode' => true,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('create users'), 403);

        return view('users.create', [
            'roles' => $this->assignableRoles($request->user()),
            'campuses' => $this->campusOptions($request->user()),
            'statuses' => ['active', 'pending', 'inactive'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('create users'), 403);

        $validated = $this->validatedUserData($request);
        $temporaryPassword = $this->generateTemporaryPassword();

        $user = DB::transaction(function () use ($validated, $temporaryPassword) {
            $role = $validated['role'];

            $user = User::create([
                'user_code' => null,
                'id_number' => trim($validated['id_number']),
                'first_name' => trim($validated['first_name']),
                'middle_name' => $this->nullableTrim($validated['middle_name'] ?? null),
                'last_name' => trim($validated['last_name']),
                'suffix' => $this->nullableTrim($validated['suffix'] ?? null),
                'email' => Str::lower(trim($validated['email'])),
                'password' => Hash::make($temporaryPassword),
                'must_change_password' => true,
                'temporary_password_sent_at' => null,
                'campus' => $validated['campus'],
                'department' => in_array($role, ['professor', 'faculty'], true)
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
                'contact_number' => $this->nullableTrim($validated['contact_number'] ?? null),
                'profile_picture' => null,
                'account_status' => $validated['account_status'],
            ]);

            $user->forceFill([
                'user_code' => sprintf('USR-%06d', $user->id),
                'email_verified_at' => now(),
            ])->save();

            $user->syncRoles([$role]);

            return $user;
        });

        $mailSent = false;
        $emailEnabled = (bool) Setting::getValue(
            'email_notifications',
            true
        );

        if ($emailEnabled) {
            try {
                Mail::to($user->email)->send(
                    new TemporaryPasswordMail($user, $temporaryPassword)
                );

                $user->update([
                    'temporary_password_sent_at' => now(),
                ]);

                $mailSent = true;
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $mailStatus = match (true) {
            ! $emailEnabled =>
                'Email notifications are disabled. Copy the temporary password shown below.',
            $mailSent =>
                'The temporary password was emailed to the user.',
            default =>
                'The email could not be sent. Copy the temporary password shown below.',
        };

        return redirect()
            ->route('users.index')
            ->with('success', 'User account created successfully.')
            ->with('temporary_password', $temporaryPassword)
            ->with('mail_status', $mailStatus);
    }

    public function edit(Request $request, User $user): View
    {
        abort_unless($request->user()->can('edit users'), 403);
        $this->ensureTargetIsManageable($request->user(), $user);

        $user->load('roles');

        return view('users.edit', [
            'managedUser' => $user,
            'roles' => $this->assignableRoles($request->user(), $user),
            'campuses' => $this->campusOptions($request->user()),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('edit users'), 403);
        $this->ensureTargetIsManageable($request->user(), $user);

        $validated = $this->validatedUserData($request, $user);
        $role = $request->user()->is($user)
            ? $user->getRoleNames()->first()
            : $validated['role'];

        DB::transaction(function () use ($user, $validated, $role) {
            $user->update([
                'id_number' => trim($validated['id_number']),
                'first_name' => trim($validated['first_name']),
                'middle_name' => $this->nullableTrim($validated['middle_name'] ?? null),
                'last_name' => trim($validated['last_name']),
                'suffix' => $this->nullableTrim($validated['suffix'] ?? null),
                'email' => Str::lower(trim($validated['email'])),
                'campus' => $validated['campus'],
                'department' => in_array($role, ['professor', 'faculty'], true)
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
                'contact_number' => $this->nullableTrim($validated['contact_number'] ?? null),
            ]);

            if ($role) {
                $user->syncRoles([$role]);
            }
        });

        return redirect()
            ->route('users.edit', $user)
            ->with('success', 'User information updated successfully.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('activate users'), 403);
        $this->ensureTargetIsManageable($request->user(), $user);

        $user->update(['account_status' => 'active']);

        return back()->with('success', "{$user->full_name}'s account has been activated.");
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('suspend users'), 403);
        $this->ensureNotSelf($request->user(), $user);
        $this->ensureTargetIsManageable($request->user(), $user);

        $user->update(['account_status' => 'suspended']);

        return back()->with('success', "{$user->full_name}'s account has been suspended.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('archive users'), 403);
        $this->ensureNotSelf($request->user(), $user);
        $this->ensureTargetIsManageable($request->user(), $user);

        $hasActiveBorrowing = $user->borrowings()
            ->whereIn('status', ['pending', 'approved', 'released', 'overdue'])
            ->exists();

        if ($hasActiveBorrowing) {
            throw ValidationException::withMessages([
                'user' => 'This user cannot be archived while an active borrowing transaction exists.',
            ]);
        }

        DB::transaction(function () use ($user) {
            $user->update(['account_status' => 'archived']);
            $user->delete();
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User account archived successfully.');
    }

    public function restore(Request $request, int $user): RedirectResponse
    {
        abort_unless($request->user()->can('restore users'), 403);

        $managedUser = User::onlyTrashed()->findOrFail($user);
        $this->ensureTargetIsManageable($request->user(), $managedUser);

        DB::transaction(function () use ($managedUser) {
            $managedUser->restore();
            $managedUser->update(['account_status' => 'inactive']);
        });

        return redirect()
            ->route('users.archived')
            ->with('success', 'User account restored. Activate it when access should be allowed.');
    }

    private function filteredUsers(Request $request)
    {
        $query = User::query()->with('roles');

        CampusAccess::scopeForUser($query, $request->user());

        return $query
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('user_code', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->role($request->string('role')->toString());
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('account_status', $request->string('status')->toString());
            })
            ->when($request->filled('campus'), function ($query) use ($request) {
                $query->where('campus', $request->string('campus')->toString());
            });
    }

    private function statistics(User $actor): array
    {
        $users = User::query();
        $archived = User::onlyTrashed();

        CampusAccess::scopeForUser($users, $actor);
        CampusAccess::scopeForUser($archived, $actor);

        return [
            'total' => (clone $users)->count(),
            'active' => (clone $users)->where('account_status', 'active')->count(),
            'pending' => (clone $users)->where('account_status', 'pending')->count(),
            'suspended' => (clone $users)->where('account_status', 'suspended')->count(),
            'archived' => $archived->count(),
        ];
    }

    private function validatedUserData(Request $request, ?User $user = null): array
    {
        $assignableRoles = $this->assignableRoles($request->user(), $user)
            ->pluck('name')
            ->all();

        $rules = [
            'role' => ['required', Rule::in($assignableRoles)],
            'id_number' => [
                'required',
                'string',
                'size:8',
                Rule::unique('users', 'id_number')->ignore($user?->id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'campus' => [
                Rule::requiredIf(fn () => CampusAccess::canViewAllCampuses($request->user())),
                'nullable',
                Rule::in(CampusAccess::options()),
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
        ];

        if (! $user) {
            $rules['account_status'] = [
                'required',
                Rule::in(['active', 'pending', 'inactive']),
            ];
        }

        $validated = $request->validate($rules, [
            'id_number.size' => 'The ID number must contain exactly 8 characters.',
            'department.required_if' => 'The department is required for professors and faculty.',
            'program.required_if' => 'The program is required for students.',
            'year_level.required_if' => 'The year level is required for students.',
            'section.required_if' => 'The section is required for students.',
            'contact_number.regex' => 'Enter a valid Philippine mobile number.',
        ]);

        $validated['campus'] = CampusAccess::campusForWrite(
            $request->user(),
            $validated['campus'] ?? null
        );

        return $validated;
    }

    private function assignableRoles(User $actor, ?User $target = null)
    {
        $roles = $actor->hasRole('super_admin')
            ? ['student', 'professor', 'faculty', 'admin', 'super_admin']
            : ['student', 'professor', 'faculty'];

        if ($target && $actor->is($target)) {
            $currentRole = $target->getRoleNames()->first();

            if ($currentRole && ! in_array($currentRole, $roles, true)) {
                $roles[] = $currentRole;
            }
        }

        return Role::query()
            ->whereIn('name', $roles)
            ->orderBy('name')
            ->get();
    }

    private function ensureTargetIsManageable(User $actor, User $target): void
    {
        if ($actor->hasRole('super_admin') || $actor->is($target)) {
            return;
        }

        if (! CampusAccess::canAccess($actor, $target->campus)) {
            abort(403, 'This user belongs to another campus.');
        }

        if ($target->hasAnyRole(['admin', 'super_admin'])) {
            abort(403);
        }
    }

    private function campusOptions(User $actor): array
    {
        return CampusAccess::canViewAllCampuses($actor)
            ? CampusAccess::options()
            : [CampusAccess::userCampus($actor)];
    }

    private function ensureNotSelf(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot perform this action on your own account.',
            ]);
        }
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value === '' ? null : $value;
    }

    private function generateTemporaryPassword(): string
    {
        $uppercase = Str::upper(Str::random(2));
        $lowercase = Str::lower(Str::random(4));
        $numbers = (string) random_int(100, 999);
        $symbols = ['!', '@', '#', '$', '%', '&'];

        return $uppercase
            .$lowercase
            .$numbers
            .$symbols[array_rand($symbols)];
    }
}
