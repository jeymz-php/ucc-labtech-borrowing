<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Equipment Scanner
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Select a transaction mode and scan the borrower’s QR code.
                </p>
            </div>

            <div class="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">
                LabTech Scanner
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-12">

                {{-- Scanner section --}}
                <section class="lg:col-span-5">
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                        <div class="border-b border-gray-200 px-5 py-4">
                            <h3 class="font-semibold text-gray-900">
                                QR / Barcode Scanner
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Select a transaction mode before scanning.
                            </p>
                        </div>

                        <div class="p-5">
                            {{-- Mode buttons --}}
                            <div class="mb-5 grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    id="releaseMode"
                                    class="rounded-xl px-4 py-3 text-sm font-semibold transition"
                                >
                                    Release Equipment
                                </button>

                                <button
                                    type="button"
                                    id="returnMode"
                                    class="rounded-xl px-4 py-3 text-sm font-semibold transition"
                                >
                                    Return Equipment
                                </button>
                            </div>

                            {{-- Camera scanner --}}
                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-3">
                                <div id="reader" class="w-full"></div>
                            </div>

                            {{-- Scanner status --}}
                            <div
                                id="scannerStatus"
                                class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700"
                            >
                                Waiting for Borrowing QR...
                            </div>

                            <div class="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                                <p class="font-semibold">
                                    QR scanning procedure
                                </p>

                                <ol class="mt-2 list-inside list-decimal space-y-1">
                                    <li>Select Release Equipment or Return Equipment.</li>
                                    <li>Scan the borrower’s borrowing QR code.</li>
                                    <li>The transaction will be processed immediately.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Borrowing details section --}}
                <section class="space-y-6 lg:col-span-7">

                    {{-- Borrowing information --}}
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    Borrowing Information
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Details of the currently scanned borrowing.
                                </p>
                            </div>
                        </div>

                        <div
                            id="borrowingPanel"
                            class="p-5"
                        >
                            <p class="text-gray-400">
                                No borrowing loaded.
                            </p>
                        </div>
                    </div>

                    {{-- Equipment list --}}
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    Required Equipment
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Each equipment unit must be verified before completion.
                                </p>
                            </div>

                            <div class="rounded-full bg-gray-100 px-4 py-2 text-sm font-bold text-gray-700">
                                <span id="progress">0 / 0</span>
                            </div>
                        </div>

                        <div class="p-5">
                            <div
                                id="equipmentList"
                                class="space-y-3"
                            >
                                <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-400">
                                    Equipment will appear after scanning a borrowing QR code.
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
            </div>
        </div>
    </div>

</x-app-layout>