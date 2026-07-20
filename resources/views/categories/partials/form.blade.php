<div class="space-y-6">
    <div>
        <label
            for="name"
            class="block text-sm font-medium text-gray-700"
        >
            Category Name
            <span class="text-red-600">*</span>
        </label>

        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $category->name ?? '') }}"
            required
            maxlength="100"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                   focus:border-green-600 focus:ring-green-600"
            placeholder="Example: Laptop"
        >

        @error('name')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="asset_prefix"
            class="block text-sm font-medium text-gray-700"
        >
            Asset Prefix
            <span class="text-red-600">*</span>
        </label>

        <input
            id="asset_prefix"
            name="asset_prefix"
            type="text"
            value="{{ old(
                'asset_prefix',
                $category->asset_prefix ?? ''
            ) }}"
            required
            minlength="2"
            maxlength="10"
            class="mt-1 block w-full rounded-lg border-gray-300 uppercase
                   shadow-sm focus:border-green-600 focus:ring-green-600"
            placeholder="Example: LAP"
        >

        <p class="mt-1 text-xs text-gray-500">
            Used when generating asset numbers such as
            AST-LAP-000001.
        </p>

        @error('asset_prefix')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="description"
            class="block text-sm font-medium text-gray-700"
        >
            Description
        </label>

        <textarea
            id="description"
            name="description"
            rows="4"
            maxlength="1000"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                   focus:border-green-600 focus:ring-green-600"
            placeholder="Describe the category..."
        >{{ old(
            'description',
            $category->description ?? ''
        ) }}</textarea>

        @error('description')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="status"
            class="block text-sm font-medium text-gray-700"
        >
            Status
            <span class="text-red-600">*</span>
        </label>

        <select
            id="status"
            name="status"
            required
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                   focus:border-green-600 focus:ring-green-600"
        >
            <option
                value="active"
                @selected(
                    old(
                        'status',
                        $category->status ?? 'active'
                    ) === 'active'
                )
            >
                Active
            </option>

            <option
                value="inactive"
                @selected(
                    old(
                        'status',
                        $category->status ?? 'active'
                    ) === 'inactive'
                )
            >
                Inactive
            </option>
        </select>

        @error('status')
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>