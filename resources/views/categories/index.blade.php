<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center
                    sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    Categories
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Manage inventory classifications and asset prefixes.
                </p>
            </div>

            <div class="flex gap-2">
                @can('restore categories')
                    <a
                        href="{{ route('categories.archived') }}"
                        class="rounded-lg border border-gray-300 bg-white
                               px-4 py-2 text-sm font-medium text-gray-700
                               hover:bg-gray-50"
                    >
                        Archived
                    </a>
                @endcan

                @can('create categories')
                    <a
                        href="{{ route('categories.create') }}"
                        class="rounded-lg bg-green-700 px-4 py-2 text-sm
                               font-medium text-white hover:bg-green-800"
                    >
                        Add Category
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-green-200
                            bg-green-50 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-200
                            bg-red-50 px-4 py-3 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 rounded-xl bg-white p-5 shadow-sm">
                <form
                    method="GET"
                    action="{{ route('categories.index') }}"
                    class="grid gap-4 md:grid-cols-4"
                >
                    <div class="md:col-span-2">
                        <label
                            for="search"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Search
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Name, code, prefix, or description"
                            class="mt-1 block w-full rounded-lg border-gray-300
                                   focus:border-green-600 focus:ring-green-600"
                        >
                    </div>

                    <div>
                        <label
                            for="status"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="mt-1 block w-full rounded-lg border-gray-300
                                   focus:border-green-600 focus:ring-green-600"
                        >
                            <option value="">All statuses</option>

                            <option
                                value="active"
                                @selected($status === 'active')
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected($status === 'inactive')
                            >
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="rounded-lg bg-green-700 px-4 py-2.5
                                   text-sm font-medium text-white
                                   hover:bg-green-800"
                        >
                            Filter
                        </button>

                        <a
                            href="{{ route('categories.index') }}"
                            class="rounded-lg border border-gray-300 bg-white
                                   px-4 py-2.5 text-sm font-medium
                                   text-gray-700 hover:bg-gray-50"
                        >
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs
                                           font-semibold uppercase tracking-wide
                                           text-gray-500">
                                    Category
                                </th>

                                <th class="px-6 py-3 text-left text-xs
                                           font-semibold uppercase tracking-wide
                                           text-gray-500">
                                    Prefix
                                </th>

                                <th class="px-6 py-3 text-center text-xs
                                           font-semibold uppercase tracking-wide
                                           text-gray-500">
                                    Items
                                </th>

                                <th class="px-6 py-3 text-center text-xs
                                           font-semibold uppercase tracking-wide
                                           text-gray-500">
                                    Status
                                </th>

                                <th class="px-6 py-3 text-right text-xs
                                           font-semibold uppercase tracking-wide
                                           text-gray-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($categories as $category)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $category->name }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            {{ $category->category_code }}
                                        </div>

                                        @if ($category->description)
                                            <div class="mt-1 max-w-md truncate
                                                        text-sm text-gray-500">
                                                {{ $category->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-md bg-green-50
                                                     px-2.5 py-1 text-sm
                                                     font-semibold text-green-700">
                                            {{ $category->asset_prefix }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        {{ $category->items_count }}
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        @if ($category->status === 'active')
                                            <span class="rounded-full bg-green-100
                                                         px-3 py-1 text-xs
                                                         font-semibold text-green-700">
                                                Active
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100
                                                         px-3 py-1 text-xs
                                                         font-semibold text-gray-700">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a
                                                href="{{ route(
                                                    'categories.show',
                                                    $category
                                                ) }}"
                                                class="text-sm font-medium
                                                       text-blue-700
                                                       hover:text-blue-900"
                                            >
                                                View
                                            </a>

                                            @can('edit categories')
                                                <a
                                                    href="{{ route(
                                                        'categories.edit',
                                                        $category
                                                    ) }}"
                                                    class="text-sm font-medium
                                                           text-green-700
                                                           hover:text-green-900"
                                                >
                                                    Edit
                                                </a>

                                                <form
                                                    action="{{ route(
                                                        'categories.toggle-status',
                                                        $category
                                                    ) }}"
                                                    method="POST"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="text-sm font-medium
                                                               text-amber-700
                                                               hover:text-amber-900"
                                                    >
                                                        {{ $category->status
                                                            === 'active'
                                                            ? 'Deactivate'
                                                            : 'Activate' }}
                                                    </button>
                                                </form>
                                            @endcan

                                            @can('archive categories')
                                                <form
                                                    action="{{ route(
                                                        'categories.destroy',
                                                        $category
                                                    ) }}"
                                                    method="POST"
                                                    onsubmit="return confirm(
                                                        'Archive this category?'
                                                    )"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="text-sm font-medium
                                                               text-red-700
                                                               hover:text-red-900"
                                                    >
                                                        Archive
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="px-6 py-12 text-center
                                               text-gray-500"
                                    >
                                        No categories found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($categories->hasPages())
                    <div class="border-t bg-white px-6 py-4">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>