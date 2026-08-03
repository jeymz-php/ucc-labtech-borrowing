<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="text-center lg:text-left">
            <p
                class="text-xs font-semibold uppercase tracking-wider
                       text-green-700"
            >
                Welcome back
            </p>

            <h1
                class="mt-1 text-2xl font-bold text-gray-900
                       sm:text-3xl"
            >
                Sign in to your account
            </h1>

            <p class="mt-2 text-xs leading-5 text-gray-500 sm:text-sm">
                Enter your registered email and password.
            </p>
        </div>

        <x-auth-session-status
            class="mt-3"
            :status="session('status')"
        />

        @if ($errors->any())
            <div
                class="mt-3 rounded-lg border border-red-200
                       bg-red-50 px-3 py-2 text-xs text-red-700"
            >
                The provided credentials could not be verified.
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('login') }}"
            class="mt-5 space-y-4"
        >
            @csrf

            <div>
                <label
                    for="email"
                    class="block text-xs font-semibold text-gray-700
                           sm:text-sm"
                >
                    Email Address
                </label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="name@ucc-caloocan.edu.ph"
                    class="mt-1.5 block w-full rounded-xl border-gray-300
                           px-3 py-2.5 text-sm shadow-sm
                           focus:border-green-600 focus:ring-green-600"
                >

                @error('email')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div x-data="{ showPassword: false }">
                <div class="flex items-center justify-between">
                    <label
                        for="password"
                        class="text-xs font-semibold text-gray-700 sm:text-sm"
                    >
                        Password
                    </label>

                    @if (Route::has('password.request'))
                        <a
                            href="{{ route('password.request') }}"
                            class="text-xs font-semibold text-green-700
                                hover:text-green-800"
                        >
                            Forgot password?
                        </a>
                    @endif
                </div>

                <div class="relative mt-1.5">
                    <input
                        id="password"
                        name="password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        class="block w-full rounded-xl border-gray-300
                            px-3 py-2.5 pr-11 text-sm shadow-sm
                            focus:border-green-600 focus:ring-green-600"
                    >

                    <button
                        type="button"
                        x-on:click="showPassword = ! showPassword"
                        class="absolute inset-y-0 right-0 flex w-11
                            items-center justify-center text-gray-400
                            transition hover:text-green-700"
                        x-bind:aria-label="
                            showPassword
                                ? 'Hide password'
                                : 'Show password'
                        "
                    >
                        {{-- Eye --}}
                        <svg
                            x-show="! showPassword"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0
                                3 3 0 016 0z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2.458 12C3.732 7.943
                                7.523 5 12 5c4.478 0
                                8.268 2.943 9.542 7
                                -1.274 4.057-5.064 7
                                -9.542 7-4.477 0
                                -8.268-2.943-9.542-7z"
                            />
                        </svg>

                        {{-- Eye slash --}}
                        <svg
                            x-show="showPassword"
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
                                d="M13.875 18.825A10.05 10.05 0
                                0112 19c-4.478 0-8.268-2.943
                                -9.542-7a9.97 9.97 0
                                012.293-3.95m3.249-2.09
                                A9.953 9.953 0 0112 5
                                c4.478 0 8.268 2.943
                                9.542 7a9.956 9.956 0
                                01-4.043 5.197M15 12
                                a3 3 0 11-4.243-2.757
                                M3 3l18 18"
                            />
                        </svg>
                    </button>
                </div>

                @error('password')
                    <p class="mt-1 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <label class="flex items-center gap-2">
                <input
                    id="remember_me"
                    name="remember"
                    type="checkbox"
                    class="rounded border-gray-300 text-green-700
                           focus:ring-green-600"
                >

                <span class="text-xs text-gray-600 sm:text-sm">
                    Keep me signed in
                </span>
            </label>

            <button
                type="submit"
                class="flex w-full items-center justify-center rounded-xl
                       bg-green-700 px-4 py-2.5 text-sm font-semibold
                       text-white shadow-sm transition hover:bg-green-800
                       focus:outline-none focus:ring-2 focus:ring-green-600
                       focus:ring-offset-2"
            >
                Sign In
            </button>
        </form>

        <div class="mt-5 border-t border-gray-200 pt-4 text-center">
            <p class="text-xs text-gray-600 sm:text-sm">
                Borrowing equipment as a student, professor, or faculty/staff member?
            </p>

            <a
                href="{{ route('guest-borrowings.create') }}"
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl
                       border border-green-700 px-4 py-2.5 text-sm font-semibold
                       text-green-700 transition hover:bg-green-50"
            >
                Continue as Guest Borrower
            </a>

            <p class="mt-2 text-[10px] leading-4 text-gray-500 sm:text-xs">
                No borrower account or password is required. Staff administrators must sign in above.
            </p>
        </div>

        <div class="mt-4 text-center">
            <button
                type="button"
                x-on:click="$dispatch('open-modal', 'user-guide')"
                class="inline-flex items-center justify-center gap-2 rounded-xl
                       border border-green-200 bg-green-50 px-4 py-2.5
                       text-sm font-semibold text-green-800 transition
                       hover:border-green-300 hover:bg-green-100
                       focus:outline-none focus:ring-2 focus:ring-green-600
                       focus:ring-offset-2"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 6.253v13M12 6.253C10.832 5.477
                           9.246 5 7.5 5S4.168 5.477 3 6.253v13
                           C4.168 18.477 5.754 18 7.5 18s3.332.477
                           4.5 1.253m0-13C13.168 5.477 14.754 5
                           16.5 5s3.332.477 4.5 1.253v13
                           C19.832 18.477 18.246 18 16.5 18
                           s-3.332.477-4.5 1.253"
                    />
                </svg>

                View User Guide
            </button>
        </div>

    </div>
</x-guest-layout>