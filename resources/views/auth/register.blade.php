<x-guest-layout>
    <div
        x-data="registrationForm()"
        class="mx-auto flex w-full max-w-2xl flex-col"
    >
        <div class="shrink-0 text-center lg:text-left">
            <p
                class="text-[10px] font-semibold uppercase tracking-wider
                       text-green-700 sm:text-xs"
            >
                Account Registration
            </p>

            <h1 class="mt-1 text-xl font-bold text-gray-900 sm:text-2xl">
                Create your account
            </h1>

            <p class="mt-1 text-[11px] text-gray-500 sm:text-xs">
                Your temporary password will be sent to your email.
            </p>
        </div>

        <div class="mt-3 grid shrink-0 grid-cols-3 gap-2">
            <template x-for="number in [1, 2, 3]" :key="number">
                <div>
                    <div
                        class="h-1.5 rounded-full transition"
                        :class="step >= number
                            ? 'bg-green-700'
                            : 'bg-gray-200'"
                    ></div>

                    <p
                        class="mt-1 text-center text-[9px] font-semibold
                               sm:text-[10px]"
                        :class="step >= number
                            ? 'text-green-700'
                            : 'text-gray-400'"
                        x-text="
                            number === 1
                                ? 'Personal'
                                : number === 2
                                    ? 'University'
                                    : 'Confirmation'
                        "
                    ></p>
                </div>
            </template>
        </div>

        @if ($errors->any())
            <div
                class="mt-2 rounded-lg border border-red-200
                       bg-red-50 px-3 py-2"
            >
                <ul class="list-inside list-disc text-[10px] text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('register') }}"
            class="mt-3"
        >
            @csrf

            {{-- Step 1: Personal --}}
            <section
                x-show="step === 1"
                x-cloak
                class="space-y-3"
            >
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
                        'student' => 'Student',
                        'professor' => 'Professor',
                        'faculty' => 'Faculty',
                    ] as $value => $label)
                        <label
                            class="cursor-pointer rounded-lg border
                                   px-2 py-2 text-center transition"
                            :class="selectedRole === '{{ $value }}'
                                ? 'border-green-600 bg-green-50 ring-1 ring-green-600'
                                : 'border-gray-200 bg-white'"
                        >
                            <input
                                type="radio"
                                name="role"
                                value="{{ $value }}"
                                class="sr-only"
                                x-model="selectedRole"
                            >

                            <span
                                class="block text-[10px] font-bold sm:text-xs"
                                :class="selectedRole === '{{ $value }}'
                                    ? 'text-green-800'
                                    : 'text-gray-700'"
                            >
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label" for="id_number">
                            ID Number *
                        </label>

                        <input
                            id="id_number"
                            name="id_number"
                            type="text"
                            value="{{ old('id_number') }}"
                            maxlength="8"
                            minlength="8"
                            required
                            class="auth-input"
                            placeholder="12345678"
                        >
                    </div>

                    <div>
                        <label class="form-label" for="contact_number">
                            Contact Number
                        </label>

                        <input
                            id="contact_number"
                            name="contact_number"
                            type="text"
                            value="{{ old('contact_number') }}"
                            class="auth-input"
                            placeholder="09XXXXXXXXX"
                        >
                    </div>

                    <div>
                        <label class="form-label" for="first_name">
                            First Name *
                        </label>

                        <input
                            id="first_name"
                            name="first_name"
                            type="text"
                            value="{{ old('first_name') }}"
                            required
                            class="auth-input"
                        >
                    </div>

                    <div>
                        <label class="form-label" for="middle_name">
                            Middle Name
                        </label>

                        <input
                            id="middle_name"
                            name="middle_name"
                            type="text"
                            value="{{ old('middle_name') }}"
                            class="auth-input"
                        >
                    </div>

                    <div>
                        <label class="form-label" for="last_name">
                            Last Name *
                        </label>

                        <input
                            id="last_name"
                            name="last_name"
                            type="text"
                            value="{{ old('last_name') }}"
                            required
                            class="auth-input"
                        >
                    </div>

                    <div>
                        <label class="form-label" for="suffix">
                            Suffix
                        </label>

                        <input
                            id="suffix"
                            name="suffix"
                            type="text"
                            value="{{ old('suffix') }}"
                            class="auth-input"
                            placeholder="Jr., Sr., III"
                        >
                    </div>
                </div>
            </section>

            {{-- Step 2: University --}}
            <section
                x-show="step === 2"
                x-cloak
                class="space-y-3"
            >
                <div>
                    <label class="form-label" for="campus">
                        Campus *
                    </label>

                    <select
                        id="campus"
                        name="campus"
                        required
                        class="auth-input"
                    >
                        <option value="">Select campus</option>

                        @foreach ([
                            'Main Campus',
                            'Congressional Extension Campus',
                            'Camarin Extension Campus',
                            'Bagong Silang Extension Campus',
                        ] as $campus)
                            <option
                                value="{{ $campus }}"
                                @selected(old('campus') === $campus)
                            >
                                {{ $campus }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div
                    x-show="selectedRole === 'student'"
                    class="grid grid-cols-2 gap-3"
                >
                    <div class="col-span-2">
                        <label class="form-label" for="program">
                            Program *
                        </label>

                        <input
                            id="program"
                            name="program"
                            type="text"
                            value="{{ old('program') }}"
                            class="auth-input"
                            placeholder="BS Information Technology"
                        >
                    </div>

                    <div>
                        <label class="form-label" for="year_level">
                            Year Level *
                        </label>

                        <select
                            id="year_level"
                            name="year_level"
                            class="auth-input"
                        >
                            <option value="">Select</option>
                            <option value="1" @selected(old('year_level') === '1')>
                                First Year
                            </option>
                            <option value="2" @selected(old('year_level') === '2')>
                                Second Year
                            </option>
                            <option value="3" @selected(old('year_level') === '3')>
                                Third Year
                            </option>
                            <option value="4" @selected(old('year_level') === '4')>
                                Fourth Year
                            </option>
                            <option value="5" @selected(old('year_level') === '5')>
                                Fifth Year
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" for="section">
                            Section *
                        </label>

                        <input
                            id="section"
                            name="section"
                            type="text"
                            value="{{ old('section') }}"
                            class="auth-input"
                            placeholder="Example: 4A"
                        >
                    </div>
                </div>

                <div
                    x-show="
                        selectedRole === 'professor'
                        || selectedRole === 'faculty'
                    "
                >
                    <label class="form-label" for="department">
                        Department *
                    </label>

                    <input
                        id="department"
                        name="department"
                        type="text"
                        value="{{ old('department') }}"
                        class="auth-input"
                        placeholder="Information Technology Department"
                    >
                </div>
            </section>

            {{-- Step 3: Account confirmation --}}
            <section
                x-show="step === 3"
                x-cloak
                class="space-y-4"
            >
                <div>
                    <label class="form-label" for="email">
                        Email Address *
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        class="auth-input"
                        placeholder="your@email.com"
                    >

                    <p class="mt-1 text-[10px] leading-4 text-gray-500">
                        A temporary password will be sent to this address.
                    </p>
                </div>

                <div
                    class="rounded-lg border border-green-200
                           bg-green-50 px-3 py-2.5"
                >
                    <p class="text-[10px] leading-4 text-green-800 sm:text-xs">
                        After registration, check your inbox and spam folder.
                        You will be asked to replace the temporary password
                        after your first login.
                    </p>
                </div>

                <label class="flex items-start gap-2">
                    <input
                        name="terms"
                        type="checkbox"
                        value="1"
                        required
                        class="mt-0.5 rounded border-gray-300 text-green-700
                               focus:ring-green-600"
                    >

                    <span class="text-[10px] leading-4 text-gray-600 sm:text-xs">
                        I have read and accept the

                        <button
                            type="button"
                            x-on:click="termsModal = true"
                            class="font-semibold text-green-700 underline"
                        >
                            Terms and Conditions
                        </button>.
                    </span>
                </label>

                <label class="flex items-start gap-2">
                    <input
                        name="privacy_policy"
                        type="checkbox"
                        value="1"
                        required
                        class="mt-0.5 rounded border-gray-300 text-green-700
                               focus:ring-green-600"
                    >

                    <span class="text-[10px] leading-4 text-gray-600 sm:text-xs">
                        I acknowledge the

                        <button
                            type="button"
                            x-on:click="privacyModal = true"
                            class="font-semibold text-green-700 underline"
                        >
                            Privacy Policy
                        </button>

                        and the processing of my information under the
                        Data Privacy Act of 2012.
                    </span>
                </label>
            </section>

            <div
                class="mt-4 flex items-center justify-between
                       border-t border-gray-200 pt-3"
            >
                <div>
                    <button
                        type="button"
                        x-show="step > 1"
                        x-on:click="previousStep()"
                        class="rounded-lg border border-gray-300
                               px-4 py-2 text-xs font-semibold text-gray-700
                               hover:bg-gray-50"
                    >
                        Previous
                    </button>

                    <a
                        x-show="step === 1"
                        href="{{ route('login') }}"
                        class="text-xs font-semibold text-green-700"
                    >
                        Back to login
                    </a>
                </div>

                <button
                    type="button"
                    x-show="step < 3"
                    x-on:click="nextStep()"
                    class="rounded-lg bg-green-700 px-5 py-2
                           text-xs font-semibold text-white hover:bg-green-800"
                >
                    Continue
                </button>

                <button
                    type="submit"
                    x-show="step === 3"
                    class="rounded-lg bg-green-700 px-5 py-2
                           text-xs font-semibold text-white hover:bg-green-800"
                >
                    Create Account
                </button>
            </div>
        </form>

        {{-- Terms Modal --}}
        <div
            x-show="termsModal"
            x-cloak
            x-on:keydown.escape.window="termsModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center
                   bg-black/60 p-4"
        >
            <div
                x-on:click.outside="termsModal = false"
                class="flex max-h-[85dvh] w-full max-w-2xl flex-col
                       overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b
                           border-gray-200 px-5 py-4"
                >
                    <h2 class="text-lg font-bold text-gray-900">
                        Terms and Conditions
                    </h2>

                    <button
                        type="button"
                        x-on:click="termsModal = false"
                        class="rounded-lg px-2 py-1 text-xl text-gray-500
                               hover:bg-gray-100"
                    >
                        &times;
                    </button>
                </div>

                <div
                    class="overflow-y-auto px-5 py-4 text-sm
                           leading-6 text-gray-600"
                >
                    <h3 class="font-bold text-gray-900">
                        1. Eligibility and accurate information
                    </h3>

                    <p class="mt-1">
                        Registration is intended for authorized University of
                        Caloocan City students, professors, and faculty members.
                        Users must submit accurate and current information.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        2. Account responsibility
                    </h3>

                    <p class="mt-1">
                        Users are responsible for protecting their login
                        credentials and for activities performed through their
                        accounts. Temporary and permanent passwords must not be
                        shared with another person.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        3. Proper system use
                    </h3>

                    <p class="mt-1">
                        The system may be used only for legitimate laboratory
                        borrowing, inventory, support, and university-related
                        transactions. Misuse, unauthorized access, false
                        requests, and attempts to manipulate records are
                        prohibited.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        4. Borrowed equipment
                    </h3>

                    <p class="mt-1">
                        Borrowers must observe approved schedules, return
                        equipment on time, use equipment responsibly, and
                        report loss, damage, or malfunction immediately.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        5. Account administration
                    </h3>

                    <p class="mt-1">
                        The university may verify registration details and may
                        suspend or restrict accounts that contain inaccurate
                        information, violate university policy, or present a
                        security risk.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        6. System availability
                    </h3>

                    <p class="mt-1">
                        Availability may be interrupted for maintenance,
                        security updates, technical issues, or other
                        operational requirements.
                    </p>
                </div>

                <div class="border-t border-gray-200 px-5 py-3 text-right">
                    <button
                        type="button"
                        x-on:click="termsModal = false"
                        class="rounded-lg bg-green-700 px-5 py-2
                               text-sm font-semibold text-white
                               hover:bg-green-800"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        {{-- Privacy Modal --}}
        <div
            x-show="privacyModal"
            x-cloak
            x-on:keydown.escape.window="privacyModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center
                   bg-black/60 p-4"
        >
            <div
                x-on:click.outside="privacyModal = false"
                class="flex max-h-[85dvh] w-full max-w-2xl flex-col
                       overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b
                           border-gray-200 px-5 py-4"
                >
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Privacy Policy
                        </h2>

                        <p class="text-xs text-gray-500">
                            Republic Act No. 10173
                        </p>
                    </div>

                    <button
                        type="button"
                        x-on:click="privacyModal = false"
                        class="rounded-lg px-2 py-1 text-xl text-gray-500
                               hover:bg-gray-100"
                    >
                        &times;
                    </button>
                </div>

                <div
                    class="overflow-y-auto px-5 py-4 text-sm
                           leading-6 text-gray-600"
                >
                    <p>
                        The UCC LabTech Borrowing Management System collects
                        and processes personal information in support of
                        account registration, identity verification,
                        equipment borrowing, inventory accountability,
                        transaction history, support requests, security,
                        reporting, and university operations.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        Information collected
                    </h3>

                    <p class="mt-1">
                        Information may include your university ID number,
                        name, email address, contact number, campus, program,
                        department, year level, section, role, login activity,
                        borrowing transactions, return records, and support
                        communications.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        Purpose of processing
                    </h3>

                    <p class="mt-1">
                        Information is processed to create and secure user
                        accounts, verify eligibility, manage equipment,
                        document borrowing and returns, communicate important
                        account information, investigate incidents, and
                        prepare authorized university reports.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        Information sharing
                    </h3>

                    <p class="mt-1">
                        Personal information will be accessible only to
                        authorized university personnel and service providers
                        when necessary for legitimate system operation, legal
                        compliance, security, or authorized university
                        functions.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        Protection and retention
                    </h3>

                    <p class="mt-1">
                        Reasonable organizational, physical, and technical
                        safeguards will be used to protect personal
                        information. Records will be retained only for as long
                        as necessary for their stated purpose, university
                        policy, legal obligations, and authorized archival
                        requirements.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        Data subject rights
                    </h3>

                    <p class="mt-1">
                        Subject to applicable law and university procedures,
                        users may request access to or correction of their
                        personal information and may raise concerns regarding
                        its processing. Certain requests may be limited when
                        retention or processing is required by law or a
                        legitimate university function.
                    </p>

                    <h3 class="mt-4 font-bold text-gray-900">
                        Privacy inquiries
                    </h3>

                    <p class="mt-1">
                        Privacy-related inquiries should be directed to the
                        designated University of Caloocan City office or Data
                        Protection Officer once the official contact details
                        are added to this policy.
                    </p>
                </div>

                <div class="border-t border-gray-200 px-5 py-3 text-right">
                    <button
                        type="button"
                        x-on:click="privacyModal = false"
                        class="rounded-lg bg-green-700 px-5 py-2
                               text-sm font-semibold text-white
                               hover:bg-green-800"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function registrationForm() {
            return {
                step: {{ $errors->any() ? 1 : 1 }},
                selectedRole: @js(old('role', 'student')),
                termsModal: false,
                privacyModal: false,

                nextStep() {
                    if (this.step < 3) {
                        this.step++;
                    }
                },

                previousStep() {
                    if (this.step > 1) {
                        this.step--;
                    }
                }
            };
        }
    </script>
</x-guest-layout>