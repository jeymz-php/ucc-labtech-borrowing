<x-app-layout>
    <x-slot name="header"><div><p class="text-sm font-medium text-green-700">Inventory / {{ $item->item_code }}</p><h2 class="text-2xl font-bold text-gray-900">Add Equipment Unit</h2></div></x-slot>
    <div class="mx-auto max-w-5xl">
        <div class="mb-6 rounded-2xl border border-green-100 bg-green-50 p-5"><p class="font-bold text-green-900">{{ $item->display_name }}</p><p class="mt-1 text-sm text-green-700">The asset number and barcode will be generated automatically.</p></div>
        <form method="POST" action="{{ route('items.units.store',$item) }}" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">@csrf
            @include('item-units.partials.form')
            <div class="mt-8 flex justify-end gap-3"><a href="{{ route('items.show',$item) }}" class="rounded-xl border border-gray-300 px-5 py-2.5 font-semibold text-gray-700">Cancel</a><button class="rounded-xl bg-green-700 px-5 py-2.5 font-semibold text-white hover:bg-green-800">Save Unit</button></div>
        </form>
    </div>
</x-app-layout>
