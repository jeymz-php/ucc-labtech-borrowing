<x-guest-layout>
    <div class="w-full">
        <div class="mb-8 text-center">
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center
                       rounded-2xl bg-green-100 text-green-700"
            >
                <svg
                    class="h-8 w-8"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 11c0-1.105.895-2 2-2s2 .895 2 2
                           -.895 2-2 2-2-.895-2-2zm0 0V7a4 4
                           0 118 0v4m-10 9h8a2 2 0 002-2v-5
                           a2 2 0 00-2-2H6a2 2 0 00-2 2v5
                           a2 2 0 002 2h4z"
                    />
                </svg>
            </div>

            <h1 class="mt-5 text-2xl font-bold text-gray-900">
                Change Your Password
            </h1>

            <p class="mt-2 text-sm leading-6 text-gray-500">
                You are using a temporary password. Create a new password
                before continuing to the system.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('password.force.update') }}"
            class="space-y-5"
        >
            @csrf
            @method('PUT')

            <div>
                <label
                    for="password"
                    class="block text-sm font-semibold text-gray-700"
                >
                    New Password
                </label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autofocus
                    autocomplete="new-password"
                    class="mt-2 block w-full rounded-xl border-gray-300
                           px-4 py-3 text-sm shadow-sm
                           focus:border-green-600 focus:ring-green-600"
                    placeholder="Enter your new password"
                >

                @error('password')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm font-semibold text-gray-700"
                >
                    Confirm New Password
                </label>

                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="mt-2 block w-full rounded-xl border-gray-300
                           px-4 py-3 text-sm shadow-sm
                           focus:border-green-600 focus:ring-green-600"
                    placeholder="Re-enter your new password"
                >
            </div>

            <div
                class="rounded-xl border border-amber-200 bg-amber-50
                       px-4 py-3"
            >
                <p class="text-xs leading-5 text-amber-800">
                    Use at least eight characters with uppercase and
                    lowercase letters and a number.
                </p>
            </div>

            <button
                type="submit"
                class="flex w-full items-center justify-center rounded-xl
                       bg-green-700 px-5 py-3 text-sm font-semibold
                       text-white shadow-sm transition
                       hover:bg-green-800 focus:outline-none
                       focus:ring-2 focus:ring-green-600
                       focus:ring-offset-2"
            >
                Change Password and Continue
            </button>
        </form>

        <form
            method="POST"
            action="{{ route('logout') }}"
            class="mt-4"
        >
            @csrf

            <button
                type="submit"
                class="w-full text-center text-sm font-semibold
                       text-gray-500 transition hover:text-red-600"
            >
                Sign out
            </button>
        </form>
    </div>
</x-guest-layout>