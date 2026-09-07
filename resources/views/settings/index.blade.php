<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-gray-900">System Settings</h1>
            <p class="mt-1 text-sm text-gray-500">
                Configure system identity, borrowing defaults, and notifications.
            </p>
        </div>
    </x-slot>

    <div
        x-data="{
            tab: 'general',
            departments: @js(array_values((array) old('staff_departments', $staffDepartments ?? ['LabTech', 'CSD']))),
            newDepartment: '',
            addDepartment() {
                const value = this.newDepartment.trim();
                if (! value) return;
                const exists = this.departments.some(item => item.toLowerCase() === value.toLowerCase());
                if (! exists) this.departments.push(value);
                this.newDepartment = '';
            },
            removeDepartment(index) {
                if (this.departments.length <= 1) {
                    alert('At least one staff department is required.');
                    return;
                }
                this.departments.splice(index, 1);
            }
        }"
        class="mx-auto max-w-6xl space-y-6"
    >
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white p-2 shadow-sm">
            <div class="flex min-w-max gap-2">
                @php
                    $settingTabs = [
                        'general' => 'General',
                        'borrowing' => 'Borrowing',
                        'notifications' => 'Notifications',
                    ];

                    if (auth()->user()->hasRole('super_admin')) {
                        $settingTabs['departments'] = 'Departments';
                    }

                    $settingTabs['system'] = 'System Information';
                @endphp

                @foreach ($settingTabs as $key => $label)
                    <button
                        type="button"
                        @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}'
                            ? 'bg-green-700 text-white shadow-sm'
                            : 'text-gray-600 hover:bg-gray-100'"
                        class="rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <section x-show="tab === 'general'" x-cloak class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">General Settings</h2>
                    <p class="mt-1 text-sm text-gray-500">Basic system identity and regional preferences.</p>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="app_name" class="block text-sm font-semibold text-gray-700">Application Name</label>
                        <input id="app_name" name="app_name" type="text" value="{{ old('app_name', $settings['app_name'] ?? '') }}" required class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        @error('app_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="institution_name" class="block text-sm font-semibold text-gray-700">Institution Name</label>
                        <input id="institution_name" name="institution_name" type="text" value="{{ old('institution_name', $settings['institution_name'] ?? '') }}" required class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        @error('institution_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="support_email" class="block text-sm font-semibold text-gray-700">Support Email</label>
                        <input id="support_email" name="support_email" type="email" value="{{ old('support_email', $settings['support_email'] ?? '') }}" class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        @error('support_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="support_phone" class="block text-sm font-semibold text-gray-700">Support Phone</label>
                        <input id="support_phone" name="support_phone" type="text" value="{{ old('support_phone', $settings['support_phone'] ?? '') }}" class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        @error('support_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="timezone" class="block text-sm font-semibold text-gray-700">Timezone</label>
                        <select id="timezone" name="timezone" required class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', $settings['timezone'] ?? 'Asia/Manila') === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                        @error('timezone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="date_format" class="block text-sm font-semibold text-gray-700">Date and Time Format</label>
                        <select id="date_format" name="date_format" required class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                            @foreach ($dateFormats as $format => $example)
                                <option value="{{ $format }}" @selected(old('date_format', $settings['date_format'] ?? 'M d, Y h:i A') === $format)>{{ $example }}</option>
                            @endforeach
                        </select>
                        @error('date_format')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section x-show="tab === 'borrowing'" x-cloak class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Borrowing Settings</h2>
                    <p class="mt-1 text-sm text-gray-500">Default operational limits used by borrowing workflows.</p>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="max_items_per_borrowing" class="block text-sm font-semibold text-gray-700">Maximum Items per Borrowing</label>
                        <input id="max_items_per_borrowing" name="max_items_per_borrowing" type="number" min="1" max="100" value="{{ old('max_items_per_borrowing', $settings['max_items_per_borrowing'] ?? 10) }}" required class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        @error('max_items_per_borrowing')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="max_borrow_days" class="block text-sm font-semibold text-gray-700">Maximum Borrowing Days</label>
                        <input id="max_borrow_days" name="max_borrow_days" type="number" min="1" max="365" value="{{ old('max_borrow_days', $settings['max_borrow_days'] ?? 7) }}" required class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        @error('max_borrow_days')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="default_borrow_time" class="block text-sm font-semibold text-gray-700">Default Borrow Time</label>
                        <input id="default_borrow_time" name="default_borrow_time" type="time" value="{{ old('default_borrow_time', $settings['default_borrow_time'] ?? '08:00') }}" required class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        @error('default_borrow_time')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="default_return_time" class="block text-sm font-semibold text-gray-700">Default Return Time</label>
                        <input id="default_return_time" name="default_return_time" type="time" value="{{ old('default_return_time', $settings['default_return_time'] ?? '17:00') }}" required class="mt-2 block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                        @error('default_return_time')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ([
                        'allow_weekend_borrowing' => ['Allow weekend borrowing', 'Permit borrowing schedules on Saturdays and Sundays.'],
                        'auto_mark_overdue' => ['Automatically mark overdue transactions', 'Enable scheduled processes to change late released borrowings to overdue.'],
                    ] as $key => [$label, $description])
                        <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $settings[$key] ?? false)) class="mt-1 rounded border-gray-300 text-green-700 focus:ring-green-600">
                            <span>
                                <span class="block font-semibold text-gray-900">{{ $label }}</span>
                                <span class="mt-1 block text-sm text-gray-500">{{ $description }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section x-show="tab === 'notifications'" x-cloak class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Notification Settings</h2>
                    <p class="mt-1 text-sm text-gray-500">Control the notification channels used by the system.</p>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ([
                        'email_notifications' => ['Email notifications', 'Allow the application to send transactional email messages.'],
                        'borrower_notifications' => ['Borrower status notifications', 'Notify borrowers when requests are approved, rejected, released, returned, or extended.'],
                        'maintenance_notifications' => ['Maintenance notifications', 'Notify responsible staff about equipment requiring inspection or repair.'],
                    ] as $key => [$label, $description])
                        <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $settings[$key] ?? true)) class="mt-1 rounded border-gray-300 text-green-700 focus:ring-green-600">
                            <span>
                                <span class="block font-semibold text-gray-900">{{ $label }}</span>
                                <span class="mt-1 block text-sm text-gray-500">{{ $description }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            @if (auth()->user()->hasRole('super_admin'))
                <section x-show="tab === 'departments'" x-cloak class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Staff Departments</h2>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Manage the department choices available on the private staff registration page.
                                LabTech and CSD are included by default.
                            </p>
                        </div>
                        <span class="inline-flex w-fit rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">
                            Super Admin Only
                        </span>
                    </div>

                    <div class="mt-6 space-y-3">
                        <template x-for="(department, index) in departments" :key="index">
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                                <input
                                    type="text"
                                    name="staff_departments[]"
                                    x-model="departments[index]"
                                    required
                                    maxlength="120"
                                    class="min-w-0 flex-1 rounded-xl border-gray-300 bg-white focus:border-green-600 focus:ring-green-600"
                                >
                                <button
                                    type="button"
                                    @click="removeDepartment(index)"
                                    class="rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                >
                                    Remove
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <input
                            type="text"
                            x-model="newDepartment"
                            @keydown.enter.prevent="addDepartment()"
                            maxlength="120"
                            placeholder="Example: MIS, Registrar, Library"
                            class="flex-1 rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600"
                        >
                        <button
                            type="button"
                            @click="addDepartment()"
                            class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-black"
                        >
                            Add Department
                        </button>
                    </div>

                    <div class="mt-5 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm leading-6 text-blue-800">
                        Staff registrants can only choose one of the departments saved here.
                    </div>
                </section>
            @endif

            <section x-show="tab === 'system'" x-cloak class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">System Information</h2>
                    <p class="mt-1 text-sm text-gray-500">Read-only information about the current application environment.</p>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($systemInformation as $label => $value)
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <div x-show="tab !== 'system'" class="flex justify-end">
                <button type="submit" class="rounded-xl bg-green-700 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
