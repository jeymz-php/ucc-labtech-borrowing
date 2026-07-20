<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ config(
            'app.name',
            'UCC LabTech Borrowing System'
        ) }}
    </title>

    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    >

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body
    class="bg-gray-50 font-sans text-gray-900 antialiased"
    x-data="{ sidebarOpen: false }"
>
    <div class="min-h-screen">
        {{-- Mobile sidebar overlay --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 transform
                   flex-col bg-green-800 text-white shadow-xl
                   transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen
                ? 'translate-x-0'
                : '-translate-x-full'"
        >
            {{-- Logo --}}
            <div
                class="flex h-24 items-center gap-4 border-b border-green-700 px-6"
                       border-green-700 px-6"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center
                        overflow-hidden rounded-xl bg-white p-1"
                >
                    <img
                        src="{{ asset('images/UCC_Logo.png') }}"
                        alt="University of Caloocan City Logo"
                        class="h-full w-full object-contain"
                    >
                </div>

                <div>
                    <div class="text-base font-bold">
                        UCC LabTech
                    </div>

                    <div class="text-xs text-green-200">
                        Borrowing Management
                    </div>
                </div>

                <button
                    type="button"
                    class="ml-auto rounded-lg p-2 hover:bg-green-700 lg:hidden"
                    @click="sidebarOpen = false"
                >
                    ✕
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-4 py-6">
                <div
                    class="mb-3 px-3 text-xs font-semibold uppercase
                           tracking-wider text-green-300"
                >
                    Main Menu
                </div>

                <div class="space-y-1">
                    @can('view dashboard')
                        <a
                            href="{{ route('dashboard') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-2.5
                                text-sm font-semibold transition duration-200
                                {{ request()->routeIs('dashboard')
                                        ? 'bg-green-800 text-white shadow-sm'
                                        : 'text-green-50 hover:bg-green-700 hover:text-white' }}"
                        >
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center"
                            >
                                <img
                                    src="{{ asset('images/icons/dashboard_icon.png') }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="h-5 w-5 object-contain transition duration-300
                                        group-hover:scale-110"
                                >
                            </span>

                            <span>Dashboard</span>
                        </a>
                    @endcan

                    @can('view categories')
                        <a
                            href="{{ route('categories.index') }}"
                            class="flex items-center gap-3 rounded-xl px-4 py-3
                                   text-sm font-medium transition
                                   {{ request()->routeIs('categories.*')
                                       ? 'bg-white text-green-800 shadow-sm'
                                       : 'text-green-100 hover:bg-green-700' }}"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 11H5m14-7H5a2 2 0 00-2
                                       2v12a2 2 0 002 2h14a2 2 0
                                       002-2V6a2 2 0 00-2-2z"
                                />
                            </svg>

                            Categories
                        </a>
                    @endcan

                    @can('view items')
                        <a
                            href="{{ route('items.index') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-2.5
                                text-sm font-semibold transition duration-200
                                {{ request()->routeIs('items.*') || request()->routeIs('item-units.*')
                                    ? 'bg-white text-green-800 shadow-sm'
                                    : 'text-green-50 hover:bg-green-700 hover:text-white' }}"
                        >
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center">
                                <img
                                    src="{{ asset('images/icons/inventory_icon.png') }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="h-5 w-5 object-contain transition duration-300 group-hover:scale-110"
                                >
                            </span>
                            <span>Inventory</span>
                        </a>
                    @endcan

                    @can('view all borrowings')
                        <a
                            href="#"
                            class="flex items-center gap-3 rounded-xl px-4 py-3
                                   text-sm font-medium text-green-100
                                   transition hover:bg-green-700"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2
                                       2 0 002 2h10a2 2 0 002-2V7a2
                                       2 0 00-2-2h-2M9 5a2 2 0
                                       002 2h2a2 2 0 002-2M9 5a2
                                       2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>

                            Borrowings

                            <span
                                class="ml-auto rounded-full bg-green-600
                                       px-2 py-0.5 text-[10px] uppercase"
                            >
                                Soon
                            </span>
                        </a>
                    @endcan
                </div>

                <div
                    class="mb-3 mt-8 px-3 text-xs font-semibold uppercase
                           tracking-wider text-green-300"
                >
                    Administration
                </div>

                <div class="space-y-1">
                    @can('view users')
                        <a
                            href="#"
                            class="flex items-center gap-3 rounded-xl px-4 py-3
                                   text-sm font-medium text-green-100
                                   transition hover:bg-green-700"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857
                                       M17 20H7m10 0v-2c0-.656-.126-1.283
                                       -.356-1.857M7 20H2v-2a3 3 0
                                       015.356-1.857M7 20v-2c0-.656.126
                                       -1.283.356-1.857m0 0a5.002 5.002
                                       0 019.288 0M15 7a3 3 0 11-6
                                       0 3 3 0 016 0zm6 3a2 2 0
                                       11-4 0 2 2 0 014 0zM7 10a2
                                       2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>

                            Users
                        </a>
                    @endcan

                    @can('view reports')
                        <a
                            href="#"
                            class="flex items-center gap-3 rounded-xl px-4 py-3
                                   text-sm font-medium text-green-100
                                   transition hover:bg-green-700"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2
                                       10H7a2 2 0 01-2-2V5a2 2
                                       0 012-2h5.586a1 1 0
                                       01.707.293l3.414 3.414a1 1
                                       0 01.293.707V19a2 2 0
                                       01-2 2z"
                                />
                            </svg>

                            Reports
                        </a>
                    @endcan

                    @can('manage settings')
                        <a
                            href="#"
                            class="flex items-center gap-3 rounded-xl px-4 py-3
                                   text-sm font-medium text-green-100
                                   transition hover:bg-green-700"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10.325 4.317c.426-1.756
                                       2.924-1.756 3.35 0a1.724
                                       1.724 0 002.573 1.066c1.543
                                       -.94 3.31.826 2.37 2.37a1.724
                                       1.724 0 001.065 2.572c1.756
                                       .426 1.756 2.924 0 3.35a1.724
                                       1.724 0 00-1.066 2.573c.94
                                       1.543-.826 3.31-2.37
                                       2.37a1.724 1.724 0
                                       00-2.572 1.065c-.426 1.756
                                       -2.924 1.756-3.35 0a1.724
                                       1.724 0 00-2.573-1.066c-1.543
                                       .94-3.31-.826-2.37-2.37a1.724
                                       1.724 0 00-1.065-2.572c-1.756
                                       -.426-1.756-2.924 0-3.35a1.724
                                       1.724 0 001.066-2.573c-.94
                                       -1.543.826-3.31 2.37-2.37.996
                                       .608 2.296.07 2.572-1.065z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3
                                       3 0 016 0z"
                                />
                            </svg>

                            Settings
                        </a>
                    @endcan
                </div>
            </nav>

            {{-- Sidebar user --}}
            <div class="border-t border-green-700 p-4">
                <div class="flex items-center gap-3 rounded-xl bg-green-700 p-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center
                               rounded-full bg-white font-bold text-green-800"
                    >
                        {{ auth()->user()->initials }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold">
                            {{ auth()->user()->full_name }}
                        </div>

                        <div class="truncate text-xs text-green-200">
                            {{ auth()->user()->roles->first()?->name
                                ? ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        auth()->user()->roles->first()->name
                                    )
                                )
                                : 'User' }}
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main area --}}
        <div class="lg:pl-72">
            {{-- Top navigation --}}
            <header
                class="sticky top-0 z-50 overflow-visible border-b
                    border-gray-200 bg-white/95 backdrop-blur"
            >
                <div
                    class="flex h-20 items-center justify-between
                        overflow-visible px-4 sm:px-6 lg:px-8"
                >
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            class="rounded-lg border border-gray-200 p-2
                                   text-gray-600 hover:bg-gray-50 lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>

                        <div>
                            <div class="text-sm text-gray-500">
                                University of Caloocan City
                            </div>

                            <div class="font-semibold text-gray-900">
                                LabTech Borrowing Management System
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            class="hidden text-right sm:block"
                        >
                            <div class="text-sm font-semibold text-gray-900">
                                {{ auth()->user()->full_name }}
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ auth()->user()->email }}
                            </div>
                        </div>

                        <div
                            x-data="{ open: false }"
                            @keydown.escape.window="open = false"
                            class="relative"
                        >
                            <button
                                type="button"
                                @click.stop="open = !open"
                                class="group relative flex h-11 w-11 items-center
                                    justify-center overflow-hidden rounded-full
                                    bg-green-100 text-sm font-bold text-green-700
                                    ring-2 ring-transparent transition duration-500
                                    hover:ring-green-200 focus:outline-none
                                    focus:ring-green-300"
                                :aria-expanded="open"
                                aria-haspopup="true"
                                aria-label="Open account menu"
                            >
                                @if (auth()->user()->profile_picture_url)
                                    <img
                                        src="{{ auth()->user()->profile_picture_url }}"
                                        alt="{{ auth()->user()->full_name }}"
                                        class="absolute inset-0 h-full w-full object-cover
                                            transition duration-500 ease-in-out"
                                        :class="open
                                            ? 'rotate-[360deg] scale-0 opacity-0'
                                            : 'rotate-0 scale-100 opacity-100'"
                                    >
                                @else
                                    <span
                                        class="absolute inset-0 flex items-center justify-center
                                            transition duration-500 ease-in-out"
                                        :class="open
                                            ? '-rotate-[360deg] scale-0 opacity-0'
                                            : 'rotate-0 scale-100 opacity-100'"
                                    >
                                        {{ auth()->user()->initials }}
                                    </span>
                                @endif

                                <img
                                    src="{{ asset('images/icons/settings_icon.png') }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="absolute h-5 w-5 object-contain
                                        transition duration-500 ease-in-out"
                                    :class="open
                                        ? 'rotate-[360deg] scale-100 opacity-100'
                                        : 'rotate-0 scale-0 opacity-0'"
                                >
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                class="absolute right-0 top-full z-[9999] mt-3 w-64
                                    origin-top-right overflow-hidden rounded-2xl
                                    border border-gray-200 bg-white shadow-2xl"
                                style="display: none;"
                            >
                                <div class="border-b border-gray-100 px-4 py-4">
                                    <p class="truncate text-sm font-bold text-gray-900">
                                        {{ auth()->user()->full_name }}
                                    </p>

                                    <p class="mt-1 truncate text-xs text-gray-500">
                                        {{ auth()->user()->email }}
                                    </p>

                                    <span
                                        class="mt-2 inline-flex rounded-full bg-green-100
                                            px-2.5 py-1 text-[10px] font-bold capitalize
                                            text-green-700"
                                    >
                                        {{ str_replace(
                                            '_',
                                            ' ',
                                            auth()->user()->getRoleNames()->first() ?? 'user'
                                        ) }}
                                    </span>
                                </div>

                                <div class="p-2">
                                    <a
                                        href="{{ route('profile.edit') }}"
                                        class="flex items-center gap-3 rounded-xl px-3 py-2.5
                                            text-sm font-medium text-gray-700 transition
                                            hover:bg-green-50 hover:text-green-700"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"
                                            />
                                        </svg>

                                        <span>My Profile</span>
                                    </a>

                                    <a
                                        href="{{ route('dashboard') }}"
                                        class="flex items-center gap-3 rounded-xl px-3 py-2.5
                                            text-sm font-medium text-gray-700 transition
                                            hover:bg-green-50 hover:text-green-700"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                            />
                                        </svg>

                                        <span>Dashboard</span>
                                    </a>
                                </div>

                                <div class="border-t border-gray-100 p-2">
                                    <form
                                        method="POST"
                                        action="{{ route('logout') }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="flex w-full items-center gap-3 rounded-xl
                                                px-3 py-2.5 text-left text-sm font-medium
                                                text-red-600 transition hover:bg-red-50"
                                        >
                                            <svg
                                                class="h-5 w-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                                />
                                            </svg>

                                            <span>Sign Out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @if (isset($header))
                <header class="border-b border-gray-200 bg-white">
                    <div
                        class="mx-auto w-full max-w-[1600px]
                            px-4 py-5
                            sm:px-6
                            lg:px-8
                            xl:px-10"
                    >
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="min-h-0 flex-1 overflow-y-auto bg-gray-50">
                <div
                    class="mx-auto w-full max-w-[1600px]
                        px-4 py-6
                        sm:px-6
                        lg:px-8
                        xl:px-10"
                >
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>
</html>