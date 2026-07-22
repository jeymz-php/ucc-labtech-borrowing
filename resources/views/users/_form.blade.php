@php
    $editing = isset($managedUser);
    $selectedRole = old(
        'role',
        $editing ? $managedUser->getRoleNames()->first() : 'student'
    );
@endphp

<div
    x-data="{
        role: @js($selectedRole),
        isStudent() { return this.role === 'student'; },
        isEmployee() { return ['professor', 'faculty'].includes(this.role); }
    }"
    class="space-y-6"
>
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Account Information</h2>
            <p class="mt-1 text-sm text-gray-500">
                Set the user's role, university ID, and login email.
            </p>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="role" class="block text-sm font-semibold text-gray-700">
                    Role <span class="text-red-500">*</span>
                </label>
                <select
                    id="role"
                    name="role"
                    x-model="role"
                    required
                    @disabled($editing && auth()->id() === $managedUser->id)
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600 disabled:bg-gray-100"
                >
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>
                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                        </option>
                    @endforeach
                </select>
                @if ($editing && auth()->id() === $managedUser->id)
                    <input type="hidden" name="role" value="{{ $selectedRole }}">
                @endif
                @error('role')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @unless ($editing)
                <div>
                    <label for="account_status" class="block text-sm font-semibold text-gray-700">
                        Initial Status <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="account_status"
                        name="account_status"
                        required
                        class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                    >
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(old('account_status', 'active') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    @error('account_status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endunless

            <div>
                <label for="id_number" class="block text-sm font-semibold text-gray-700">
                    ID Number <span class="text-red-500">*</span>
                </label>
                <input
                    id="id_number"
                    name="id_number"
                    type="text"
                    maxlength="8"
                    value="{{ old('id_number', $editing ? $managedUser->id_number : '') }}"
                    required
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('id_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $editing ? $managedUser->email : '') }}"
                    required
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Personal Information</h2>
            <p class="mt-1 text-sm text-gray-500">
                Enter the user's complete name and contact details.
            </p>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="first_name" class="block text-sm font-semibold text-gray-700">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input
                    id="first_name"
                    name="first_name"
                    type="text"
                    value="{{ old('first_name', $editing ? $managedUser->first_name : '') }}"
                    required
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('first_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="middle_name" class="block text-sm font-semibold text-gray-700">Middle Name</label>
                <input
                    id="middle_name"
                    name="middle_name"
                    type="text"
                    value="{{ old('middle_name', $editing ? $managedUser->middle_name : '') }}"
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('middle_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="last_name" class="block text-sm font-semibold text-gray-700">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input
                    id="last_name"
                    name="last_name"
                    type="text"
                    value="{{ old('last_name', $editing ? $managedUser->last_name : '') }}"
                    required
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('last_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="suffix" class="block text-sm font-semibold text-gray-700">Suffix</label>
                <input
                    id="suffix"
                    name="suffix"
                    type="text"
                    value="{{ old('suffix', $editing ? $managedUser->suffix : '') }}"
                    placeholder="Jr., Sr., III"
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('suffix')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="contact_number" class="block text-sm font-semibold text-gray-700">Contact Number</label>
                <input
                    id="contact_number"
                    name="contact_number"
                    type="text"
                    value="{{ old('contact_number', $editing ? $managedUser->contact_number : '') }}"
                    placeholder="09XXXXXXXXX"
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('contact_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div>
            <h2 class="text-lg font-bold text-gray-900">University Information</h2>
            <p class="mt-1 text-sm text-gray-500">
                Campus and academic information are adjusted based on the selected role.
            </p>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="campus" class="block text-sm font-semibold text-gray-700">
                    Campus <span class="text-red-500">*</span>
                </label>
                <select
                    id="campus"
                    name="campus"
                    required
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                    <option value="">Select campus</option>
                    @foreach ($campuses as $campus)
                        <option value="{{ $campus }}" @selected(old('campus', $editing ? $managedUser->campus : '') === $campus)>
                            {{ $campus }}
                        </option>
                    @endforeach
                </select>
                @error('campus')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="isEmployee()" x-cloak class="sm:col-span-2">
                <label for="department" class="block text-sm font-semibold text-gray-700">
                    Department <span class="text-red-500">*</span>
                </label>
                <input
                    id="department"
                    name="department"
                    type="text"
                    value="{{ old('department', $editing ? $managedUser->department : '') }}"
                    :required="isEmployee()"
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('department')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="isStudent()" x-cloak class="sm:col-span-2">
                <label for="program" class="block text-sm font-semibold text-gray-700">
                    Program <span class="text-red-500">*</span>
                </label>
                <input
                    id="program"
                    name="program"
                    type="text"
                    value="{{ old('program', $editing ? $managedUser->program : '') }}"
                    :required="isStudent()"
                    placeholder="Bachelor of Science in Information Technology"
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('program')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="isStudent()" x-cloak>
                <label for="year_level" class="block text-sm font-semibold text-gray-700">
                    Year Level <span class="text-red-500">*</span>
                </label>
                <select
                    id="year_level"
                    name="year_level"
                    :required="isStudent()"
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                    <option value="">Select year</option>
                    @foreach (['1', '2', '3', '4', '5'] as $year)
                        <option value="{{ $year }}" @selected(old('year_level', $editing ? $managedUser->year_level : '') === $year)>
                            Year {{ $year }}
                        </option>
                    @endforeach
                </select>
                @error('year_level')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="isStudent()" x-cloak>
                <label for="section" class="block text-sm font-semibold text-gray-700">
                    Section <span class="text-red-500">*</span>
                </label>
                <input
                    id="section"
                    name="section"
                    type="text"
                    value="{{ old('section', $editing ? $managedUser->section : '') }}"
                    :required="isStudent()"
                    class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                >
                @error('section')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('users.index') }}"
            class="rounded-xl border border-gray-300 px-5 py-2.5 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
        >
            Cancel
        </a>
        <button
            type="submit"
            class="rounded-xl bg-green-700 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800"
        >
            {{ $editing ? 'Save Changes' : 'Create User' }}
        </button>
    </div>
</div>
