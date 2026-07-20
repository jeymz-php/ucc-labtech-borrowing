<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Inventory</h1>
                <p class="mt-1 text-sm text-gray-500">Manage equipment types, stock levels, images, and physical units.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('restore items')
                    <a href="{{ route('items.archived') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Archived Items</a>
                @endcan
                @can('create items')
                    <a href="{{ route('items.create') }}" class="rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800">Add Item</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Total Items', 'value' => $summary['total']],
                ['label' => 'Active Items', 'value' => $summary['active']],
                ['label' => 'Available Units', 'value' => $summary['available_units']],
                ['label' => 'Low Stock', 'value' => $summary['low_stock']],
            ] as $card)
                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $card['label'] }}</p>
                    <p class="mt-3 text-3xl font-bold {{ $card['label'] === 'Low Stock' && $card['value'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ number_format($card['value']) }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('items.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="xl:col-span-2">
                    <label for="search" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Search</label>
                    <input id="search" name="search" value="{{ $search }}" type="search" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm focus:border-green-600 focus:ring-green-600" placeholder="Code, name, brand, or model">
                </div>
                <div>
                    <label for="category" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Category</label>
                    <select id="category" name="category" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm focus:border-green-600 focus:ring-green-600">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                    <select id="status" name="status" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm focus:border-green-600 focus:ring-green-600">
                        <option value="">All statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div>
                    <label for="stock" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Stock</label>
                    <div class="mt-2 flex gap-2">
                        <select id="stock" name="stock" class="min-w-0 flex-1 rounded-xl border-gray-300 px-3 py-2.5 text-sm focus:border-green-600 focus:ring-green-600">
                            <option value="">All</option>
                            <option value="available" @selected($stock === 'available')>Available</option>
                            <option value="low" @selected($stock === 'low')>Low Stock</option>
                            <option value="out" @selected($stock === 'out')>Out of Stock</option>
                        </select>
                        <button class="rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">Filter</button>
                    </div>
                </div>
            </form>
            @if ($search || $categoryId || $status || $stock)
                <div class="mt-3 text-right"><a href="{{ route('items.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-800">Clear filters</a></div>
            @endif
        </section>

        @if ($items->isEmpty())
            <section class="rounded-2xl border-2 border-dashed border-gray-200 bg-white px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-700">
                    <img src="{{ asset('images/icons/inventory_icon.png') }}" alt="" class="h-7 w-7 object-contain">
                </div>
                <h2 class="mt-4 text-lg font-bold text-gray-800">No inventory items found</h2>
                <p class="mt-1 text-sm text-gray-500">Add an item or adjust your current filters.</p>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($items as $item)
                    <article class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-green-300 hover:shadow-md">
                        <div class="flex h-44 items-center justify-center overflow-hidden bg-gray-100">
                            @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-green-100 text-xl font-bold text-green-700">{{ strtoupper(substr($item->name, 0, 2)) }}</div>
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-green-700">{{ $item->item_code }}</p>
                                    <h2 class="mt-1 truncate text-lg font-bold text-gray-900">{{ $item->name }}</h2>
                                    <p class="mt-1 truncate text-sm text-gray-500">{{ $item->brand ?: 'No brand' }}{{ $item->model ? ' • '.$item->model : '' }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold capitalize {{ $item->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $item->status }}</span>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-gray-50 p-3 text-center">
                                <div><p class="text-lg font-bold text-gray-900">{{ $item->quantity_total }}</p><p class="text-[10px] uppercase text-gray-400">Total</p></div>
                                <div><p class="text-lg font-bold text-green-700">{{ $item->quantity_available }}</p><p class="text-[10px] uppercase text-gray-400">Available</p></div>
                                <div><p class="text-lg font-bold {{ $item->isLowStock() ? 'text-amber-600' : 'text-gray-900' }}">{{ $item->minimum_stock }}</p><p class="text-[10px] uppercase text-gray-400">Minimum</p></div>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-3 border-t border-gray-100 pt-4">
                                <span class="truncate text-xs text-gray-500">{{ $item->category->name }}</span>
                                <a href="{{ route('items.show', $item) }}" class="text-sm font-semibold text-green-700 hover:text-green-800">View details</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <div>{{ $items->links() }}</div>
        @endif
    </div>
</x-app-layout>
