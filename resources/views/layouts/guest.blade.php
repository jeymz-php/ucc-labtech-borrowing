<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ config('app.name', 'UCC LabTech Borrowing Management System') }}
    </title>

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body
    class="h-dvh overflow-hidden bg-gray-50 font-sans text-gray-900 antialiased"
    x-data
>
    <main class="h-dvh overflow-hidden">
        <div class="grid h-full lg:grid-cols-[42%_58%]">
            {{-- Desktop branding --}}
            <section
                class="relative hidden h-full overflow-hidden bg-green-800
                       px-10 py-8 text-white lg:flex lg:flex-col
                       lg:justify-between xl:px-14 xl:py-10"
            >
                <div
                    class="absolute -right-32 -top-32 h-80 w-80
                           rounded-full bg-green-700"
                ></div>

                <div
                    class="absolute -bottom-40 -left-24 h-96 w-96
                           rounded-full bg-green-700/70"
                ></div>

                <div class="relative z-10 flex items-center gap-4">
                    <div
                        class="flex h-16 w-16 shrink-0 items-center justify-center
                               rounded-full bg-white p-2 shadow-lg xl:h-20 xl:w-20"
                    >
                        <img
                            src="{{ asset('images/UCC_Logo.png') }}"
                            alt="University of Caloocan City Logo"
                            class="h-full w-full object-contain"
                        >
                    </div>

                    <div>
                        <h1 class="text-lg font-bold xl:text-xl">
                            University of Caloocan City
                        </h1>

                        <p class="mt-1 text-xs text-green-200 xl:text-sm">
                            LabTech Borrowing Management System
                        </p>
                    </div>
                </div>

                <div class="relative z-10 max-w-lg">
                    <span
                        class="inline-flex rounded-full bg-white/15
                               px-4 py-1.5 text-xs font-semibold xl:text-sm"
                    >
                        UCC Laboratory Services
                    </span>

                    <h2
                        class="mt-5 text-3xl font-bold leading-tight
                               xl:text-4xl"
                    >
                        A smarter way to manage laboratory equipment.
                    </h2>

                    <p
                        class="mt-4 text-sm leading-6 text-green-100
                               xl:text-base xl:leading-7"
                    >
                        Request, monitor, and manage laboratory equipment
                        through one secure university platform.
                    </p>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-sm font-semibold">
                                Equipment Tracking
                            </p>

                            <p class="mt-1 text-xs leading-5 text-green-100">
                                Monitor availability and asset condition.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-sm font-semibold">
                                Secure Borrowing
                            </p>

                            <p class="mt-1 text-xs leading-5 text-green-100">
                                Manage requests and returns securely.
                            </p>
                        </div>
                    </div>
                </div>

                <p class="relative z-10 text-xs text-green-200">
                    © {{ date('Y') }} University of Caloocan City
                </p>
            </section>

            {{-- Authentication area --}}
            <section
                class="flex h-full min-h-0 items-center justify-center
                       overflow-hidden px-4 py-4 sm:px-6 lg:px-8"
            >
                <div class="flex h-full w-full max-w-3xl flex-col justify-center">
                    {{-- Mobile header --}}
                    <div
                        class="mb-3 flex shrink-0 items-center justify-center
                               gap-3 lg:hidden"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center
                                   justify-center rounded-full bg-white p-1
                                   shadow"
                        >
                            <img
                                src="{{ asset('images/UCC_Logo.png') }}"
                                alt="University of Caloocan City Logo"
                                class="h-full w-full object-contain"
                            >
                        </div>

                        <div>
                            <p class="text-sm font-bold text-gray-900">
                                University of Caloocan City
                            </p>

                            <p class="text-[10px] text-gray-500 sm:text-xs">
                                LabTech Borrowing Management System
                            </p>
                        </div>
                    </div>

                    <div
                        class="min-h-0 rounded-2xl border border-gray-200
                               bg-white p-4 shadow-sm sm:p-6 lg:border-0
                               lg:bg-transparent lg:p-0 lg:shadow-none"
                    >
                        {{ $slot }}
                    </div>
                </div>
            </section>
        </div>
    </main>

    <x-user-guide-modal />
</body>
</html>