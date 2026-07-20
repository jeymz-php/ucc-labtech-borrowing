<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ $category->name }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $category->category_code }}
                </p>
            </div>

            <div class="flex gap-2">
                @can('edit categories')
                    <a
                        href="{{ route(
                            'categories.edit',
                            $category
                        ) }}"
                        class="rounded-lg bg-green-700 px-4 py-2 text-sm
                               font-medium text-white hover:bg-green-800"
                    >
                        Edit
                    </a>
                @endcan

                <a
                    href="{{ route('categories.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2
                           text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-xl bg-white p-6 shadow-sm md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Category Details
                    </h3>

                    <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm text-gray-500">
                                Category Code
                            </dt>

                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $category->category_code }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Asset Prefix
                            </dt>

                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $category->asset_prefix }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Status
                            </dt>

                            <dd class="mt-1 font-medium text-gray-900">
                                {{ ucfirst($category->status) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Number of Items
                            </dt>

                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $category->items_count }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm text-gray-500">
                                Description
                            </dt>

                            <dd class="mt-1 text-gray-900">
                                {{ $category->description
                                    ?: 'No description provided.' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Record Information
                    </h3>

                    <dl class="mt-6 space-y-4">
                        <div>
                            <dt class="text-sm text-gray-500">
                                Created By
                            </dt>

                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $category->creator?->full_name
                                    ?? 'System' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Last Updated By
                            </dt>

                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $category->updater?->full_name
                                    ?? 'System' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">
                                Created
                            </dt>

                            <dd class="mt-1 text-gray-900">
                                {{ $category->created_at->format(
                                    'F d, Y h:i A'
                                ) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Recent Items
                    </h3>
                </div>

                @forelse ($category->items as $item)
                    <div class="flex items-center justify-between border-b
                                px-6 py-4 last:border-b-0">
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ $item->name }}
                            </div>

                            <div class="text-sm text-gray-500">
                                {{ $item->item_code }}
                            </div>
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ $item->quantity_available }}
                            /
                            {{ $item->quantity_total }}
                            available
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-gray-500">
                        No inventory items currently use this category.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>