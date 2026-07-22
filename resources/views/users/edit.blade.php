<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Edit User</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Update {{ $managedUser->full_name }}'s account information.
                </p>
            </div>

            <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-bold uppercase text-gray-700">
                {{ $managedUser->account_status }}
            </span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-green-100 font-bold text-green-700">
                        @if ($managedUser->profile_picture_url)
                            <img src="{{ $managedUser->profile_picture_url }}" alt="{{ $managedUser->full_name }}" class="h-full w-full object-cover">
                        @else
                            {{ $managedUser->initials }}
                        @endif
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">{{ $managedUser->full_name }}</p>
                        <p class="text-sm text-gray-500">{{ $managedUser->user_code }} · {{ $managedUser->id_number }}</p>
                    </div>
                </div>
                <div class="text-sm text-gray-500">
                    Last login:
                    <span class="font-semibold text-gray-700">
                        {{ $managedUser->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}
                    </span>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ route('users.update', $managedUser) }}">
            @csrf
            @method('PUT')
            @include('users._form')
        </form>
    </div>
</x-app-layout>
