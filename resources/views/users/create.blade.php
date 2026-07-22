<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Create User</h1>
            <p class="mt-1 text-sm text-gray-500">
                Create a new account and generate a temporary password.
            </p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl">
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('users._form')
        </form>
    </div>
</x-app-layout>
