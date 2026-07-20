<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                My Profile
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Manage your personal information and account security.
            </p>
        </div>
    </x-slot>

    <div
        x-data="profilePhotoManager()"
        class="space-y-6"
    >
        {{-- Success notifications --}}
        @if (session('status'))
            <div
                x-data="{ visible: true }"
                x-show="visible"
                x-transition
                class="flex items-start justify-between rounded-xl
                       border border-green-200 bg-green-50
                       px-4 py-3 text-sm text-green-800"
            >
                <div class="flex items-center gap-3">
                    <svg
                        class="h-5 w-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0
                               11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    <span>
                        @switch(session('status'))
                            @case('profile-updated')
                                Profile information updated successfully.
                                @break

                            @case('photo-updated')
                                Profile picture updated successfully.
                                @break

                            @case('photo-removed')
                                Profile picture removed successfully.
                                @break

                            @default
                                Changes saved successfully.
                        @endswitch
                    </span>
                </div>

                <button
                    type="button"
                    x-on:click="visible = false"
                    class="text-green-700 hover:text-green-900"
                >
                    &times;
                </button>
            </div>
        @endif

        {{-- Profile header --}}
        <section
            class="overflow-hidden rounded-2xl border border-gray-200
                bg-white shadow-sm"
        >
            {{-- Green cover --}}
            <div
                class="relative h-28 overflow-hidden
                    bg-gradient-to-r from-green-900 via-green-700
                    to-emerald-600 sm:h-40"
            >
                <div
                    class="absolute -right-16 -top-20 h-56 w-56
                        rounded-full bg-white/10"
                ></div>

                <div
                    class="absolute bottom-[-80px] left-[25%]
                        h-48 w-48 rounded-full bg-white/5"
                ></div>
            </div>

            {{-- White profile information area --}}
            <div class="relative px-5 pb-6 pt-16 sm:px-8 sm:pb-7 sm:pt-5">
                {{-- Avatar overlaps cover only --}}
                <div
                    class="absolute -top-12 left-5 h-24 w-24
                        sm:-top-14 sm:left-8 sm:h-28 sm:w-28"
                >
                    <div
                        class="flex h-full w-full items-center justify-center
                            overflow-hidden rounded-full border-4 border-white
                            bg-green-100 text-2xl font-bold text-green-700
                            shadow-lg sm:text-3xl"
                    >
                        <img
                            x-show="previewUrl"
                            x-cloak
                            x-bind:src="previewUrl"
                            alt="Selected profile picture preview"
                            class="h-full w-full object-cover"
                        >

                        @if ($user->profile_picture_url)
                            <img
                                x-show="! previewUrl"
                                src="{{ $user->profile_picture_url }}"
                                alt="{{ $user->full_name }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <span
                                x-show="! previewUrl"
                                class="flex h-full w-full items-center justify-center"
                            >
                                {{ $user->initials }}
                            </span>
                        @endif
                    </div>

                    <button
                        type="button"
                        x-on:click="$refs.photoInput.click()"
                        class="absolute bottom-1 right-1 flex h-9 w-9
                            items-center justify-center rounded-full
                            border-2 border-white bg-green-700
                            text-white shadow-md transition
                            hover:scale-110 hover:bg-green-800"
                        aria-label="Select profile picture"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 9a2 2 0 012-2h1l2-3h8l2 3h1
                                a2 2 0 012 2v9a2 2 0 01-2 2H5
                                a2 2 0 01-2-2V9z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 13a3 3 0 11-6 0
                                3 3 0 016 0z"
                            />
                        </svg>
                    </button>
                </div>

                <div
                    class="flex flex-col gap-4
                        sm:min-h-[88px] sm:flex-row
                        sm:items-center sm:justify-between
                        sm:pl-32"
                >
                    {{-- User identity --}}
                    <div class="min-w-0">
                        <h2
                            class="break-words text-xl font-bold
                                leading-tight text-gray-900 sm:text-2xl"
                        >
                            {{ $user->full_name }}
                        </h2>

                        <p
                            class="mt-1 max-w-full break-all
                                text-sm text-gray-500"
                        >
                            {{ $user->email }}
                        </p>

                        <div class="mt-2 flex flex-wrap gap-2">
                            <span
                                class="inline-flex rounded-full bg-green-100
                                    px-3 py-1 text-xs font-bold capitalize
                                    text-green-700"
                            >
                                {{ str_replace(
                                    '_',
                                    ' ',
                                    $user->getRoleNames()->first()
                                ) }}
                            </span>

                            <span
                                class="inline-flex rounded-full bg-gray-100
                                    px-3 py-1 text-xs font-semibold
                                    capitalize text-gray-600"
                            >
                                {{ $user->account_status }}
                            </span>
                        </div>
                    </div>

                    {{-- User code --}}
                    <div
                        class="shrink-0 text-sm text-gray-500
                            sm:text-right"
                    >
                        User Code:

                        <span class="font-semibold text-gray-800">
                            {{ $user->user_code ?? 'Not assigned' }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Left column --}}
            <aside class="space-y-6">
                {{-- Photo form --}}
                <section
                    class="rounded-2xl border border-gray-200
                           bg-white p-5 shadow-sm"
                >
                    <h2 class="font-bold text-gray-900">
                        Profile Picture
                    </h2>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Select a JPG, PNG, or WebP image. Maximum size is
                        2 MB and minimum resolution is 100 × 100 pixels.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('profile.photo.update') }}"
                        enctype="multipart/form-data"
                        class="mt-4"
                    >
                        @csrf
                        @method('PATCH')

                        <input
                            x-ref="photoInput"
                            x-on:change="previewPhoto($event)"
                            type="file"
                            name="profile_picture"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            class="hidden"
                        >

                        <button
                            type="button"
                            x-on:click="$refs.photoInput.click()"
                            class="flex w-full items-center justify-center
                                   gap-2 rounded-xl border border-dashed
                                   border-green-300 bg-green-50
                                   px-4 py-3 text-sm font-semibold
                                   text-green-700 transition
                                   hover:border-green-500
                                   hover:bg-green-100"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903
                                       A5.002 5.002 0 0115.9 6
                                       H16a5 5 0 011 9.9
                                       M15 13l-3-3m0 0l-3 3
                                       m3-3v12"
                                />
                            </svg>

                            Select Picture
                        </button>

                        <p
                            x-show="fileName"
                            x-cloak
                            class="mt-3 truncate text-xs text-gray-600"
                        >
                            Selected:
                            <span
                                class="font-semibold"
                                x-text="fileName"
                            ></span>
                        </p>

                        @error('profile_picture')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <div
                            x-show="previewUrl"
                            x-cloak
                            class="mt-4 grid grid-cols-2 gap-2"
                        >
                            <button
                                type="submit"
                                class="rounded-xl bg-green-700
                                       px-4 py-2.5 text-xs font-semibold
                                       text-white transition
                                       hover:bg-green-800"
                            >
                                Save Picture
                            </button>

                            <button
                                type="button"
                                x-on:click="clearPreview()"
                                class="rounded-xl border border-gray-300
                                       px-4 py-2.5 text-xs font-semibold
                                       text-gray-700 transition
                                       hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>

                    @if ($user->profile_picture)
                        <form
                            method="POST"
                            action="{{ route('profile.photo.destroy') }}"
                            class="mt-3"
                            x-on:submit="
                                if (! confirm(
                                    'Remove your current profile picture?'
                                )) {
                                    $event.preventDefault()
                                }
                            "
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="w-full rounded-xl border
                                       border-red-200 bg-red-50
                                       px-4 py-2.5 text-xs font-semibold
                                       text-red-600 transition
                                       hover:bg-red-100"
                            >
                                Remove Current Picture
                            </button>
                        </form>
                    @endif
                </section>

                {{-- University details --}}
                <section
                    class="rounded-2xl border border-gray-200
                           bg-white p-5 shadow-sm"
                >
                    <h2 class="font-bold text-gray-900">
                        University Information
                    </h2>

                    <dl class="mt-4 divide-y divide-gray-100">
                        <div class="py-3 first:pt-0">
                            <dt
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-gray-400"
                            >
                                ID Number
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-gray-800">
                                {{ $user->id_number }}
                            </dd>
                        </div>

                        <div class="py-3">
                            <dt
                                class="text-xs font-semibold uppercase
                                       tracking-wide text-gray-400"
                            >
                                Campus
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-gray-800">
                                {{ $user->campus }}
                            </dd>
                        </div>

                        @if ($user->program)
                            <div class="py-3">
                                <dt
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-gray-400"
                                >
                                    Program
                                </dt>

                                <dd
                                    class="mt-1 text-sm font-medium
                                           text-gray-800"
                                >
                                    {{ $user->program }}
                                </dd>
                            </div>
                        @endif

                        @if ($user->year_level)
                            <div class="py-3">
                                <dt
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-gray-400"
                                >
                                    Year and Section
                                </dt>

                                <dd
                                    class="mt-1 text-sm font-medium
                                           text-gray-800"
                                >
                                    Year {{ $user->year_level }}

                                    @if ($user->section)
                                        — {{ $user->section }}
                                    @endif
                                </dd>
                            </div>
                        @endif

                        @if ($user->department)
                            <div class="py-3">
                                <dt
                                    class="text-xs font-semibold uppercase
                                           tracking-wide text-gray-400"
                                >
                                    Department
                                </dt>

                                <dd
                                    class="mt-1 text-sm font-medium
                                           text-gray-800"
                                >
                                    {{ $user->department }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>
            </aside>

            {{-- Main column --}}
            <main class="space-y-6 lg:col-span-2">
                <section
                    class="rounded-2xl border border-gray-200
                           bg-white p-5 shadow-sm sm:p-6"
                >
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Personal Information
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Update your name, email address, and contact
                            number.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('profile.update') }}"
                        class="mt-6"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label
                                    for="first_name"
                                    class="block text-sm font-semibold
                                           text-gray-700"
                                >
                                    First Name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="first_name"
                                    name="first_name"
                                    type="text"
                                    value="{{ old(
                                        'first_name',
                                        $user->first_name
                                    ) }}"
                                    required
                                    class="mt-2 block w-full rounded-xl
                                           border-gray-300 px-4 py-2.5
                                           text-sm shadow-sm
                                           focus:border-green-600
                                           focus:ring-green-600"
                                >

                                @error('first_name')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="middle_name"
                                    class="block text-sm font-semibold
                                           text-gray-700"
                                >
                                    Middle Name
                                </label>

                                <input
                                    id="middle_name"
                                    name="middle_name"
                                    type="text"
                                    value="{{ old(
                                        'middle_name',
                                        $user->middle_name
                                    ) }}"
                                    class="mt-2 block w-full rounded-xl
                                           border-gray-300 px-4 py-2.5
                                           text-sm shadow-sm
                                           focus:border-green-600
                                           focus:ring-green-600"
                                >

                                @error('middle_name')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="last_name"
                                    class="block text-sm font-semibold
                                           text-gray-700"
                                >
                                    Last Name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="last_name"
                                    name="last_name"
                                    type="text"
                                    value="{{ old(
                                        'last_name',
                                        $user->last_name
                                    ) }}"
                                    required
                                    class="mt-2 block w-full rounded-xl
                                           border-gray-300 px-4 py-2.5
                                           text-sm shadow-sm
                                           focus:border-green-600
                                           focus:ring-green-600"
                                >

                                @error('last_name')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="suffix"
                                    class="block text-sm font-semibold
                                           text-gray-700"
                                >
                                    Suffix
                                </label>

                                <input
                                    id="suffix"
                                    name="suffix"
                                    type="text"
                                    value="{{ old(
                                        'suffix',
                                        $user->suffix
                                    ) }}"
                                    placeholder="Jr., Sr., III"
                                    class="mt-2 block w-full rounded-xl
                                           border-gray-300 px-4 py-2.5
                                           text-sm shadow-sm
                                           focus:border-green-600
                                           focus:ring-green-600"
                                >

                                @error('suffix')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    for="email"
                                    class="block text-sm font-semibold
                                           text-gray-700"
                                >
                                    Email Address
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old(
                                        'email',
                                        $user->email
                                    ) }}"
                                    required
                                    autocomplete="email"
                                    class="mt-2 block w-full rounded-xl
                                           border-gray-300 px-4 py-2.5
                                           text-sm shadow-sm
                                           focus:border-green-600
                                           focus:ring-green-600"
                                >

                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    for="contact_number"
                                    class="block text-sm font-semibold
                                           text-gray-700"
                                >
                                    Contact Number
                                </label>

                                <input
                                    id="contact_number"
                                    name="contact_number"
                                    type="text"
                                    value="{{ old(
                                        'contact_number',
                                        $user->contact_number
                                    ) }}"
                                    placeholder="09XXXXXXXXX"
                                    maxlength="13"
                                    class="mt-2 block w-full rounded-xl
                                           border-gray-300 px-4 py-2.5
                                           text-sm shadow-sm
                                           focus:border-green-600
                                           focus:ring-green-600"
                                >

                                @error('contact_number')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div
                            class="mt-6 flex flex-col-reverse gap-3
                                   sm:flex-row sm:items-center
                                   sm:justify-end"
                        >
                            <a
                                href="{{ route('dashboard') }}"
                                class="rounded-xl border border-gray-300
                                       px-5 py-2.5 text-center text-sm
                                       font-semibold text-gray-700
                                       transition hover:bg-gray-50"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="rounded-xl bg-green-700
                                       px-6 py-2.5 text-sm font-semibold
                                       text-white shadow-sm transition
                                       hover:bg-green-800"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                </section>

                @include('profile.partials.update-password-form')

                @include('profile.partials.delete-user-form')
            </main>
        </div>
    </div>

    <script>
        function profilePhotoManager() {
            return {
                previewUrl: null,
                fileName: null,

                previewPhoto(event) {
                    const file = event.target.files[0];

                    if (!file) {
                        this.clearPreview();
                        return;
                    }

                    const allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    if (!allowedTypes.includes(file.type)) {
                        alert(
                            'Please select a JPG, PNG, or WebP image.'
                        );

                        event.target.value = '';
                        this.clearPreview();
                        return;
                    }

                    const maximumSize = 2 * 1024 * 1024;

                    if (file.size > maximumSize) {
                        alert(
                            'The selected image must not exceed 2 MB.'
                        );

                        event.target.value = '';
                        this.clearPreview();
                        return;
                    }

                    if (this.previewUrl) {
                        URL.revokeObjectURL(this.previewUrl);
                    }

                    this.previewUrl = URL.createObjectURL(file);
                    this.fileName = file.name;
                },

                clearPreview() {
                    if (this.previewUrl) {
                        URL.revokeObjectURL(this.previewUrl);
                    }

                    this.previewUrl = null;
                    this.fileName = null;

                    if (this.$refs.photoInput) {
                        this.$refs.photoInput.value = '';
                    }
                }
            };
        }
    </script>
</x-app-layout>