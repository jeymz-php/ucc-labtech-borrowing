<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                My Dashboard
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Manage your laboratory requests and account.
            </p>
        </div>
    </x-slot>

    @php
        $user = auth()->user();

        $roleName = $user->getRoleNames()->first()
            ? str_replace('_', ' ', $user->getRoleNames()->first())
            : 'User';
    @endphp

    <div class="space-y-6">
        {{-- Welcome section --}}
        <section
            class="relative overflow-hidden rounded-2xl border
                   border-green-700 bg-gradient-to-r
                   from-green-900 via-green-800 to-green-600
                   px-5 py-6 text-white shadow-sm sm:px-7 sm:py-7"
        >
            {{-- Decorative elements --}}
            <div
                class="pointer-events-none absolute -right-14 -top-20
                       h-56 w-56 rounded-full bg-white/10"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-24 right-20
                       h-52 w-52 rounded-full bg-white/5"
            ></div>

            <div
                class="relative z-10 flex flex-col gap-6
                       sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <p class="text-sm font-medium text-green-100">
                        Welcome back,
                    </p>

                    <h2
                        class="mt-1 break-words text-2xl font-bold
                               leading-tight sm:text-3xl"
                    >
                        {{ $user->first_name }}!
                    </h2>

                    <p
                        class="mt-3 max-w-2xl text-sm leading-6
                               text-green-100"
                    >
                        Browse available laboratory equipment, submit
                        borrowing requests, and monitor your account
                        transactions from one place.
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            class="inline-flex rounded-full bg-white/15
                                   px-3 py-1 text-xs font-semibold
                                   capitalize text-white"
                        >
                            {{ $roleName }}
                        </span>

                        <span
                            class="inline-flex rounded-full bg-white/15
                                   px-3 py-1 text-xs font-semibold
                                   capitalize text-white"
                        >
                            {{ $user->campus }}
                        </span>
                    </div>
                </div>

                {{-- Profile picture --}}
                <a
                    href="{{ route('profile.edit') }}"
                    class="group relative mx-auto h-24 w-24 shrink-0
                           sm:mx-0 sm:h-28 sm:w-28"
                    aria-label="Open profile"
                >
                    <div
                        class="flex h-full w-full items-center justify-center
                               overflow-hidden rounded-full border-4
                               border-white/80 bg-white text-2xl font-bold
                               text-green-700 shadow-lg transition
                               duration-300 group-hover:scale-105
                               group-hover:border-white sm:text-3xl"
                    >
                        @if ($user->profile_picture_url)
                            <img
                                src="{{ $user->profile_picture_url }}"
                                alt="{{ $user->full_name }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            {{ $user->initials }}
                        @endif
                    </div>

                    <span
                        class="absolute bottom-0 right-0 flex h-8 w-8
                               items-center justify-center rounded-full
                               border-2 border-white bg-green-700
                               text-white shadow-md"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15.232 5.232l3.536 3.536
                                   M9 11l6.232-6.232a2.5 2.5 0
                                   013.536 3.536L12.536 14.536
                                   a2 2 0 01-.879.51L8 16l.954-3.657
                                   A2 2 0 019.464 11.464L9 11z"
                            />
                        </svg>
                    </span>
                </a>
            </div>
        </section>

        {{-- Statistics --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Role --}}
            <article
                class="group rounded-2xl border border-gray-200
                       bg-white p-5 shadow-sm transition duration-300
                       hover:-translate-y-1 hover:border-green-200
                       hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-gray-400"
                        >
                            Account Type
                        </p>

                        <p
                            class="mt-3 text-xl font-bold capitalize
                                   text-gray-900"
                        >
                            {{ $roleName }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center
                               justify-center rounded-xl bg-green-100
                               text-green-700 transition
                               group-hover:bg-green-700
                               group-hover:text-white"
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
                                d="M5.121 17.804A9.004 9.004 0
                                   0112 15c2.624 0 4.985 1.123
                                   6.879 2.804M15 11a3 3 0
                                   11-6 0 3 3 0 016 0z
                                   M12 21a9 9 0 100-18
                                   9 9 0 000 18z"
                            />
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Available units --}}
            <article
                class="group rounded-2xl border border-gray-200
                       bg-white p-5 shadow-sm transition duration-300
                       hover:-translate-y-1 hover:border-green-200
                       hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-gray-400"
                        >
                            Available Units
                        </p>

                        <p class="mt-3 text-3xl font-bold text-green-700">
                            {{ number_format($availableEquipment) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center
                               justify-center rounded-xl bg-green-100
                               text-green-700 transition
                               group-hover:bg-green-700
                               group-hover:text-white"
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
                                d="M20 7l-8-4-8 4m16 0-8 4
                                   m8-4v10l-8 4m0-10L4 7
                                   m8 4v10"
                            />
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Equipment types --}}
            <article
                class="group rounded-2xl border border-gray-200
                       bg-white p-5 shadow-sm transition duration-300
                       hover:-translate-y-1 hover:border-green-200
                       hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-gray-400"
                        >
                            Equipment Types
                        </p>

                        <p class="mt-3 text-3xl font-bold text-gray-900">
                            {{ number_format($totalEquipment) }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center
                               justify-center rounded-xl bg-green-100
                               text-green-700 transition
                               group-hover:bg-green-700
                               group-hover:text-white"
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
                    </div>
                </div>
            </article>

            {{-- Account status --}}
            <article
                class="group rounded-2xl border border-gray-200
                       bg-white p-5 shadow-sm transition duration-300
                       hover:-translate-y-1 hover:border-green-200
                       hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-wide text-gray-400"
                        >
                            Account Status
                        </p>

                        <span
                            class="mt-4 inline-flex rounded-full
                                   bg-green-100 px-3 py-1
                                   text-xs font-bold capitalize
                                   text-green-700"
                        >
                            {{ $user->account_status }}
                        </span>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center
                               justify-center rounded-xl bg-green-100
                               text-green-700 transition
                               group-hover:bg-green-700
                               group-hover:text-white"
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
                                d="M9 12l2 2 4-4m6 2
                                   a9 9 0 11-18 0
                                   9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>
            </article>
        </section>

        {{-- Quick actions --}}
        <section>
            <div class="mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    Quick Actions
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Access your most commonly used system functions.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @can('view items')
                    <a
                        href="{{ route('items.index') }}"
                        class="group relative overflow-hidden rounded-2xl
                               border border-gray-200 bg-white p-5
                               shadow-sm transition duration-300
                               hover:-translate-y-1
                               hover:border-green-300 hover:shadow-md"
                    >
                        <div
                            class="absolute inset-y-0 left-0 w-1
                                   bg-green-600 opacity-0 transition
                                   group-hover:opacity-100"
                        ></div>

                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div
                                class="flex h-12 w-12 shrink-0 items-center
                                       justify-center rounded-xl
                                       bg-green-100 text-green-700
                                       transition group-hover:bg-green-700
                                       group-hover:text-white"
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
                                        d="M20 7l-8-4-8 4m16 0-8 4
                                           m8-4v10l-8 4m0-10L4 7
                                           m8 4v10"
                                    />
                                </svg>
                            </div>

                            <svg
                                class="h-5 w-5 text-gray-300 transition
                                       group-hover:translate-x-1
                                       group-hover:text-green-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>

                        <h3 class="mt-4 font-bold text-gray-900">
                            Browse Equipment
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            View available laboratory equipment and their
                            current availability.
                        </p>
                    </a>
                @endcan

                @can('create borrowing requests')
                    <a
                        href="#"
                        class="group relative overflow-hidden rounded-2xl
                               border border-gray-200 bg-white p-5
                               shadow-sm transition duration-300
                               hover:-translate-y-1
                               hover:border-green-300 hover:shadow-md"
                    >
                        <div
                            class="absolute inset-y-0 left-0 w-1
                                   bg-green-600 opacity-0 transition
                                   group-hover:opacity-100"
                        ></div>

                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div
                                class="flex h-12 w-12 shrink-0 items-center
                                       justify-center rounded-xl
                                       bg-green-100 text-green-700
                                       transition group-hover:bg-green-700
                                       group-hover:text-white"
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
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                            </div>

                            <svg
                                class="h-5 w-5 text-gray-300 transition
                                       group-hover:translate-x-1
                                       group-hover:text-green-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>

                        <h3 class="mt-4 font-bold text-gray-900">
                            New Borrowing Request
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Submit a new request to borrow available
                            laboratory equipment.
                        </p>
                    </a>
                @endcan

                <a
                    href="{{ route('profile.edit') }}"
                    class="group relative overflow-hidden rounded-2xl
                           border border-gray-200 bg-white p-5
                           shadow-sm transition duration-300
                           hover:-translate-y-1
                           hover:border-green-300 hover:shadow-md"
                >
                    <div
                        class="absolute inset-y-0 left-0 w-1
                               bg-green-600 opacity-0 transition
                               group-hover:opacity-100"
                    ></div>

                    <div class="flex items-start justify-between gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center
                                   justify-center rounded-xl bg-green-100
                                   text-green-700 transition
                                   group-hover:bg-green-700
                                   group-hover:text-white"
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
                                    d="M12 4.354a4 4 0 110 5.292
                                       M15 21H3v-1a6 6 0 0112 0v1z"
                                />
                            </svg>
                        </div>

                        <svg
                            class="h-5 w-5 text-gray-300 transition
                                   group-hover:translate-x-1
                                   group-hover:text-green-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-4 font-bold text-gray-900">
                        Manage Profile
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Update your personal information, password, and
                        profile picture.
                    </p>
                </a>
            </div>
        </section>

        {{-- Recently added equipment --}}
        <section
            class="rounded-2xl border border-gray-200
                   bg-white p-5 shadow-sm sm:p-6"
        >
            <div
                class="flex flex-col gap-3 sm:flex-row
                       sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Recently Added Equipment
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Recently registered equipment in the laboratory
                        inventory.
                    </p>
                </div>

                @can('view items')
                    <a
                        href="{{ route('items.index') }}"
                        class="inline-flex items-center gap-2 text-sm
                               font-semibold text-green-700 transition
                               hover:text-green-800"
                    >
                        View all

                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </a>
                @endcan
            </div>

            @if ($recentItems->isEmpty())
                <div
                    class="mt-6 rounded-2xl border-2 border-dashed
                           border-gray-200 px-5 py-12 text-center"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center
                               justify-center rounded-full bg-gray-100
                               text-gray-400"
                    >
                        <svg
                            class="h-7 w-7"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0-8 4
                                   m8-4v10l-8 4m0-10L4 7
                                   m8 4v10"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-4 font-bold text-gray-700">
                        No equipment available
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Recently added equipment will appear here.
                    </p>
                </div>
            @else
                <div
                    class="mt-6 grid gap-4
                           sm:grid-cols-2 xl:grid-cols-3"
                >
                    @foreach ($recentItems as $item)
                        <article
                            class="group rounded-2xl border
                                   border-gray-200 bg-white p-4
                                   transition duration-300
                                   hover:-translate-y-1
                                   hover:border-green-300
                                   hover:shadow-md"
                        >
                            <div
                                class="flex items-start justify-between
                                       gap-3"
                            >
                                <div
                                    class="flex h-12 w-12 shrink-0
                                           items-center justify-center
                                           rounded-xl bg-green-100
                                           text-sm font-bold text-green-700"
                                >
                                    {{ strtoupper(
                                        substr($item->name ?? 'IT', 0, 2)
                                    ) }}
                                </div>

                                <span
                                    class="inline-flex rounded-full
                                           bg-green-100 px-2.5 py-1
                                           text-[10px] font-bold
                                           text-green-700"
                                >
                                    Available
                                </span>
                            </div>

                            <h3
                                class="mt-4 line-clamp-1 font-bold
                                       text-gray-900"
                            >
                                {{ $item->name }}
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $item->category?->name
                                    ?? 'Uncategorized' }}
                            </p>

                            <div
                                class="mt-4 flex items-center
                                       justify-between border-t
                                       border-gray-100 pt-3"
                            >
                                <span class="text-xs text-gray-400">
                                    Recently added
                                </span>

                                <span
                                    class="text-xs font-semibold
                                           text-green-700"
                                >
                                    View details
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-app-layout>