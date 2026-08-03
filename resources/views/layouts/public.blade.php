<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'UCC LabTech Borrowing Management System') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased" x-data>
    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('guest-borrowings.create') }}" class="flex min-w-0 items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white p-1 shadow-sm ring-1 ring-gray-200">
                    <img src="{{ asset('images/UCC_Logo.png') }}" alt="University of Caloocan City Logo" class="h-full w-full object-contain">
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold text-gray-900 sm:text-base">UCC LabTech</span>
                    <span class="block truncate text-xs text-gray-500">Borrowing Management System</span>
                </span>
            </a>

            <div class="flex items-center gap-2">
                <button type="button" x-on:click="$dispatch('open-modal', 'user-guide')" class="hidden rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 sm:inline-flex">
                    User Guide
                </button>
                <a href="{{ route('login') }}" class="rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800">
                    Staff Login
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
        {{ $slot }}
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>© {{ date('Y') }} University of Caloocan City</p>
            <button type="button" x-on:click="$dispatch('open-modal', 'user-guide')" class="text-left font-semibold text-green-700 hover:text-green-800">Open User Guide</button>
        </div>
    </footer>

    <x-user-guide-modal />
</body>
</html>
