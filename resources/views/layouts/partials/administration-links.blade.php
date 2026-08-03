<div
    class="mb-3 mt-8 px-3 text-xs font-semibold uppercase
           tracking-wider text-green-300"
>
    Administration
</div>

<div class="space-y-1">
    @can('use scanner')
        <a
            href="{{ route('scanner.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3
                   text-sm font-medium transition
                   {{ request()->routeIs('scanner.*')
                        ? 'bg-white text-green-800 shadow-sm'
                        : 'text-green-100 hover:bg-green-700' }}"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7V5a1 1 0 011-1h2M17 4h2a1 1 0 011 1v2M20 17v2a1 1 0 01-1 1h-2M7 20H5a1 1 0 01-1-1v-2M8 8h3v3H8V8zm5 0h3v3h-3V8zM8 13h3v3H8v-3zm5 0h1v1h-1v-1zM15 13h1v3h-3v-1"/>
            </svg>
            Scanner
        </a>
    @endcan
    @can('view users')
        <a
            href="{{ route('users.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3
                   text-sm font-medium transition
                   {{ request()->routeIs('users.*')
                        ? 'bg-white text-green-800 shadow-sm'
                        : 'text-green-100 hover:bg-green-700' }}"
        >
            <img
                src="{{ asset('images/icons/users_icon.png') }}"
                alt=""
                aria-hidden="true"
                class="h-5 w-5 shrink-0 object-contain"
            >

            Users
        </a>
    @endcan

    @if (auth()->user()->hasRole('super_admin'))
        <a
            href="{{ route('staff-registration.manage') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3
                   text-sm font-medium transition
                   {{ request()->routeIs('staff-registration.*')
                        ? 'bg-white text-green-800 shadow-sm'
                        : 'text-green-100 hover:bg-green-700' }}"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm8-3v6m3-3h-6"/>
            </svg>
            Staff Registration
        </a>
    @endif

    @can('view maintenance')
        <a
            href="{{ route('maintenance.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3
                   text-sm font-medium transition
                   {{ request()->routeIs('maintenance.*')
                        ? 'bg-white text-green-800 shadow-sm'
                        : 'text-green-100 hover:bg-green-700' }}"
        >
            <img
                src="{{ asset('images/icons/maintenance_logo.png') }}"
                alt=""
                aria-hidden="true"
                class="h-5 w-5 shrink-0 object-contain"
            >

            Maintenance
        </a>
    @endcan

    @can('view reports')
        <a
            href="{{ route('reports.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3
                   text-sm font-medium transition
                   {{ request()->routeIs('reports.*')
                        ? 'bg-white text-green-800 shadow-sm'
                        : 'text-green-100 hover:bg-green-700' }}"
        >
            <img
                src="{{ asset('images/icons/reports_icon.png') }}"
                alt=""
                aria-hidden="true"
                class="h-5 w-5 shrink-0 object-contain"
            >

            Reports
        </a>
    @endcan

    @can('view activity logs')
        <a
            href="{{ route('audit-logs.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3
                   text-sm font-medium transition
                   {{ request()->routeIs('audit-logs.*')
                        ? 'bg-white text-green-800 shadow-sm'
                        : 'text-green-100 hover:bg-green-700' }}"
        >
            <img
                src="{{ asset('images/icons/audit_logo.png') }}"
                alt=""
                aria-hidden="true"
                class="h-5 w-5 shrink-0 object-contain"
            >

            Audit Logs
        </a>
    @endcan

    @can('manage settings')
        <a
            href="{{ route('settings.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3
                   text-sm font-medium transition
                   {{ request()->routeIs('settings.*')
                        ? 'bg-white text-green-800 shadow-sm'
                        : 'text-green-100 hover:bg-green-700' }}"
        >
            <img
                src="{{ asset('images/icons/settings_icon.png') }}"
                alt=""
                aria-hidden="true"
                class="h-5 w-5 shrink-0 object-contain"
            >

            Settings
        </a>
    @endcan
</div>