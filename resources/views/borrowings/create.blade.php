<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                New Borrowing Request
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Choose available physical units and provide the borrowing schedule.
            </p>
        </div>
    </x-slot>

    <form
        method="POST"
        action="{{ route('borrowings.store') }}"
        class="space-y-6"
    >
        @csrf

        @if ($errors->any())
            <div
                class="rounded-xl border border-red-200
                       bg-red-50 p-4 text-sm text-red-700"
            >
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Request information --}}
        <section
            class="rounded-2xl border border-gray-200
                   bg-white p-6 shadow-sm"
        >
            <h2 class="font-bold text-gray-900">
                Request Information
            </h2>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label
                        for="purpose"
                        class="text-sm font-semibold text-gray-700"
                    >
                        Purpose
                    </label>

                    <textarea
                        id="purpose"
                        name="purpose"
                        rows="3"
                        required
                        class="mt-2 w-full rounded-xl border-gray-300
                               focus:border-green-600 focus:ring-green-600"
                    >{{ old('purpose') }}</textarea>
                </div>

                <div>
                    <label
                        for="borrow_at"
                        class="text-sm font-semibold text-gray-700"
                    >
                        Borrow date and time
                    </label>

                    <input
                        id="borrow_at"
                        type="datetime-local"
                        name="borrow_at"
                        value="{{ old('borrow_at') }}"
                        required
                        class="mt-2 w-full rounded-xl border-gray-300
                               focus:border-green-600 focus:ring-green-600"
                    >
                </div>

                <div>
                    <label
                        for="expected_return_at"
                        class="text-sm font-semibold text-gray-700"
                    >
                        Expected return
                    </label>

                    <input
                        id="expected_return_at"
                        type="datetime-local"
                        name="expected_return_at"
                        value="{{ old('expected_return_at') }}"
                        required
                        class="mt-2 w-full rounded-xl border-gray-300
                               focus:border-green-600 focus:ring-green-600"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label
                        for="request_notes"
                        class="text-sm font-semibold text-gray-700"
                    >
                        Additional notes
                    </label>

                    <textarea
                        id="request_notes"
                        name="request_notes"
                        rows="2"
                        class="mt-2 w-full rounded-xl border-gray-300
                               focus:border-green-600 focus:ring-green-600"
                    >{{ old('request_notes') }}</textarea>
                </div>
            </div>
        </section>

        {{-- Equipment selection --}}
        <section
            class="overflow-hidden rounded-2xl border
                   border-gray-200 bg-white shadow-sm"
        >
            <div class="border-b border-gray-100 px-6 py-5">
                <div
                    class="flex flex-col gap-4
                           lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <h2 class="font-bold text-gray-900">
                            Select Equipment Units
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Maximum of 10 units per request.
                        </p>
                    </div>

                    {{-- Search bar --}}
                    <div class="w-full lg:max-w-md">
                        <label
                            for="equipmentSearch"
                            class="sr-only"
                        >
                            Search equipment
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute
                                       inset-y-0 left-0 flex items-center pl-4"
                            >
                                <svg
                                    class="h-5 w-5 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m21 21-4.35-4.35
                                           M19 11a8 8 0 1 1-16 0
                                           8 8 0 0 1 16 0Z"
                                    />
                                </svg>
                            </span>

                            <input
                                id="equipmentSearch"
                                type="search"
                                autocomplete="off"
                                placeholder="Search name, asset number, condition..."
                                class="w-full rounded-xl border-gray-300
                                       py-3 pl-11 pr-11 text-sm
                                       focus:border-green-600
                                       focus:ring-green-600"
                            >

                            <button
                                id="clearEquipmentSearch"
                                type="button"
                                class="absolute inset-y-0 right-0 hidden
                                       items-center px-4 text-gray-400
                                       transition hover:text-gray-700"
                                aria-label="Clear equipment search"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18 18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        <p
                            id="equipmentSearchStatus"
                            class="mt-2 text-xs text-gray-500"
                            aria-live="polite"
                        >
                            Showing all {{ $units->count() }} available units.
                        </p>
                    </div>
                </div>
            </div>

            <div
                id="equipmentUnitsGrid"
                class="grid gap-3 p-6 sm:grid-cols-2 xl:grid-cols-3"
            >
                @forelse ($units as $unit)
                    @php
                        $location = $unit->location ?: $unit->item->location;

                        $searchableText = strtolower(
                            trim(
                                implode(' ', [
                                    $unit->item->display_name,
                                    $unit->asset_number,
                                    $unit->condition,
                                    $location,
                                ])
                            )
                        );
                    @endphp

                    <label
                        class="equipment-unit-card flex cursor-pointer
                               gap-3 rounded-xl border border-gray-200
                               p-4 transition
                               hover:border-green-400 hover:bg-green-50
                               has-[:checked]:border-green-600
                               has-[:checked]:bg-green-50
                               has-[:checked]:ring-1
                               has-[:checked]:ring-green-600"
                        data-search="{{ $searchableText }}"
                    >
                        <input
                            type="checkbox"
                            name="item_unit_ids[]"
                            value="{{ $unit->id }}"
                            @checked(
                                in_array(
                                    $unit->id,
                                    old('item_unit_ids', [])
                                )
                            )
                            class="equipment-checkbox mt-1 rounded
                                   border-gray-300 text-green-700
                                   focus:ring-green-600"
                        >

                        <span class="min-w-0 flex-1">
                            <span
                                class="block font-semibold text-gray-900"
                            >
                                {{ $unit->item->display_name }}
                            </span>

                            <span
                                class="mt-1 block text-xs text-gray-500"
                            >
                                {{ $unit->asset_number }}
                                ·
                                {{ ucfirst(str_replace('_', ' ', $unit->condition)) }}
                            </span>

                            <span
                                class="mt-1 block text-xs text-gray-400"
                            >
                                {{ $location ?: 'Location not specified' }}
                            </span>
                        </span>

                        <span
                            class="hidden h-6 w-6 shrink-0 items-center
                                   justify-center rounded-full
                                   bg-green-700 text-white
                                   [.equipment-unit-card:has(input:checked)_&]:flex"
                            aria-hidden="true"
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
                                    stroke-width="2.5"
                                    d="m5 13 4 4L19 7"
                                />
                            </svg>
                        </span>
                    </label>
                @empty
                    <div
                        class="py-10 text-center text-gray-500
                               sm:col-span-2 xl:col-span-3"
                    >
                        No borrowable units are currently available.
                    </div>
                @endforelse

                {{-- No search results --}}
                <div
                    id="noEquipmentSearchResults"
                    class="hidden py-12 text-center
                           sm:col-span-2 xl:col-span-3"
                >
                    <div
                        class="mx-auto flex h-12 w-12 items-center
                               justify-center rounded-full bg-gray-100"
                    >
                        <svg
                            class="h-6 w-6 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="m21 21-4.35-4.35
                                   M19 11a8 8 0 1 1-16 0
                                   8 8 0 0 1 16 0Z"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-3 font-semibold text-gray-900">
                        No equipment found
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Try searching with a different name,
                        asset number, condition, or location.
                    </p>
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a
                href="{{ route('borrowings.index') }}"
                class="rounded-xl border border-gray-300 px-5
                       py-2.5 text-sm font-semibold text-gray-700
                       transition hover:bg-gray-50"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="rounded-xl bg-green-700 px-6 py-2.5
                       text-sm font-semibold text-white shadow-sm
                       transition hover:bg-green-800"
            >
                Submit Request
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById(
                'equipmentSearch'
            );

            const clearButton = document.getElementById(
                'clearEquipmentSearch'
            );

            const statusText = document.getElementById(
                'equipmentSearchStatus'
            );

            const noResults = document.getElementById(
                'noEquipmentSearchResults'
            );

            const equipmentCards = Array.from(
                document.querySelectorAll('.equipment-unit-card')
            );

            if (!searchInput) {
                return;
            }

            function filterEquipment() {
                const query = searchInput.value
                    .trim()
                    .toLowerCase();

                let visibleCount = 0;

                equipmentCards.forEach((card) => {
                    const searchableText =
                        card.dataset.search?.toLowerCase() ?? '';

                    const matches =
                        query === '' ||
                        searchableText.includes(query);

                    card.classList.toggle('hidden', !matches);

                    if (matches) {
                        visibleCount++;
                    }
                });

                clearButton?.classList.toggle(
                    'hidden',
                    query === ''
                );

                clearButton?.classList.toggle(
                    'flex',
                    query !== ''
                );

                noResults?.classList.toggle(
                    'hidden',
                    visibleCount !== 0
                );

                if (!statusText) {
                    return;
                }

                if (query === '') {
                    statusText.textContent =
                        `Showing all ${equipmentCards.length} available units.`;

                    return;
                }

                statusText.textContent =
                    `${visibleCount} equipment unit${visibleCount === 1 ? '' : 's'} found.`;
            }

            searchInput.addEventListener(
                'input',
                filterEquipment
            );

            clearButton?.addEventListener('click', () => {
                searchInput.value = '';
                filterEquipment();
                searchInput.focus();
            });

            filterEquipment();
        });
    </script>
</x-app-layout>