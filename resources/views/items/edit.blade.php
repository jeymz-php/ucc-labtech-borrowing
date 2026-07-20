<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Edit Item</h1>
                <p class="mt-1 text-sm text-gray-500">Update {{ $item->name }} and its inventory settings.</p>
            </div>
            <a href="{{ route('items.show', $item) }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Back to Item</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl">
        <form action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            @csrf
            @method('PUT')
            <div class="p-5 sm:p-7">@include('items.partials.form', ['item' => $item])</div>
            <div class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-7">
                <a href="{{ route('items.show', $item) }}" class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-semibold text-gray-700 hover:bg-gray-100">Cancel</a>
                <button type="submit" class="rounded-xl bg-green-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800">Update Item</button>
            </div>
        </form>
    </div>
</x-app-layout>
