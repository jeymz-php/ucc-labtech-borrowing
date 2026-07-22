<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ $archivedMode ? 'Archived Users' : 'Users' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $archivedMode
                        ? 'Review and restore archived user accounts.'
                        : 'Manage user accounts, roles, and access status.' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if ($archivedMode)
                    <a href="{{ route('users.index') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Active Users
                    </a>
                @else
                    @can('archive users')
                        <a href="{{ route('users.archived') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Archived ({{ $statistics['archived'] }})
                        </a>
                    @endcan

                    @can('create users')
                        <a href="{{ route('users.create') }}" class="rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                            Add User
                        </a>
                    @endcan
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('temporary_password'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-bold">Temporary Password</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <code class="rounded-lg bg-white px-3 py-2 text-base font-bold text-gray-900 ring-1 ring-amber-200">
                        {{ session('temporary_password') }}
                    </code>
                    <p>{{ session('mail_status') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        @unless ($archivedMode)
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    ['Total', $statistics['total'], 'bg-blue-50 text-blue-700'],
                    ['Active', $statistics['active'], 'bg-green-50 text-green-700'],
                    ['Pending', $statistics['pending'], 'bg-amber-50 text-amber-700'],
                    ['Suspended', $statistics['suspended'], 'bg-red-50 text-red-700'],
                    ['Archived', $statistics['archived'], 'bg-gray-100 text-gray-700'],
                ] as [$label, $value, $classes])
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $classes }}">{{ $label }}</div>
                        <p class="mt-3 text-3xl font-black text-gray-900">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        @endunless

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ $archivedMode ? route('users.archived') : route('users.index') }}" class="grid gap-3 lg:grid-cols-6">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name, ID, code, or email"
                    class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600 lg:col-span-2"
                >

                @unless ($archivedMode)
                    <select name="role" class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        <option value="">All roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        <option value="">All statuses</option>
                        @foreach (['active', 'pending', 'suspended', 'inactive'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>

                    <select name="campus" class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        <option value="">All campuses</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus }}" @selected(request('campus') === $campus)>
                                {{ $campus }}
                            </option>
                        @endforeach
                    </select>
                @endunless

                <div class="flex gap-2">
                    <button class="flex-1 rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                        Filter
                    </button>
                    <a href="{{ $archivedMode ? route('users.archived') : route('users.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">University Information</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Last Login</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $managedUser)
                            @php
                                $roleName = $managedUser->getRoleNames()->first() ?? 'No role';
                                $statusClasses = match ($managedUser->account_status) {
                                    'active' => 'bg-green-100 text-green-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'suspended' => 'bg-red-100 text-red-700',
                                    'inactive' => 'bg-gray-100 text-gray-700',
                                    'archived' => 'bg-gray-200 text-gray-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };

                                $targetIsManageable = auth()->user()->hasRole('super_admin')
                                    || auth()->id() === $managedUser->id
                                    || ! $managedUser->hasAnyRole(['admin', 'super_admin']);
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-green-100 font-bold text-green-700">
                                            @if ($managedUser->profile_picture_url)
                                                <img src="{{ $managedUser->profile_picture_url }}" alt="{{ $managedUser->full_name }}" class="h-full w-full object-cover">
                                            @else
                                                {{ $managedUser->initials }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-900">{{ $managedUser->full_name }}</p>
                                            <p class="truncate text-xs text-gray-500">{{ $managedUser->email }}</p>
                                            <p class="mt-1 text-xs text-gray-400">{{ $managedUser->user_code }} · {{ $managedUser->id_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold capitalize text-blue-700">
                                        {{ str_replace('_', ' ', $roleName) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    <p>{{ $managedUser->campus ?: '—' }}</p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ $managedUser->program ?: $managedUser->department ?: '—' }}
                                        @if ($managedUser->year_level || $managedUser->section)
                                            · Year {{ $managedUser->year_level }} {{ $managedUser->section }}
                                        @endif
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold uppercase {{ $statusClasses }}">
                                        {{ $managedUser->account_status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    {{ $managedUser->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        @if ($archivedMode)
                                            @if ($targetIsManageable)
                                                @can('restore users')
                                                <form method="POST" action="{{ route('users.restore', $managedUser->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="rounded-lg bg-green-700 px-3 py-2 text-xs font-semibold text-white hover:bg-green-800">
                                                        Restore
                                                    </button>
                                                </form>
                                                @endcan
                                            @endif
                                        @else
                                            @if ($targetIsManageable)
                                            @can('edit users')
                                                <a href="{{ route('users.edit', $managedUser) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                                    Edit
                                                </a>
                                            @endcan

                                            @if ($managedUser->account_status !== 'active')
                                                @can('activate users')
                                                    <form method="POST" action="{{ route('users.activate', $managedUser) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="rounded-lg bg-green-700 px-3 py-2 text-xs font-semibold text-white hover:bg-green-800">
                                                            Activate
                                                        </button>
                                                    </form>
                                                @endcan
                                            @elseif (auth()->id() !== $managedUser->id)
                                                @can('suspend users')
                                                    <form method="POST" action="{{ route('users.suspend', $managedUser) }}" onsubmit="return confirm('Suspend this user account?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600">
                                                            Suspend
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif

                                            @if (auth()->id() !== $managedUser->id)
                                                @can('archive users')
                                                    <form method="POST" action="{{ route('users.destroy', $managedUser) }}" onsubmit="return confirm('Archive this user account?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                                            Archive
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                    No user accounts matched the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-gray-100 px-5 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
