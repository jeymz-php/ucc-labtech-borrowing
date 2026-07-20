<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="text-center lg:text-left">
            <p
                class="text-xs font-semibold uppercase tracking-wider
                       text-green-700"
            >
                Account Security
            </p>

            <h1 class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">
                Create a new password
            </h1>

            <p class="mt-2 text-xs leading-5 text-gray-500 sm:text-sm">
                You signed in using a temporary password. Create your
                permanent password before continuing.
            </p>
        </div>

        @if ($errors->any())
            <div
                class="mt-4 rounded-lg border border-red-200
                       bg-red-50 px-3 py-2"
            >
                <ul class="list-inside list-disc text-xs text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('password.force.update') }}"
            class="mt-5 space-y-4"
            x-data="{
                showCurrent: false,
                showNew: false,
                showConfirmation: false
            }"
        >
            @csrf
            @method('PUT')

            <div>
                <label
                    for="current_password"
                    class="block text-xs font-semibold text-gray-700 sm:text-sm"
                >
                    Temporary Password
                </label>

                <div class="relative mt-1.5">
                    <input
                        id="current_password"
                        name="current_password"
                        x-bind:type="showCurrent ? 'text' : 'password'"
                        required
                        autofocus
                        autocomplete="current-password"
                        class="block w-full rounded-xl border-gray-300
                            px-3 py-2.5 pr-11 text-sm shadow-sm
                            focus:border-green-600 focus:ring-green-600"
                    >

                    <button
                        type="button"
                        x-on:click="showCurrent = ! showCurrent"
                        class="absolute inset-y-0 right-0 flex w-11
                            items-center justify-center text-gray-400
                            hover:text-green-700"
                    >
                        <span
                            class="text-xs font-semibold"
                            x-text="showCurrent ? 'Hide' : 'Show'"
                        ></span>
                    </button>
                </div>
            </div>

            <div>
                <label
                    for="password"
                    class="block text-xs font-semibold text-gray-700 sm:text-sm"
                >
                    New Password
                </label>

                <div class="relative mt-1.5">
                    <input
                        id="password"
                        name="password"
                        x-bind:type="showNew ? 'text' : 'password'"
                        required
                        autocomplete="new-password"
                        class="block w-full rounded-xl border-gray-300
                            px-3 py-2.5 pr-11 text-sm shadow-sm
                            focus:border-green-600 focus:ring-green-600"
                    >

                    <button
                        type="button"
                        x-on:click="showNew = ! showNew"
                        class="absolute inset-y-0 right-0 flex w-11
                            items-center justify-center text-gray-400
                            hover:text-green-700"
                    >
                        <span
                            class="text-xs font-semibold"
                            x-text="showNew ? 'Hide' : 'Show'"
                        ></span>
                    </button>
                </div>
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="block text-xs font-semibold text-gray-700 sm:text-sm"
                >
                    Confirm New Password
                </label>

                <div class="relative mt-1.5">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        x-bind:type="
                            showConfirmation
                                ? 'text'
                                : 'password'
                        "
                        required
                        autocomplete="new-password"
                        class="block w-full rounded-xl border-gray-300
                            px-3 py-2.5 pr-11 text-sm shadow-sm
                            focus:border-green-600 focus:ring-green-600"
                    >

                    <button
                        type="button"
                        x-on:click="
                            showConfirmation = ! showConfirmation
                        "
                        class="absolute inset-y-0 right-0 flex w-11
                            items-center justify-center text-gray-400
                            hover:text-green-700"
                    >
                        <span
                            class="text-xs font-semibold"
                            x-text="
                                showConfirmation
                                    ? 'Hide'
                                    : 'Show'
                            "
                        ></span>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                class="flex w-full items-center justify-center rounded-xl
                       bg-green-700 px-4 py-2.5 text-sm font-semibold
                       text-white transition hover:bg-green-800
                       focus:outline-none focus:ring-2 focus:ring-green-600
                       focus:ring-offset-2"
            >
                Save New Password
            </button>
        </form>
    </div>
</x-guest-layout>