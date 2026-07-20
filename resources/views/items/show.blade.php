<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-green-700">{{ $item->item_code }}</p>
                <h1 class="mt-1 text-xl font-bold text-gray-900">{{ $item->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">Inventory details and registered physical units.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('edit items')
                    <a href="{{ route('items.edit', $item) }}" class="rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-800">Edit Item</a>
                @endcan
                <a href="{{ route('items.index') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif

        <section class="grid gap-6 lg:grid-cols-[320px_1fr]">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex aspect-square items-center justify-center bg-gray-100">
                    @if ($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-3xl bg-green-100 text-3xl font-bold text-green-700">{{ strtoupper(substr($item->name, 0, 2)) }}</div>
                    @endif
                </div>
                <div class="p-5">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-bold capitalize {{ $item->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $item->status }}</span>
                        @if ($item->isLowStock())<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Low Stock</span>@endif
                    </div>
                    <dl class="mt-5 divide-y divide-gray-100 text-sm">
                        <div class="py-3"><dt class="text-xs uppercase text-gray-400">Category</dt><dd class="mt-1 font-semibold text-gray-800">{{ $item->category->name }}</dd></div>
                        <div class="py-3"><dt class="text-xs uppercase text-gray-400">Brand / Model</dt><dd class="mt-1 font-semibold text-gray-800">{{ $item->brand ?: '—' }}{{ $item->model ? ' / '.$item->model : '' }}</dd></div>
                        <div class="py-3"><dt class="text-xs uppercase text-gray-400">Location</dt><dd class="mt-1 font-semibold text-gray-800">{{ $item->location ?: 'Not specified' }}</dd></div>
                    </dl>
                </div>
            </div>

            <div class="space-y-6">
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Total Units', 'value' => $item->quantity_total, 'class' => 'text-gray-900'],
                        ['label' => 'Available', 'value' => $item->quantity_available, 'class' => 'text-green-700'],
                        ['label' => 'Borrowed', 'value' => $unitSummary['borrowed'] ?? 0, 'class' => 'text-blue-700'],
                        ['label' => 'Maintenance', 'value' => $unitSummary['maintenance'] ?? 0, 'class' => 'text-amber-600'],
                    ] as $card)
                        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $card['label'] }}</p><p class="mt-3 text-3xl font-bold {{ $card['class'] }}">{{ number_format($card['value']) }}</p></article>
                    @endforeach
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-bold text-gray-900">Item Details</h2>
                    <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Minimum Stock</dt><dd class="mt-1 text-sm font-semibold text-gray-800">{{ $item->minimum_stock }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Asset Prefix</dt><dd class="mt-1 text-sm font-semibold text-gray-800">{{ $item->category->asset_prefix }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Description</dt><dd class="mt-1 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $item->description ?: 'No description provided.' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Created By</dt><dd class="mt-1 text-sm text-gray-700">{{ $item->creator?->full_name ?? 'System' }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Last Updated</dt><dd class="mt-1 text-sm text-gray-700">{{ $item->updated_at->format('M d, Y h:i A') }}</dd></div>
                    </dl>
                </section>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="text-lg font-bold text-gray-900">Physical Units</h2><p class="mt-1 text-sm text-gray-500">Asset, barcode, condition, and availability records.</p></div>
                @can('create items')<div class="flex flex-wrap gap-2"><a href="{{ route('items.units.create', $item) }}" class="rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white">Add Unit</a><a href="{{ route('items.units.bulk-create', $item) }}" class="rounded-xl border border-green-700 px-4 py-2 text-sm font-semibold text-green-700">Add Multiple</a></div>@endcan
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Asset Number</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Serial / Property</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Condition</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Availability</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Acquired</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Action</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($units as $unit)
                            <tr class="hover:bg-gray-50"><td class="px-5 py-4"><p class="font-semibold text-gray-900">{{ $unit->asset_number }}</p><p class="text-xs text-gray-500">{{ $unit->barcode_value }}</p></td><td class="px-5 py-4 text-sm text-gray-600"><p>{{ $unit->serial_number ?: 'No serial number' }}</p><p class="text-xs text-gray-400">{{ $unit->property_number ?: 'No property number' }}</p></td><td class="px-5 py-4"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold capitalize text-gray-700">{{ str_replace('_', ' ', $unit->condition) }}</span></td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $unit->availability_status === 'available' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ $unit->availability_status }}</span></td><td class="px-5 py-4 text-sm text-gray-600">{{ $unit->acquisition_date?->format('M d, Y') ?? '—' }}</td><td class="px-5 py-4 text-right"><a href="{{ route('item-units.show', $unit) }}" class="text-sm font-semibold text-green-700">Manage</a></td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">No physical units are registered for this item.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($units->hasPages())<div class="border-t border-gray-100 px-5 py-4">{{ $units->links() }}</div>@endif
        </section>

        @can('edit items')
            <section class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="font-bold text-gray-900">Item Status</h2><p class="mt-1 text-sm text-gray-500">Inactive items remain recorded but are hidden from normal borrowing selections.</p></div>
                <form method="POST" action="{{ route('items.toggle-status', $item) }}">@csrf @method('PATCH')<button class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Set {{ $item->status === 'active' ? 'Inactive' : 'Active' }}</button></form>
            </section>
        @endcan

        @can('archive items')
            <section class="flex flex-col gap-4 rounded-2xl border border-red-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="font-bold text-red-700">Archive Item</h2><p class="mt-1 text-sm text-gray-500">The item can be restored later. Borrowed or reserved units prevent archiving.</p></div>
                <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('Archive this item and all of its physical units?')">@csrf @method('DELETE')<button class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Archive Item</button></form>
            </section>
        @endcan
    </div>
</x-app-layout>
