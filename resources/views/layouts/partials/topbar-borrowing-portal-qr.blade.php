<div
    x-data="{ borrowingQrOpen: false }"
    @keydown.escape.window="borrowingQrOpen = false"
    class="relative"
>
    <button
        type="button"
        @click="borrowingQrOpen = true"
        class="relative flex h-11 w-11 items-center justify-center rounded-full
               border border-gray-200 bg-white text-gray-600 transition
               hover:border-green-300 hover:bg-green-50 hover:text-green-700
               focus:outline-none focus:ring-2 focus:ring-green-500
               focus:ring-offset-2"
        aria-label="Open borrowing QR code"
        title="Borrowing QR Code"
    >
        <svg
            class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.8"
                d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2v2h-2v-2zm4 0h2v4h-2v-4zm-4 4h4v2h-4v-2z"
            />
        </svg>
    </button>

    <template x-teleport="body">
        <div
            x-show="borrowingQrOpen"
            x-cloak
            x-transition.opacity
            @click.self="borrowingQrOpen = false"
            class="fixed inset-0 z-[10000] flex items-center justify-center
                   bg-gray-950/60 p-4 backdrop-blur-[1px]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="borrowing-qr-modal-title"
        >
            <div
                x-show="borrowingQrOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-3 scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                x-transition:leave-end="translate-y-3 scale-95 opacity-0"
                class="w-full max-w-md overflow-hidden rounded-2xl bg-white
                       shadow-2xl ring-1 ring-black/5"
            >
                <div class="flex items-start justify-between gap-4 px-6 pt-6">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center
                                   rounded-xl bg-green-100 text-green-700"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2v2h-2v-2zm4 0h2v4h-2v-4zm-4 4h4v2h-4v-2z"
                                />
                            </svg>
                        </span>

                        <div>
                            <h2
                                id="borrowing-qr-modal-title"
                                class="text-lg font-bold text-gray-900"
                            >
                                Guest Borrower QR
                            </h2>

                            <p class="mt-0.5 text-xs text-gray-500">
                                Open the public guest borrowing form
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="borrowingQrOpen = false"
                        class="flex h-9 w-9 shrink-0 items-center justify-center
                               rounded-xl border border-gray-200 text-gray-400
                               transition hover:bg-gray-50 hover:text-gray-700"
                        aria-label="Close borrowing QR modal"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 text-center">
                    <p class="text-sm leading-6 text-gray-600">
                        Scan this code on a phone or another device to open the Guest Borrower request page. No borrower account is required.
                    </p>

                    <div
                        class="mx-auto mt-5 flex w-fit items-center justify-center
                               rounded-2xl border border-gray-200 bg-white p-4
                               shadow-sm"
                    >
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                            ->size(260)
                            ->margin(1)
                            ->errorCorrection('H')
                            ->generate(route('guest-borrowings.create')) !!}
                    </div>

                    <div
                        class="mt-4 overflow-hidden rounded-xl bg-gray-50
                               px-4 py-3 text-xs text-gray-500"
                    >
                        <p class="truncate" title="{{ route('guest-borrowings.create') }}">
                            {{ route('guest-borrowings.create') }}
                        </p>
                    </div>

                    <div
                        class="mt-4 rounded-xl border border-green-200 bg-green-50
                               px-4 py-3 text-left text-xs leading-5 text-green-800"
                    >
                        <p>
                            <strong>Guest borrower:</strong> select Student, Professor,
                            or Faculty / Staff, provide university details, choose
                            equipment, and submit the request.
                        </p>

                        <p class="mt-1">
                            <strong>After submission:</strong> the system generates a
                            unique borrowing QR code for LabTech approval, release,
                            and return processing.
                        </p>
                    </div>

                    <a
                        href="{{ route('borrow.qr.download') }}"
                        class="mt-5 inline-flex items-center justify-center gap-2
                               rounded-xl bg-green-700 px-5 py-3 text-sm
                               font-semibold text-white shadow-sm transition
                               hover:bg-green-800 focus:outline-none focus:ring-2
                               focus:ring-green-600 focus:ring-offset-2"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M8 12l4 4m0 0l4-4m-4 4V4"
                            />
                        </svg>

                        Download Guest Portal QR
                    </a>
                </div>
            </div>
        </div>
    </template>
</div>
