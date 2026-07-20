<section
    x-data="{
        confirmDeletion: false,
        showPassword: false
    }"
    class="rounded-2xl border border-red-200 bg-white
           p-5 shadow-sm sm:p-6"
>
    <div
        class="flex flex-col gap-4 sm:flex-row
               sm:items-start sm:justify-between"
    >
        <div>
            <h2 class="text-lg font-bold text-red-700">
                Delete Account
            </h2>

            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">
                Permanently delete your account and associated information.
                This action cannot be undone.
            </p>
        </div>

        <button
            type="button"
            x-on:click="confirmDeletion = true"
            class="shrink-0 rounded-xl border border-red-600
                   bg-red-600 px-5 py-2.5 text-sm font-semibold
                   text-white transition hover:bg-red-700"
        >
            Delete Account
        </button>
    </div>

    {{-- Confirmation modal --}}
    <div
        x-show="confirmDeletion"
        x-cloak
        x-on:keydown.escape.window="confirmDeletion = false"
        class="fixed inset-0 z-[100] flex items-center justify-center
               bg-black/60 p-4"
    >
        <div
            x-on:click.outside="confirmDeletion = false"
            class="w-full max-w-md overflow-hidden rounded-2xl
                   bg-white shadow-2xl"
        >
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-lg font-bold text-gray-900">
                    Confirm Account Deletion
                </h3>

                <p class="mt-1 text-sm leading-6 text-gray-500">
                    Enter your current password to permanently delete
                    your account.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('profile.destroy') }}"
                class="p-5"
            >
                @csrf
                @method('DELETE')

                <div>
                    <label
                        for="delete_account_password"
                        class="block text-sm font-semibold text-gray-700"
                    >
                        Current Password
                    </label>

                    <div class="relative mt-2">
                        <input
                            id="delete_account_password"
                            name="password"
                            x-bind:type="
                                showPassword ? 'text' : 'password'
                            "
                            autocomplete="current-password"
                            class="block w-full rounded-xl border-gray-300
                                   px-4 py-2.5 pr-12 text-sm shadow-sm
                                   focus:border-red-500 focus:ring-red-500"
                        >

                        <button
                            type="button"
                            x-on:click="
                                showPassword = ! showPassword
                            "
                            class="absolute inset-y-0 right-0 flex w-12
                                   items-center justify-center
                                   text-xs font-semibold text-gray-500
                                   hover:text-red-600"
                            x-text="showPassword ? 'Hide' : 'Show'"
                        ></button>
                    </div>

                    @error('password', 'userDeletion')
                        <p class="mt-2 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div
                    class="mt-6 flex flex-col-reverse gap-3
                           sm:flex-row sm:justify-end"
                >
                    <button
                        type="button"
                        x-on:click="confirmDeletion = false"
                        class="rounded-xl border border-gray-300
                               px-5 py-2.5 text-sm font-semibold
                               text-gray-700 transition hover:bg-gray-50"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-red-600 px-5 py-2.5
                               text-sm font-semibold text-white
                               transition hover:bg-red-700"
                    >
                        Permanently Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>