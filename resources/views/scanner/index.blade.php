<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                Equipment Scanner
            </h2>

            <div class="flex gap-2">
                <button
                    id="releaseMode"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg">
                    Release
                </button>

                <button
                    id="returnMode"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Return
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">

        <div class="max-w-7xl mx-auto px-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- CAMERA --}}

                <div class="bg-white rounded-xl shadow p-4">

                    <h3 class="font-bold text-lg mb-3">
                        Scanner
                    </h3>

                    <div
                        id="reader"
                        class="w-full border rounded-lg overflow-hidden">
                    </div>

                    <p
                        id="scannerStatus"
                        class="mt-4 text-center text-gray-500">

                        Waiting for Borrowing QR...

                    </p>

                </div>

                {{-- BORROWING --}}

                <div class="bg-white rounded-xl shadow p-4">

                    <h3 class="font-bold text-lg mb-3">
                        Borrowing
                    </h3>

                    <div id="borrowingPanel">

                        <p class="text-gray-400">
                            No borrowing loaded.
                        </p>

                    </div>

                </div>

                {{-- ITEMS --}}

                <div class="bg-white rounded-xl shadow p-4">

                    <div class="flex justify-between items-center">

                        <h3 class="font-bold text-lg">
                            Equipment
                        </h3>

                        <span
                            id="progress"
                            class="text-sm font-semibold">

                            0 / 0

                        </span>

                    </div>

                    <div
                        id="equipmentList"
                        class="mt-4 space-y-2">

                    </div>

                </div>

            </div>

        </div>

    </div>

    @vite([
        'resources/js/scanner.js'
    ])

    <script src="https://unpkg.com/html5-qrcode"></script>

</x-app-layout>