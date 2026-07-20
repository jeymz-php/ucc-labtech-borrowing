<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h1 class="text-xl font-bold text-gray-900">Archived Items</h1><p class="mt-1 text-sm text-gray-500">Restore previously archived inventory records.</p></div>
            <a href="{{ route('items.index') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back to Inventory</a>
        </div>
    </x-slot>

    <div class="space-y-5">
        @if (session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row">
                <input name="search" value="{{ $search }}" type="search" class="min-w-0 flex-1 rounded-xl border-gray-300 px-4 py-2.5 text-sm focus:border-green-600 focus:ring-green-600" placeholder="Search archived items">
                <button class="rounded-xl bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">Search</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Item</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Category</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Archived</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Action</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($items as $item)
                            <tr><td class="px-5 py-4"><p class="font-semibold text-gray-900">{{ $item->name }}</p><p class="text-xs text-gray-500">{{ $item->item_code }}</p></td><td class="px-5 py-4 text-sm text-gray-600">{{ $item->category?->name ?? 'Unavailable' }}</td><td class="px-5 py-4 text-sm text-gray-600">{{ $item->deleted_at?->format('M d, Y h:i A') }}</td><td class="px-5 py-4 text-right"><form method="POST" action="{{ route('items.restore', $item->id) }}">@csrf @method('PATCH')<button class="rounded-lg bg-green-700 px-4 py-2 text-xs font-semibold text-white hover:bg-green-800">Restore</button></form></td></tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-gray-500">No archived items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <div>{{ $items->links() }}</div>
    </div>
</x-app-layout>
