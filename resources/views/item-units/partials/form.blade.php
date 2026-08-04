@php($unit = $itemUnit ?? null)
<div class="grid gap-6 lg:grid-cols-2">
    <div class="lg:col-span-2">
        <label class="mb-2 block text-sm font-semibold text-gray-700">Campus</label>
        @if ($canSelectCampus ?? false)
            <select name="campus" required class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                @foreach ($campuses as $campusOption)
                    <option value="{{ $campusOption }}" @selected(old('campus', $selectedCampus ?? $unit?->campus) === $campusOption)>
                        {{ $campusOption }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="campus" value="{{ $selectedCampus ?? $unit?->campus }}">
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                {{ $selectedCampus ?? $unit?->campus }}
            </div>
        @endif
        @error('campus')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Serial Number</label>
        <input name="serial_number" value="{{ old('serial_number', $unit?->serial_number) }}" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600" placeholder="Optional manufacturer serial">
        @error('serial_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Property Number</label>
        <input name="property_number" value="{{ old('property_number', $unit?->property_number) }}" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600" placeholder="Optional university property number">
        @error('property_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Acquisition Date</label>
        <input type="date" name="acquisition_date" value="{{ old('acquisition_date', optional($unit?->acquisition_date)->format('Y-m-d')) }}" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
        @error('acquisition_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Acquisition Cost</label>
        <input type="number" min="0" step="0.01" name="acquisition_cost" value="{{ old('acquisition_cost', $unit?->acquisition_cost) }}" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600" placeholder="0.00">
        @error('acquisition_cost')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Assigned Location</label>
        <input name="location" value="{{ old('location', $unit?->location ?? $item->location ?? '') }}" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600" placeholder="Laboratory, room, cabinet">
        @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Condition</label>
        <select name="condition" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
            @foreach(['excellent'=>'Excellent','good'=>'Good','fair'=>'Fair','damaged'=>'Damaged','for_repair'=>'For Repair','unserviceable'=>'Unserviceable'] as $value=>$label)
                <option value="{{ $value }}" @selected(old('condition', $unit?->condition ?? 'good') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('condition')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Availability</label>
        <select name="availability_status" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
            @foreach(['available'=>'Available','reserved'=>'Reserved','borrowed'=>'Borrowed','maintenance'=>'Maintenance','lost'=>'Lost'] as $value=>$label)
                <option value="{{ $value }}" @selected(old('availability_status', $unit?->availability_status ?? 'available') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('availability_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="lg:col-span-2">
        <label class="mb-2 block text-sm font-semibold text-gray-700">Remarks</label>
        <textarea name="remarks" rows="4" class="w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600" placeholder="Optional notes about this unit">{{ old('remarks', $unit?->remarks) }}</textarea>
        @error('remarks')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
