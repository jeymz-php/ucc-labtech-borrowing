<div
    id="uccGlobalLoader"
    class="fixed inset-0 z-[20000] hidden items-center justify-center bg-slate-950/55 px-5 backdrop-blur-sm"
    role="status"
    aria-live="polite"
    aria-hidden="true"
>
    <div class="w-full max-w-sm overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl">
        <div class="relative overflow-hidden bg-green-800 px-6 pb-7 pt-8 text-center text-white">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-green-700/70"></div>
            <div class="absolute -bottom-14 -left-10 h-36 w-36 rounded-full bg-green-900/40"></div>

            <div class="relative mx-auto flex h-20 w-20 items-center justify-center">
                <span class="ucc-loader-ring absolute inset-0 rounded-full border-4 border-white/20 border-t-white"></span>
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white p-2 shadow-xl">
                    <img
                        src="{{ asset('images/UCC_Logo.png') }}"
                        alt=""
                        class="h-full w-full object-contain"
                    >
                </span>
            </div>

            <h2 id="uccGlobalLoaderTitle" class="relative mt-5 text-lg font-extrabold">
                Please wait
            </h2>
            <p id="uccGlobalLoaderMessage" class="relative mt-1 text-sm text-green-100">
                UCC LabTech is processing your request.
            </p>
        </div>

        <div class="bg-white px-6 py-5">
            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                <div class="ucc-loader-progress h-full w-1/3 rounded-full bg-green-700"></div>
            </div>
            <p class="mt-3 text-center text-xs text-gray-500">
                Please do not close or refresh this page.
            </p>
        </div>
    </div>
</div>
