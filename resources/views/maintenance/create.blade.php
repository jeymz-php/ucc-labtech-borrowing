<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-bold text-gray-900">Create Maintenance Record</h2></x-slot>
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('maintenance.store') }}" class="space-y-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Equipment Unit</label>
                <select name="item_unit_id" required class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    <option value="">Select a unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('item_unit_id')==$unit->id)>{{ $unit->item->name }} — {{ $unit->asset_number ?: $unit->barcode_value }} ({{ ucwords(str_replace('_',' ',$unit->condition)) }})</option>
                    @endforeach
                </select>
                @error('item_unit_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Priority</label>
                <select name="priority" required class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    @foreach(['low','medium','high','critical'] as $priority)<option value="{{ $priority }}" @selected(old('priority','medium')===$priority)>{{ ucfirst($priority) }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Issue Title</label>
                <input name="issue_title" value="{{ old('issue_title') }}" required maxlength="255" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                @error('issue_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Issue Description</label>
                <textarea name="issue_description" rows="5" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">{{ old('issue_description') }}</textarea>
            </div>
            <div class="flex justify-end gap-3"><a href="{{ route('maintenance.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Cancel</a><button class="rounded-xl bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">Create Record</button></div>
        </form>
    </div>
</x-app-layout>
