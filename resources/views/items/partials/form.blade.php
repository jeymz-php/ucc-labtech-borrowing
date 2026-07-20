@php
    $editing = isset($item);
@endphp

<div x-data="itemForm()" class="space-y-7">
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
            <p class="text-sm font-semibold text-red-700">Please correct the highlighted fields.</p>
            <ul class="mt-2 list-inside list-disc text-xs text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section>
        <div class="border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900">Item Information</h3>
            <p class="mt-1 text-sm text-gray-500">Enter the main details used to identify this equipment.</p>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="category_id" class="block text-sm font-semibold text-gray-700">Category <span class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" required class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600">
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $item->category_id ?? '') === (string) $category->id)>
                            {{ $category->name }} ({{ $category->asset_prefix }})
                        </option>
                    @endforeach
                </select>
                @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700">Item Name <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name', $item->name ?? '') }}" required maxlength="150" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600" placeholder="Example: Desktop Computer">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="brand" class="block text-sm font-semibold text-gray-700">Brand</label>
                <input id="brand" name="brand" type="text" value="{{ old('brand', $item->brand ?? '') }}" maxlength="100" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600" placeholder="Example: Acer">
            </div>

            <div>
                <label for="model" class="block text-sm font-semibold text-gray-700">Model</label>
                <input id="model" name="model" type="text" value="{{ old('model', $item->model ?? '') }}" maxlength="100" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600" placeholder="Example: Veriton X">
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-semibold text-gray-700">Description</label>
                <textarea id="description" name="description" rows="4" maxlength="2000" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600" placeholder="Describe the equipment and its intended use.">{{ old('description', $item->description ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <section>
        <div class="border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900">Stock and Location</h3>
            <p class="mt-1 text-sm text-gray-500">Configure monitoring thresholds and the equipment storage location.</p>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="minimum_stock" class="block text-sm font-semibold text-gray-700">Minimum Stock <span class="text-red-500">*</span></label>
                <input id="minimum_stock" name="minimum_stock" type="number" min="0" value="{{ old('minimum_stock', $item->minimum_stock ?? 0) }}" required class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600">
                <p class="mt-1 text-xs text-gray-500">The system marks this item as low stock when available units reach this value.</p>
            </div>

            <div>
                <label for="location" class="block text-sm font-semibold text-gray-700">Location</label>
                <input id="location" name="location" type="text" value="{{ old('location', $item->location ?? '') }}" maxlength="150" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600" placeholder="Example: Computer Laboratory 2">
            </div>

            <div>
                <label for="status" class="block text-sm font-semibold text-gray-700">Item Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" required class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600">
                    <option value="active" @selected(old('status', $item->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $item->status ?? 'active') === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
    </section>

    <section>
        <div class="border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900">Item Image</h3>
            <p class="mt-1 text-sm text-gray-500">Upload a JPG, PNG, or WebP image up to 4 MB.</p>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-[180px_1fr] sm:items-start">
            <div class="flex aspect-square items-center justify-center overflow-hidden rounded-2xl border border-dashed border-gray-300 bg-gray-50">
                <img x-show="previewUrl" x-cloak :src="previewUrl" alt="Item preview" class="h-full w-full object-cover">
                @if ($editing && $item->image_url)
                    <img x-show="!previewUrl" src="{{ $item->image_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                @else
                    <div x-show="!previewUrl" class="px-4 text-center text-xs text-gray-400">No image selected</div>
                @endif
            </div>

            <div>
                <input x-ref="imageInput" x-on:change="previewImage($event)" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-green-100 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-green-700 hover:file:bg-green-200">
                @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <button x-show="previewUrl" x-cloak type="button" x-on:click="clearImage()" class="mt-3 text-sm font-semibold text-red-600 hover:text-red-700">Clear selected image</button>

                @if ($editing && $item->image)
                    <label class="mt-4 flex items-start gap-2">
                        <input name="remove_image" value="1" type="checkbox" class="mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                        <span class="text-sm text-gray-600">Remove the current image when saving.</span>
                    </label>
                @endif
            </div>
        </div>
    </section>

    @unless ($editing)
        <section>
            <div class="border-b border-gray-100 pb-3">
                <h3 class="text-base font-bold text-gray-900">Initial Physical Units</h3>
                <p class="mt-1 text-sm text-gray-500">Generate asset-numbered physical units together with this item.</p>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="initial_units_count" class="block text-sm font-semibold text-gray-700">Number of Units <span class="text-red-500">*</span></label>
                    <input x-model.number="unitCount" id="initial_units_count" name="initial_units_count" type="number" min="0" max="500" value="{{ old('initial_units_count', 0) }}" required class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600">
                </div>

                <div>
                    <label for="initial_condition" class="block text-sm font-semibold text-gray-700">Initial Condition</label>
                    <select id="initial_condition" name="initial_condition" :required="unitCount > 0" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600">
                        @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'damaged' => 'Damaged', 'for_repair' => 'For Repair', 'unserviceable' => 'Unserviceable'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('initial_condition', 'good') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="acquisition_date" class="block text-sm font-semibold text-gray-700">Acquisition Date</label>
                    <input id="acquisition_date" name="acquisition_date" type="date" value="{{ old('acquisition_date') }}" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600">
                </div>

                <div>
                    <label for="acquisition_cost" class="block text-sm font-semibold text-gray-700">Cost per Unit</label>
                    <input id="acquisition_cost" name="acquisition_cost" type="number" min="0" step="0.01" value="{{ old('acquisition_cost') }}" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600" placeholder="0.00">
                </div>

                <div class="sm:col-span-2">
                    <label for="unit_remarks" class="block text-sm font-semibold text-gray-700">Unit Remarks</label>
                    <textarea id="unit_remarks" name="unit_remarks" rows="3" maxlength="1000" class="mt-2 block w-full rounded-xl border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-green-600 focus:ring-green-600">{{ old('unit_remarks') }}</textarea>
                </div>
            </div>
        </section>
    @endunless
</div>

<script>
    function itemForm() {
        return {
            unitCount: Number(@js(old('initial_units_count', 0))),
            previewUrl: null,
            previewImage(event) {
                const file = event.target.files[0];
                if (!file) return this.clearImage();
                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = URL.createObjectURL(file);
            },
            clearImage() {
                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
                if (this.$refs.imageInput) this.$refs.imageInput.value = '';
            }
        };
    }
</script>
