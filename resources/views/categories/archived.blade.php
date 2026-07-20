<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    Archived Categories
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Restore previously archived categories.
                </p>
            </div>

            <a
                href="{{ route('categories.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2
                       text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Back to Categories
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-green-200
                            bg-green-50 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 rounded-xl bg-white p-5 shadow-sm">
                <form
                    method="GET"
                    action="{{ route('categories.archived') }}"
                    class="flex flex-col gap-3 sm:flex-row"
                >
                    <input
                        name="search"
                        type="text"
                        value="{{ $search }}"
                        placeholder="Search archived categories"
                        class="block flex-1 rounded-lg border-gray-300
                               focus:border-green-600 focus:ring-green-600"
                    >

                    <button
                        type="submit"
                        class="rounded-lg bg-green-700 px-5 py-2.5
                               text-sm font-medium text-white
                               hover:bg-green-800"
                    >
                        Search
                    </button>

                    <a
                        href="{{ route('categories.archived') }}"
                        class="rounded-lg border border-gray-300 bg-white
                               px-5 py-2.5 text-center text-sm font-medium
                               text-gray-700 hover:bg-gray-50"
                    >
                        Reset
                    </a>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs
                                           font-semibold uppercase text-gray-500">
                                    Category
                                </th>

                                <th class="px-6 py-3 text-left text-xs
                                           font-semibold uppercase text-gray-500">
                                    Prefix
                                </th>

                                <th class="px-6 py-3 text-left text-xs
                                           font-semibold uppercase text-gray-500">
                                    Archived
                                </th>

                                <th class="px-6 py-3 text-right text-xs
                                           font-semibold uppercase text-gray-500">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $category->name }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            {{ $category->category_code }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $category->asset_prefix }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $category->deleted_at?->format(
                                            'F d, Y h:i A'
                                        ) }}
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <form
                                            action="{{ route(
                                                'categories.restore',
                                                $category->id
                                            ) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="text-sm font-medium
                                                       text-green-700
                                                       hover:text-green-900"
                                            >
                                                Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="4"
                                        class="px-6 py-12 text-center
                                               text-gray-500"
                                    >
                                        No archived categories found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($categories->hasPages())
                    <div class="border-t px-6 py-4">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>