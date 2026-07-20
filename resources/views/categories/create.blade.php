<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    Add Category
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Create a new inventory category.
                </p>
            </div>

            <a
                href="{{ route('categories.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2
                       text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <form
                    action="{{ route('categories.store') }}"
                    method="POST"
                >
                    @csrf

                    <div class="p-6">
                        @include('categories.partials.form')
                    </div>

                    <div class="flex justify-end gap-3 border-t bg-gray-50 p-6">
                        <a
                            href="{{ route('categories.index') }}"
                            class="rounded-lg border border-gray-300 bg-white
                                   px-5 py-2.5 text-sm font-medium text-gray-700
                                   hover:bg-gray-100"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="rounded-lg bg-green-700 px-5 py-2.5
                                   text-sm font-medium text-white
                                   hover:bg-green-800"
                        >
                            Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>