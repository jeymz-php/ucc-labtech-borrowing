<x-modal name="user-guide" maxWidth="7xl" focusable>
    <div class="flex h-[88vh] max-h-[920px] min-h-[540px] flex-col bg-white">
        {{-- Modal header --}}
        <div
            class="flex shrink-0 items-center justify-between gap-4
                   border-b border-gray-200 px-4 py-4 sm:px-6"
        >
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center
                           rounded-xl bg-green-100 text-green-700"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6.253v13M12 6.253C10.832 5.477
                               9.246 5 7.5 5S4.168 5.477 3 6.253v13
                               C4.168 18.477 5.754 18 7.5 18s3.332.477
                               4.5 1.253m0-13C13.168 5.477 14.754 5
                               16.5 5s3.332.477 4.5 1.253v13
                               C19.832 18.477 18.246 18 16.5 18
                               s-3.332.477-4.5 1.253"
                        />
                    </svg>
                </div>

                <div class="min-w-0">
                    <h2 class="truncate text-lg font-bold text-gray-900 sm:text-xl">
                        UCC LabTech User Guide
                    </h2>

                    <p class="mt-0.5 hidden text-sm text-gray-500 sm:block">
                        User Manual and Guide for the LabTech Borrowing Management System
                    </p>
                </div>
            </div>

            <button
                type="button"
                x-on:click="$dispatch('close')"
                class="flex h-10 w-10 shrink-0 items-center justify-center
                       rounded-xl text-gray-500 transition hover:bg-gray-100
                       hover:text-gray-800 focus:outline-none focus:ring-2
                       focus:ring-green-600 focus:ring-offset-2"
                aria-label="Close user guide"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18 18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        {{-- PDF viewer --}}
        <div class="min-h-0 flex-1 bg-gray-100 p-2 sm:p-4">
            <iframe
                src="{{ route('user-guide.show') }}#toolbar=1&navpanes=0&scrollbar=1&view=FitH"
                title="UCC LabTech Borrowing Management System User Manual"
                class="h-full w-full rounded-xl border border-gray-300 bg-white"
                loading="lazy"
            >
                <p>
                    Your browser cannot display PDF files.
                    <a href="{{ route('user-guide.show') }}" target="_blank" rel="noopener">
                        Open the user guide in a new tab.
                    </a>
                </p>
            </iframe>
        </div>

        {{-- Modal actions --}}
        <div
            class="flex shrink-0 flex-col-reverse gap-3 border-t
                   border-gray-200 bg-white px-4 py-4
                   sm:flex-row sm:items-center sm:justify-between sm:px-6"
        >
            <p class="text-xs leading-5 text-gray-500 sm:max-w-lg">
                Having trouble viewing the document? Open it in a new tab
                or download the PDF to your device.
            </p>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a
                    href="{{ route('user-guide.download') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl
                           border border-gray-300 px-4 py-2.5 text-sm
                           font-semibold text-gray-700 transition hover:bg-gray-50"
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
                            d="M4 16v1a3 3 0 003 3h10a3 3 0
                               003-3v-1m-4-4-4 4m0 0-4-4m4
                               4V4"
                        />
                    </svg>

                    Download PDF
                </a>

                <a
                    href="{{ route('user-guide.show') }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center justify-center gap-2 rounded-xl
                           bg-green-700 px-4 py-2.5 text-sm font-semibold
                           text-white shadow-sm transition hover:bg-green-800"
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
                            d="M14 3h7m0 0v7m0-7L10 14
                               M5 7v12a2 2 0 002 2h12a2 2 0
                               002-2v-5"
                        />
                    </svg>

                    Open in New Tab
                </a>
            </div>
        </div>
    </div>
</x-modal>
