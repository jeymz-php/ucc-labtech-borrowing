<x-public-layout>
    <div class="mx-auto max-w-4xl">
        <section class="overflow-hidden rounded-3xl bg-gray-900 text-white shadow-xl">
            <div class="relative px-6 py-8 sm:px-10">
                <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-green-700/50"></div>
                <div class="relative">
                    <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-green-200">Private staff access</span>
                    <h1 class="mt-4 text-3xl font-extrabold">Create Staff Administrator Account</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-300">This protected registration page creates an active, email-verified LabTech administrator account. The private link must not be shared publicly.</p>
                </div>
            </div>
        </section>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('staff-registration.store', $registrationToken) }}" class="mt-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="id_number" class="text-sm font-semibold text-gray-700">Employee / Staff ID Number</label>
                    <input id="id_number" name="id_number" value="{{ old('id_number') }}" required class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                </div>
                <div>
                    <label for="email" class="text-sm font-semibold text-gray-700">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                </div>
                <div>
                    <label for="first_name" class="text-sm font-semibold text-gray-700">First Name</label>
                    <input id="first_name" name="first_name" value="{{ old('first_name') }}" required class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                </div>
                <div>
                    <label for="middle_name" class="text-sm font-semibold text-gray-700">Middle Name</label>
                    <input id="middle_name" name="middle_name" value="{{ old('middle_name') }}" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                </div>
                <div>
                    <label for="last_name" class="text-sm font-semibold text-gray-700">Last Name</label>
                    <input id="last_name" name="last_name" value="{{ old('last_name') }}" required class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                </div>
                <div>
                    <label for="suffix" class="text-sm font-semibold text-gray-700">Suffix</label>
                    <input id="suffix" name="suffix" value="{{ old('suffix') }}" placeholder="Jr., Sr., III" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                </div>
                <div>
                    <label for="campus" class="text-sm font-semibold text-gray-700">Campus</label>
                    <select id="campus" name="campus" required class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                        <option value="">Select campus</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus }}" @selected(old('campus') === $campus)>{{ $campus }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="department" class="text-sm font-semibold text-gray-700">Department / Office</label>
                    <input id="department" name="department" value="{{ old('department') }}" required placeholder="LabTech Office" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                </div>
                <div class="sm:col-span-2">
                    <label for="contact_number" class="text-sm font-semibold text-gray-700">Contact Number <span class="font-normal text-gray-400">(optional)</span></label>
                    <input id="contact_number" name="contact_number" value="{{ old('contact_number') }}" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                </div>
                <div x-data="{ visible: false }">
                    <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
                    <div class="relative mt-2">
                        <input id="password" name="password" x-bind:type="visible ? 'text' : 'password'" required autocomplete="new-password" class="w-full rounded-xl border-gray-300 pr-12 focus:border-green-600 focus:ring-green-600">
                        <button type="button" x-on:click="visible = !visible" class="absolute inset-y-0 right-0 px-4 text-xs font-bold text-green-700" x-text="visible ? 'Hide' : 'Show'"></button>
                    </div>
                </div>
                <div>
                    <label for="password_confirmation" class="text-sm font-semibold text-gray-700">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 p-5 text-sm leading-6 text-green-900">
                The account will be automatically approved, activated, and assigned the <strong>Admin</strong> role. After registration, you will be redirected to the staff login page.
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('login') }}" class="rounded-xl border border-gray-300 px-6 py-3 text-center text-sm font-bold text-gray-700 hover:bg-gray-50">Back to Login</a>
                <button type="submit" class="rounded-xl bg-green-700 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-green-800">Create Administrator Account</button>
            </div>
        </form>
    </div>
</x-public-layout>
