<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row
                   sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Dashboard
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Welcome back, {{ auth()->user()->first_name }}.
                    Here is your current inventory overview.
                </p>
            </div>

            <div class="flex gap-2">
                @can('create categories')
                    <a
                        href="{{ route('categories.create') }}"
                        class="rounded-xl border border-gray-300 bg-white
                               px-4 py-2.5 text-sm font-semibold
                               text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                        Add Category
                    </a>
                @endcan

                @can('create items')
                    <button
                        type="button"
                        disabled
                        title="Items module will be developed next."
                        class="cursor-not-allowed rounded-xl bg-green-700
                               px-4 py-2.5 text-sm font-semibold text-white
                               opacity-60"
                    >
                        Add Inventory Item
                    </button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        {{-- Welcome banner --}}
        <section
            class="relative mb-8 overflow-hidden rounded-2xl bg-green-800
                   px-6 py-7 text-white shadow-lg sm:px-8"
        >
            <div
                class="absolute -right-20 -top-20 h-64 w-64
                       rounded-full bg-green-700"
            ></div>

            <div
                class="absolute -bottom-24 right-24 h-48 w-48
                       rounded-full bg-green-600/50"
            ></div>

            <div class="relative z-10 max-w-2xl">
                <div
                    class="mb-3 inline-flex rounded-full bg-white/15
                           px-3 py-1 text-xs font-semibold"
                >
                    UCC LabTech Office
                </div>

                <h2 class="text-2xl font-bold sm:text-3xl">
                    Inventory monitoring made simpler.
                </h2>

                <p class="mt-3 max-w-xl text-sm leading-6 text-green-100">
                    Track equipment, monitor availability, manage categories,
                    and prepare the system for online and walk-in borrowing.
                </p>
            </div>
        </section>

        {{-- Primary statistics --}}
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-2xl border border-gray-100 bg-white p-6
                       shadow-sm transition hover:-translate-y-0.5
                       hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Active Users
                        </p>

                        <p class="mt-3 text-3xl font-bold text-gray-900">
                            {{ number_format(
                                $statistics['total_users']
                            ) }}
                        </p>
                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center
                               rounded-xl bg-blue-50 text-blue-700"
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
                                d="M17 20h5v-2a3 3 0
                                   00-5.356-1.857M17 20H7m10
                                   0v-2c0-.656-.126-1.283
                                   -.356-1.857M7 20H2v-2a3 3 0
                                   015.356-1.857M15 7a3 3 0
                                   11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                    </div>
                </div>

                <p class="mt-4 text-xs text-gray-500">
                    Approved and active accounts
                </p>
            </div>

            <div
                class="rounded-2xl border border-gray-100 bg-white p-6
                       shadow-sm transition hover:-translate-y-0.5
                       hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Categories
                        </p>

                        <p class="mt-3 text-3xl font-bold text-gray-900">
                            {{ number_format(
                                $statistics['total_categories']
                            ) }}
                        </p>
                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center
                               rounded-xl bg-purple-50 text-purple-700"
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
                                d="M19 11H5m14-7H5a2 2
                                   0 00-2 2v12a2 2 0
                                   002 2h14a2 2 0 002-2V6a2
                                   2 0 00-2-2z"
                            />
                        </svg>
                    </div>
                </div>

                <p class="mt-4 text-xs text-gray-500">
                    Active inventory classifications
                </p>
            </div>

            <div
                class="rounded-2xl border border-gray-100 bg-white p-6
                       shadow-sm transition hover:-translate-y-0.5
                       hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Inventory Items
                        </p>

                        <p class="mt-3 text-3xl font-bold text-gray-900">
                            {{ number_format(
                                $statistics['total_items']
                            ) }}
                        </p>
                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center
                               rounded-xl bg-amber-50 text-amber-700"
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
                                d="M20 7l-8-4-8 4m16
                                   0l-8 4m8-4v10l-8
                                   4m0-10L4 7m8
                                   4v10M4 7v10l8 4"
                            />
                        </svg>
                    </div>
                </div>

                <p class="mt-4 text-xs text-gray-500">
                    Unique equipment and supply records
                </p>
            </div>

            <div
                class="rounded-2xl border border-gray-100 bg-white p-6
                       shadow-sm transition hover:-translate-y-0.5
                       hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Physical Units
                        </p>

                        <p class="mt-3 text-3xl font-bold text-gray-900">
                            {{ number_format(
                                $statistics['total_units']
                            ) }}
                        </p>
                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center
                               rounded-xl bg-green-50 text-green-700"
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
                                d="M9 17v-6a2 2 0
                                   012-2h2a2 2 0
                                   012 2v6m-6 0h6m-9
                                   4h12a2 2 0
                                   002-2V7a2 2 0
                                   00-2-2H6a2 2 0
                                   00-2 2v12a2 2 0
                                   002 2z"
                            />
                        </svg>
                    </div>
                </div>

                <p class="mt-4 text-xs text-gray-500">
                    Individually tracked asset units
                </p>
            </div>
        </section>

        {{-- Availability statistics --}}
        <section class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-2xl border border-green-100
                       bg-green-50 p-5"
            >
                <p class="text-sm font-semibold text-green-700">
                    Available
                </p>

                <p class="mt-2 text-3xl font-bold text-green-900">
                    {{ number_format(
                        $statistics['available_units']
                    ) }}
                </p>
            </div>

            <div
                class="rounded-2xl border border-blue-100
                       bg-blue-50 p-5"
            >
                <p class="text-sm font-semibold text-blue-700">
                    Borrowed
                </p>

                <p class="mt-2 text-3xl font-bold text-blue-900">
                    {{ number_format(
                        $statistics['borrowed_units']
                    ) }}
                </p>
            </div>

            <div
                class="rounded-2xl border border-amber-100
                       bg-amber-50 p-5"
            >
                <p class="text-sm font-semibold text-amber-700">
                    Maintenance
                </p>

                <p class="mt-2 text-3xl font-bold text-amber-900">
                    {{ number_format(
                        $statistics['maintenance_units']
                    ) }}
                </p>
            </div>

            <div
                class="rounded-2xl border border-red-100
                       bg-red-50 p-5"
            >
                <p class="text-sm font-semibold text-red-700">
                    Lost
                </p>

                <p class="mt-2 text-3xl font-bold text-red-900">
                    {{ number_format(
                        $statistics['lost_units']
                    ) }}
                </p>
            </div>
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-3">
            {{-- Recent items --}}
            <div
                class="overflow-hidden rounded-2xl border border-gray-100
                       bg-white shadow-sm xl:col-span-2"
            >
                <div
                    class="flex items-center justify-between border-b
                           border-gray-100 px-6 py-5"
                >
                    <div>
                        <h3 class="font-bold text-gray-900">
                            Recent Inventory Items
                        </h3>

                        <p class="mt-1 text-xs text-gray-500">
                            Latest item records added to the system
                        </p>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($recentItems as $item)
                        <div
                            class="flex flex-col gap-3 px-6 py-4
                                   sm:flex-row sm:items-center
                                   sm:justify-between"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-11 w-11 items-center
                                           justify-center rounded-xl
                                           bg-green-50 font-bold text-green-700"
                                >
                                    {{ strtoupper(
                                        substr($item->name, 0, 1)
                                    ) }}
                                </div>

                                <div>
                                    <div class="font-semibold text-gray-900">
                                        {{ $item->name }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $item->item_code }}
                                        ·
                                        {{ $item->category?->name
                                            ?? 'Uncategorized' }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-left sm:text-right">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $item->quantity_available }}
                                    /
                                    {{ $item->quantity_total }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    units available
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <p class="font-medium text-gray-700">
                                No inventory items yet.
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                Inventory records will appear here.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Low stock --}}
            <div
                class="overflow-hidden rounded-2xl border border-gray-100
                       bg-white shadow-sm"
            >
                <div class="border-b border-gray-100 px-6 py-5">
                    <h3 class="font-bold text-gray-900">
                        Low Stock Alerts
                    </h3>

                    <p class="mt-1 text-xs text-gray-500">
                        Items at or below minimum availability
                    </p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($lowStockItems as $item)
                        <div class="px-6 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        {{ $item->name }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $item->category?->name }}
                                    </div>
                                </div>

                                <span
                                    class="rounded-full bg-red-100 px-2.5
                                           py-1 text-xs font-bold text-red-700"
                                >
                                    {{ $item->quantity_available }}
                                    left
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <div
                                class="mx-auto flex h-12 w-12 items-center
                                       justify-center rounded-full bg-green-50
                                       text-green-700"
                            >
                                ✓
                            </div>

                            <p class="mt-3 font-medium text-gray-700">
                                Inventory levels are healthy.
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                No low-stock items detected.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Recent units --}}
        <section
            class="mt-6 overflow-hidden rounded-2xl
                   border border-gray-100 bg-white shadow-sm"
        >
            <div
                class="flex items-center justify-between border-b
                       border-gray-100 px-6 py-5"
            >
                <div>
                    <h3 class="font-bold text-gray-900">
                        Recently Registered Assets
                    </h3>

                    <p class="mt-1 text-xs text-gray-500">
                        Latest physical units added to inventory
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs
                                       font-semibold uppercase tracking-wide
                                       text-gray-500"
                            >
                                Asset
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs
                                       font-semibold uppercase tracking-wide
                                       text-gray-500"
                            >
                                Item
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs
                                       font-semibold uppercase tracking-wide
                                       text-gray-500"
                            >
                                Condition
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs
                                       font-semibold uppercase tracking-wide
                                       text-gray-500"
                            >
                                Availability
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs
                                       font-semibold uppercase tracking-wide
                                       text-gray-500"
                            >
                                Date Added
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentUnits as $unit)
                            <tr class="hover:bg-gray-50">
                                <td
                                    class="whitespace-nowrap px-6 py-4
                                           font-semibold text-green-700"
                                >
                                    {{ $unit->asset_number }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $unit->item?->name }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $unit->item?->category?->name }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full bg-gray-100
                                               px-2.5 py-1 text-xs font-semibold
                                               text-gray-700"
                                    >
                                        {{ ucwords(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $unit->condition
                                            )
                                        ) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    @php
                                        $availabilityClasses = match (
                                            $unit->availability_status
                                        ) {
                                            'available' =>
                                                'bg-green-100 text-green-700',

                                            'borrowed' =>
                                                'bg-blue-100 text-blue-700',

                                            'maintenance' =>
                                                'bg-amber-100 text-amber-700',

                                            'lost' =>
                                                'bg-red-100 text-red-700',

                                            default =>
                                                'bg-gray-100 text-gray-700',
                                        };
                                    @endphp

                                    <span
                                        class="rounded-full px-2.5 py-1
                                               text-xs font-semibold
                                               {{ $availabilityClasses }}"
                                    >
                                        {{ ucwords(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $unit->availability_status
                                            )
                                        ) }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-6 py-4
                                           text-sm text-gray-500"
                                >
                                    {{ $unit->created_at->format(
                                        'M d, Y'
                                    ) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="px-6 py-12 text-center
                                           text-gray-500"
                                >
                                    No physical assets registered yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="py-8 text-center text-xs text-gray-400">
            UCC LabTech Borrowing Management System
            ·
            Developed by LabTech Developer
        </footer>
    </div>
</x-app-layout>