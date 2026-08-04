<x-public-layout>
    <div
        x-data="guestBorrowingForm()"
        x-init="startInventoryPolling()"
        x-effect="document.body.classList.toggle('overflow-hidden', agreementOpen)"
        class="space-y-6"
    >
        <section class="overflow-hidden rounded-3xl bg-green-800 text-white shadow-xl">
            <div class="relative px-6 py-8 sm:px-10 lg:px-12">
                <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-green-700"></div>
                <div class="absolute -bottom-28 left-1/3 h-56 w-56 rounded-full bg-emerald-600/50"></div>
                <div class="relative max-w-3xl">
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-green-50">
                        No account required
                    </span>
                    <h1 class="mt-4 text-3xl font-extrabold sm:text-4xl">Guest Borrowing Request</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-green-100 sm:text-base">
                        Enter your university information, choose equipment, accept the borrowing agreement, and receive a QR code for LabTech processing.
                    </p>
                </div>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
                <p class="font-bold">Please correct the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border p-4 transition" :class="step === 1 ? 'border-green-600 bg-green-50' : 'border-gray-200 bg-white'">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full font-bold" :class="step === 1 ? 'bg-green-700 text-white' : 'bg-gray-100 text-gray-500'">1</span>
                    <div>
                        <p class="font-bold text-gray-900">Borrower Information</p>
                        <p class="text-xs text-gray-500">Role and university details</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border p-4 transition" :class="step === 2 ? 'border-green-600 bg-green-50' : 'border-gray-200 bg-white'">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full font-bold" :class="step === 2 ? 'bg-green-700 text-white' : 'bg-gray-100 text-gray-500'">2</span>
                    <div>
                        <p class="font-bold text-gray-900">Schedule and Equipment</p>
                        <p class="text-xs text-gray-500">Borrowing request details</p>
                    </div>
                </div>
            </div>
        </div>

        <form
            id="guestBorrowingForm"
            method="POST"
            action="{{ route('guest-borrowings.store') }}"
            class="space-y-6"
        >
            @csrf

            <section x-show="step === 1" x-transition class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Select your role</h2>
                    <p class="mt-1 text-sm text-gray-500">The required fields adjust automatically based on the selected borrower role.</p>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    @foreach ([
                        'student' => ['Student', 'Program, year, section, and Student ID'],
                        'professor' => ['Professor', 'Department information'],
                        'faculty_staff' => ['Faculty / Staff', 'Staff identification information'],
                    ] as $value => [$label, $description])
                        <label class="cursor-pointer rounded-2xl border p-4 transition" :class="role === '{{ $value }}' ? 'border-green-600 bg-green-50 ring-1 ring-green-600' : 'border-gray-200 hover:border-green-300'">
                            <input type="radio" name="role" value="{{ $value }}" x-model="role" class="sr-only" required>
                            <span class="block font-bold text-gray-900">{{ $label }}</span>
                            <span class="mt-1 block text-xs leading-5 text-gray-500">{{ $description }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-7 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="full_name" class="text-sm font-semibold text-gray-700">Complete Name</label>
                        <input id="full_name" name="full_name" value="{{ old('full_name') }}" required autocomplete="name" placeholder="First Name, Middle Name, Last Name, Suffix" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    </div>

                    <div x-show="role === 'student' || role === 'faculty_staff'" x-transition>
                        <label for="id_number" class="text-sm font-semibold text-gray-700" x-text="role === 'student' ? 'Student ID Number' : 'Faculty / Staff ID Number'"></label>
                        <input id="id_number" name="id_number" value="{{ old('id_number') }}" :required="role === 'student' || role === 'faculty_staff'" autocomplete="off" placeholder="Enter university ID number" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    </div>

                    <div :class="role === 'professor' ? 'sm:col-span-2' : ''">
                        <label for="email" class="text-sm font-semibold text-gray-700">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="room" class="text-sm font-semibold text-gray-700">
                            Your Room
                        </label>
                        <input
                            id="room"
                            type="text"
                            name="room"
                            value="{{ old('room') }}"
                            required
                            maxlength="120"
                            autocomplete="off"
                            placeholder="Example: Room 504, Laboratory 2, or Faculty Room"
                            class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600"
                        >
                        <p class="mt-1.5 text-xs text-gray-500">
                            Enter the room where the equipment will be used or delivered.
                        </p>
                    </div>

                    <template x-if="role === 'student'">
                        <div class="contents">
                            <div>
                                <label for="program" class="text-sm font-semibold text-gray-700">Program</label>
                                <input id="program" name="program" value="{{ old('program') }}" :required="role === 'student'" placeholder="BS Information Technology" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                            </div>
                            <div>
                                <label for="year_level" class="text-sm font-semibold text-gray-700">Year Level</label>
                                <select id="year_level" name="year_level" :required="role === 'student'" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                    <option value="">Select year level</option>
                                    @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year)
                                        <option value="{{ $year }}" @selected(old('year_level') === $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="section" class="text-sm font-semibold text-gray-700">Section</label>
                                <input id="section" name="section" value="{{ old('section') }}" :required="role === 'student'" placeholder="Example: 4A" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                            </div>
                        </div>
                    </template>

                    <div x-show="role === 'professor'" x-transition class="sm:col-span-2">
                        <label for="department" class="text-sm font-semibold text-gray-700">Department</label>
                        <input id="department" name="department" value="{{ old('department') }}" :required="role === 'professor'" placeholder="Enter department" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="button" x-on:click="continueToBorrowing()" class="rounded-xl bg-green-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-green-800">
                        Continue to Borrowing
                    </button>
                </div>
            </section>

            <section x-show="step === 2" x-cloak x-transition class="space-y-6">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Borrowing Information</h2>
                            <p class="mt-1 text-sm text-gray-500">Provide the schedule and reason for borrowing.</p>
                        </div>
                        <button type="button" x-on:click="step = 1; window.scrollTo({ top: 0, behavior: 'smooth' })" class="text-sm font-bold text-green-700 hover:text-green-800">Edit borrower information</button>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="purpose" class="text-sm font-semibold text-gray-700">Purpose</label>
                            <textarea id="purpose" name="purpose" rows="3" required class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">{{ old('purpose') }}</textarea>
                        </div>
                        <div>
                            <label for="borrow_at" class="text-sm font-semibold text-gray-700">Borrow date and time</label>
                            <input id="borrow_at" type="datetime-local" name="borrow_at" value="{{ old('borrow_at') }}" required class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                        </div>
                        <div>
                            <label for="expected_return_at" class="text-sm font-semibold text-gray-700">Expected return date and time</label>
                            <input id="expected_return_at" type="datetime-local" name="expected_return_at" value="{{ old('expected_return_at') }}" required class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="request_notes" class="text-sm font-semibold text-gray-700">Additional Notes</label>
                            <textarea id="request_notes" name="request_notes" rows="2" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">{{ old('request_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-6 sm:p-8">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Select Equipment Units</h2>
                                <p class="mt-1 text-sm text-gray-500">All active equipment remains visible. Only units marked Available can be selected.</p>
                            </div>
                            <div class="w-full lg:max-w-md">
                                <label for="guestEquipmentSearch" class="sr-only">Search equipment</label>
                                <div class="relative">
                                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
                                    <input id="guestEquipmentSearch" type="search" x-model="search" placeholder="Search equipment or asset number..." class="w-full rounded-xl border-gray-300 py-3 pl-11 pr-4 text-sm focus:border-green-600 focus:ring-green-600">
                                </div>
                                <p class="mt-2 text-xs text-gray-500"><span x-text="visibleCount"></span> equipment unit(s) shown · Live inventory updates automatically</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-6 sm:grid-cols-2 xl:grid-cols-3 sm:p-8">
                        @forelse ($units as $unit)
                            @php
                                $selectable = $unit->isBorrowable();
                                $location = $unit->location ?: $unit->item->location;
                                $searchText = strtolower(implode(' ', array_filter([
                                    $unit->item->display_name,
                                    $unit->asset_number,
                                    $unit->condition,
                                    $unit->availability_status,
                                    $location,
                                ])));
                            @endphp
                            <label
                                data-unit-card
                                data-unit-id="{{ $unit->id }}"
                                data-search="{{ $searchText }}"
                                x-show="matches($el.dataset.search)"
                                class="relative flex gap-3 rounded-2xl border p-4 transition"
                                :class="unitSelectable({{ $unit->id }}, {{ $selectable ? 'true' : 'false' }}) ? 'cursor-pointer border-gray-200 hover:border-green-400 hover:bg-green-50' : 'cursor-not-allowed border-gray-200 bg-gray-50 opacity-75'"
                            >
                                <input
                                    type="checkbox"
                                    name="item_unit_ids[]"
                                    value="{{ $unit->id }}"
                                    @checked(in_array($unit->id, old('item_unit_ids', [])))
                                    data-unit-checkbox="{{ $unit->id }}"
                                    :disabled="! unitSelectable({{ $unit->id }}, {{ $selectable ? 'true' : 'false' }})"
                                    class="mt-1 rounded border-gray-300 text-green-700 focus:ring-green-600"
                                >
                                <span class="min-w-0 flex-1">
                                    <span class="block font-bold text-gray-900">{{ $unit->item->display_name }}</span>
                                    <span class="mt-1 block text-xs text-gray-500">{{ $unit->asset_number ?: 'No asset number' }} · {{ ucfirst(str_replace('_', ' ', $unit->condition)) }}</span>
                                    <span class="mt-1 block text-xs text-gray-400">{{ $location ?: 'Location not specified' }}</span>
                                    <span data-unit-status="{{ $unit->id }}" class="mt-3 inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $unit->availability_status === 'available' ? 'bg-green-100 text-green-700' : ($unit->availability_status === 'borrowed' ? 'bg-violet-100 text-violet-700' : ($unit->availability_status === 'reserved' ? 'bg-amber-100 text-amber-700' : 'bg-gray-200 text-gray-700')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $unit->availability_status)) }}
                                    </span>
                                </span>
                            </label>
                        @empty
                            <div class="py-12 text-center text-gray-500 sm:col-span-2 xl:col-span-3">No equipment records are available.</div>
                        @endforelse
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" x-on:click="step = 1" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">Previous</button>
                    <button type="button" x-on:click="openAgreement()" class="rounded-xl bg-green-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-green-800">Continue to Proceed</button>
                </div>
            </section>

            <div
                x-show="agreementOpen"
                x-cloak
                x-transition.opacity
                x-on:keydown.escape.window="agreementOpen = false"
                x-on:click.self="agreementOpen = false"
                class="fixed inset-0 z-[10000] overflow-y-auto overscroll-contain bg-gray-950/70 p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="guestAgreementTitle"
            >
                <div class="flex min-h-full items-center justify-center">
                    <div
                        x-on:click.stop
                        class="flex w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:rounded-3xl"
                        style="max-height: calc(100dvh - 2rem);"
                    >
                        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-200 bg-white px-4 py-4 sm:px-6 sm:py-5">
                            <div class="min-w-0">
                                <h2 id="guestAgreementTitle" class="text-lg font-bold text-gray-900 sm:text-xl">
                                    Terms, Privacy, and Borrower Responsibility
                                </h2>
                                <p class="mt-1 text-xs leading-5 text-gray-500 sm:text-sm">
                                    Review every section and accept all three confirmations before submitting.
                                </p>
                            </div>

                            <button
                                type="button"
                                x-on:click="agreementOpen = false"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition hover:bg-gray-50 hover:text-gray-800"
                                aria-label="Close agreement modal"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div
                            id="agreementScrollArea"
                            class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 text-sm leading-6 text-gray-700 sm:space-y-5 sm:px-6 sm:py-5"
                            style="-webkit-overflow-scrolling: touch;"
                        >
                            <section class="rounded-2xl border border-green-200 bg-green-50 p-4 sm:p-5">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-700 text-xs font-bold text-white">1</span>
                                    <div>
                                        <h3 class="text-base font-bold text-green-950">Terms and Conditions</h3>
                                        <p class="mt-2 text-green-900">
                                            The borrower confirms that the information provided is accurate and that the selected equipment will be used only for legitimate university, academic, teaching, or authorized institutional purposes.
                                        </p>
                                        <p class="mt-2 text-green-900">
                                            The borrower agrees to follow the approved borrowing schedule, present the generated borrowing QR code when requested, and return all equipment to the LabTech Office on or before the expected return date and time.
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-600 text-xs font-bold text-white">2</span>
                                    <div>
                                        <h3 class="text-base font-bold text-amber-950">Lost or Damaged Equipment</h3>
                                        <p class="mt-2 text-amber-900">
                                            The borrower accepts responsibility for taking reasonable care of every borrowed unit. Any loss, theft, malfunction, physical damage, missing accessory, or other incident must be reported immediately to LabTech staff.
                                        </p>
                                        <p class="mt-2 text-amber-900">
                                            When loss or damage is determined to have resulted from misuse, negligence, unauthorized handling, or failure to follow borrowing rules, the borrower may be required to cover repair or replacement costs, subject to assessment and applicable University of Caloocan City policies.
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <section class="rounded-2xl border border-blue-200 bg-blue-50 p-4 sm:p-5">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-700 text-xs font-bold text-white">3</span>
                                    <div>
                                        <h3 class="text-base font-bold text-blue-950">Privacy Policy</h3>
                                        <p class="mt-2 text-blue-900">
                                            The submitted name, university identification, role, academic or department information, room, email address, schedule, and borrowing records will be processed for identity verification, equipment administration, communication, audit, and accountability purposes.
                                        </p>
                                        <p class="mt-2 text-blue-900">
                                            Information will be handled according to the Data Privacy Act of 2012 and applicable university data-handling policies.
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <div class="space-y-3 border-t border-gray-200 pt-4 sm:pt-5">
                                <p class="font-bold text-gray-900">Required confirmations</p>

                                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-green-300 hover:bg-green-50/40">
                                    <input type="checkbox" name="terms_accepted" value="1" x-model="termsAccepted" class="mt-1 rounded border-gray-300 text-green-700 focus:ring-green-600">
                                    <span>I have read and accept the Terms and Conditions.</span>
                                </label>

                                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-blue-300 hover:bg-blue-50/40">
                                    <input type="checkbox" name="privacy_accepted" value="1" x-model="privacyAccepted" class="mt-1 rounded border-gray-300 text-blue-700 focus:ring-blue-600">
                                    <span>I acknowledge the Privacy Policy and consent to processing of my information for this borrowing request.</span>
                                </label>

                                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 transition hover:border-amber-300">
                                    <input type="checkbox" name="liability_accepted" value="1" x-model="liabilityAccepted" class="mt-1 rounded border-amber-300 text-amber-700 focus:ring-amber-600">
                                    <span>I accept responsibility for the selected equipment and understand the possible repair or replacement obligation for loss or damage caused by misuse or negligence.</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-col-reverse gap-3 border-t border-gray-200 bg-white px-4 py-4 sm:flex-row sm:justify-end sm:px-6 sm:py-5">
                            <button
                                type="button"
                                x-on:click="agreementOpen = false"
                                class="w-full rounded-xl border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50 sm:w-auto"
                            >
                                Review Request
                            </button>

                            <button
                                type="submit"
                                :disabled="!canSubmitAgreement"
                                class="w-full rounded-xl bg-green-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                            >
                                Submit Guest Borrowing Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function guestBorrowingForm() {
            return {
                step: {{ $errors->has('purpose') || $errors->has('borrow_at') || $errors->has('expected_return_at') || $errors->has('item_unit_ids') ? 2 : 1 }},
                role: @json(old('role', 'student')),
                search: '',
                agreementOpen: false,
                termsAccepted: {{ old('terms_accepted') ? 'true' : 'false' }},
                privacyAccepted: {{ old('privacy_accepted') ? 'true' : 'false' }},
                liabilityAccepted: {{ old('liability_accepted') ? 'true' : 'false' }},
                liveUnits: {},
                pollTimer: null,
                inventoryRequestRunning: false,

                get canSubmitAgreement() {
                    return this.termsAccepted && this.privacyAccepted && this.liabilityAccepted;
                },

                get visibleCount() {
                    return Array.from(document.querySelectorAll('[data-unit-card]'))
                        .filter(card => this.matches(card.dataset.search || ''))
                        .length;
                },

                matches(value) {
                    const query = this.search.trim().toLowerCase();
                    return query === '' || String(value).toLowerCase().includes(query);
                },

                unitSelectable(id, fallback) {
                    return this.liveUnits[id]?.selectable ?? fallback;
                },

                continueToBorrowing() {
                    const fields = Array.from(document.querySelectorAll('#guestBorrowingForm [required]'))
                        .filter(field => field.offsetParent !== null && field.closest('[x-show="step === 1"]'));
                    const invalid = fields.find(field => !field.checkValidity());
                    if (invalid) {
                        invalid.reportValidity();
                        return;
                    }
                    this.step = 2;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                openAgreement() {
                    const form = document.getElementById('guestBorrowingForm');
                    const required = Array.from(form.querySelectorAll('[required]'))
                        .filter(field => field.offsetParent !== null && !field.closest('[role="dialog"]'));
                    const invalid = required.find(field => !field.checkValidity());
                    if (invalid) {
                        invalid.reportValidity();
                        return;
                    }
                    const selected = form.querySelectorAll('input[name="item_unit_ids[]"]:checked:not(:disabled)');
                    if (selected.length === 0) {
                        alert('Select at least one available equipment unit.');
                        return;
                    }
                    this.agreementOpen = true;
                    this.$nextTick(() => {
                        document.getElementById('agreementScrollArea')?.scrollTo({ top: 0 });
                    });
                },

                async refreshInventory() {
                    if (this.inventoryRequestRunning) {
                        return;
                    }

                    this.inventoryRequestRunning = true;

                    try {
                        const response = await fetch(@json(route('guest-borrowings.inventory')), {
                            headers: { 'Accept': 'application/json' },
                            cache: 'no-store',
                        });

                        if (response.status === 429) {
                            console.debug('Live inventory update paused briefly because of rate limiting.');
                            return;
                        }

                        if (!response.ok) {
                            return;
                        }

                        const data = await response.json();
                        this.liveUnits = Object.fromEntries(data.units.map(unit => [unit.id, unit]));

                        data.units.forEach(unit => {
                            const badge = document.querySelector(`[data-unit-status="${unit.id}"]`);
                            const checkbox = document.querySelector(`[data-unit-checkbox="${unit.id}"]`);

                            if (badge) {
                                badge.textContent = unit.availability_status.replaceAll('_', ' ');
                                badge.className = 'mt-3 inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide ' +
                                    (unit.availability_status === 'available'
                                        ? 'bg-green-100 text-green-700'
                                        : unit.availability_status === 'borrowed'
                                            ? 'bg-violet-100 text-violet-700'
                                            : unit.availability_status === 'reserved'
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-gray-200 text-gray-700');
                            }

                            if (checkbox) {
                                checkbox.disabled = !unit.selectable;

                                if (!unit.selectable) {
                                    checkbox.checked = false;
                                }
                            }
                        });
                    } catch (error) {
                        console.debug('Live inventory update unavailable.', error);
                    } finally {
                        this.inventoryRequestRunning = false;
                    }
                },

                startInventoryPolling() {
                    if (this.pollTimer) {
                        return;
                    }

                    this.refreshInventory();

                    this.pollTimer = window.setInterval(() => {
                        if (!document.hidden) {
                            this.refreshInventory();
                        }
                    }, 5000);
                },

                destroy() {
                    if (this.pollTimer) {
                        window.clearInterval(this.pollTimer);
                        this.pollTimer = null;
                    }
                },
            };
        }
    </script>
</x-public-layout>
