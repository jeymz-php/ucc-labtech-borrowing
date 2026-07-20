<section
    class="rounded-2xl border border-gray-200 bg-white
           p-5 shadow-sm sm:p-6"
>
    <div>
        <h2 class="text-lg font-bold text-gray-900">
            Update Password
        </h2>

        <p class="mt-1 text-sm leading-6 text-gray-500">
            Use a strong password that you do not use on another account.
        </p>
    </div>

    @if (session('status') === 'password-updated')
        <div
            class="mt-4 rounded-xl border border-green-200
                   bg-green-50 px-4 py-3 text-sm text-green-700"
        >
            Your password was updated successfully.
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('password.update') }}"
        class="mt-6 space-y-5"
        x-data="{
            showCurrent: false,
            showNew: false,
            showConfirmation: false
        }"
    >
        @csrf
        @method('PUT')

        {{-- Current password --}}
        <div>
            <label
                for="update_password_current_password"
                class="block text-sm font-semibold text-gray-700"
            >
                Current Password
            </label>

            <div class="relative mt-2">
                <input
                    id="update_password_current_password"
                    name="current_password"
                    x-bind:type="showCurrent ? 'text' : 'password'"
                    autocomplete="current-password"
                    class="block w-full rounded-xl border-gray-300
                           px-4 py-2.5 pr-12 text-sm shadow-sm
                           focus:border-green-600 focus:ring-green-600"
                >

                <button
                    type="button"
                    x-on:click="showCurrent = ! showCurrent"
                    class="absolute inset-y-0 right-0 flex w-12
                           items-center justify-center text-gray-400
                           transition hover:text-green-700"
                    x-bind:aria-label="
                        showCurrent
                            ? 'Hide current password'
                            : 'Show current password'
                    "
                >
                    <svg
                        x-show="! showCurrent"
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5
                               12 5c4.478 0 8.268 2.943
                               9.542 7-1.274 4.057-5.064 7
                               -9.542 7-4.477 0-8.268-2.943
                               -9.542-7z"
                        />
                    </svg>

                    <svg
                        x-show="showCurrent"
                        x-cloak
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8
                               M9.9 4.2A9.8 9.8 0 0112 4
                               c5 0 9 4 10 8a12.4 12.4 0 01-2.1 4.1
                               M6.6 6.6A11.8 11.8 0 002 12
                               c1 4 5 8 10 8a9.8 9.8 0 005.4-1.6"
                        />
                    </svg>
                </button>
            </div>

            @error('current_password', 'updatePassword')
                <p class="mt-1 text-xs text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- New password --}}
        <div>
            <label
                for="update_password_password"
                class="block text-sm font-semibold text-gray-700"
            >
                New Password
            </label>

            <div class="relative mt-2">
                <input
                    id="update_password_password"
                    name="password"
                    x-bind:type="showNew ? 'text' : 'password'"
                    autocomplete="new-password"
                    class="block w-full rounded-xl border-gray-300
                           px-4 py-2.5 pr-12 text-sm shadow-sm
                           focus:border-green-600 focus:ring-green-600"
                >

                <button
                    type="button"
                    x-on:click="showNew = ! showNew"
                    class="absolute inset-y-0 right-0 flex w-12
                           items-center justify-center text-gray-400
                           transition hover:text-green-700"
                >
                    <span
                        class="text-xs font-semibold"
                        x-text="showNew ? 'Hide' : 'Show'"
                    ></span>
                </button>
            </div>

            <p class="mt-1 text-xs leading-5 text-gray-500">
                Use at least 8 characters with uppercase, lowercase,
                numbers, and symbols.
            </p>

            @error('password', 'updatePassword')
                <p class="mt-1 text-xs text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Confirmation --}}
        <div>
            <label
                for="update_password_password_confirmation"
                class="block text-sm font-semibold text-gray-700"
            >
                Confirm New Password
            </label>

            <div class="relative mt-2">
                <input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    x-bind:type="
                        showConfirmation ? 'text' : 'password'
                    "
                    autocomplete="new-password"
                    class="block w-full rounded-xl border-gray-300
                           px-4 py-2.5 pr-12 text-sm shadow-sm
                           focus:border-green-600 focus:ring-green-600"
                >

                <button
                    type="button"
                    x-on:click="
                        showConfirmation = ! showConfirmation
                    "
                    class="absolute inset-y-0 right-0 flex w-12
                           items-center justify-center text-gray-400
                           transition hover:text-green-700"
                >
                    <span
                        class="text-xs font-semibold"
                        x-text="
                            showConfirmation ? 'Hide' : 'Show'
                        "
                    ></span>
                </button>
            </div>

            @error('password_confirmation', 'updatePassword')
                <p class="mt-1 text-xs text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="flex justify-end border-t border-gray-100 pt-5">
            <button
                type="submit"
                class="rounded-xl bg-green-700 px-6 py-2.5
                       text-sm font-semibold text-white shadow-sm
                       transition hover:bg-green-800"
            >
                Update Password
            </button>
        </div>
    </form>
</section>