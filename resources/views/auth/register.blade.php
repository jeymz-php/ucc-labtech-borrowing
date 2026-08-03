<x-public-layout>
    <div class="mx-auto max-w-xl rounded-3xl border border-gray-200 bg-white p-7 text-center shadow-sm sm:p-10">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-green-100 text-green-700">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19a6 6 0 00-12 0m6-9a4 4 0 100-8 4 4 0 000 8zm9 9v-1a5 5 0 00-5-5m2-9a4 4 0 010 7.75" />
            </svg>
        </div>

        <h1 class="mt-5 text-2xl font-extrabold text-gray-900">Borrower Accounts Are No Longer Required</h1>
        <p class="mt-3 text-sm leading-6 text-gray-600">
            Students, professors, and faculty or staff may submit a borrowing request through the Guest Borrower portal without creating an account.
        </p>

        <div class="mt-7 grid gap-3 sm:grid-cols-2">
            <a href="{{ route('guest-borrowings.create') }}" class="rounded-xl bg-green-700 px-5 py-3 text-sm font-bold text-white hover:bg-green-800">
                Continue as Guest Borrower
            </a>
            <a href="{{ route('login') }}" class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">
                Staff Login
            </a>
        </div>
    </div>
</x-public-layout>
