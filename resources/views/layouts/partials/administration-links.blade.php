<div
    class="mb-3 mt-8 px-3 text-xs font-semibold uppercase
           tracking-wider text-green-300"
>
    Administration
</div>

<div class="space-y-1">
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