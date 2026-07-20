<x-app-layout>
    <x-slot name="header"><div><p class="text-sm font-medium text-green-700">Inventory / {{ $itemUnit->asset_number }}</p><h2 class="text-2xl font-bold text-gray-900">Edit Equipment Unit</h2></div></x-slot>
    <div class="mx-auto max-w-5xl"><form method="POST" action="{{ route('item-units.update',$itemUnit) }}" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
        @include('item-units.partials.form',['item'=>$itemUnit->item])
        <div class="mt-8 flex justify-end gap-3"><a href="{{ route('item-units.show',$itemUnit) }}" class="rounded-xl border border-gray-300 px-5 py-2.5 font-semibold text-gray-700">Cancel</a><button class="rounded-xl bg-green-700 px-5 py-2.5 font-semibold text-white hover:bg-green-800">Update Unit</button></div>
    </form></div>
</x-app-layout>
