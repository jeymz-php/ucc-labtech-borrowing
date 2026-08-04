<x-public-layout>
    @php
        $statusClasses = [
            'pending' => 'bg-amber-100 text-amber-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'released' => 'bg-violet-100 text-violet-800',
            'returned' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-700',
            'overdue' => 'bg-red-100 text-red-800',
        ];
    @endphp

    <div
        x-data="guestTracking({
            statusUrl: @js(route('guest-borrowings.status', $borrowing->public_token)),
            initialStatus: @js($borrowing->status),
            initialUpdatedAt: @js($borrowing->updated_at?->toIso8601String()),
        })"
        x-init="start()"
        class="space-y-6"
    >
        @if (session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-3xl bg-green-800 text-white shadow-xl">
            <div class="relative px-6 py-8 sm:px-10">
                <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-green-700"></div>
                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-green-200">Guest Borrowing Request</p>
                        <h1 class="mt-2 text-3xl font-extrabold">{{ $borrowing->borrowing_code }}</h1>
                        <p class="mt-2 text-sm text-green-100">Keep this page and QR code for LabTech verification, release, and return processing.</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-5 py-4 text-center backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-green-200">Live status</p>
                        <span
                            id="guestStatusBadge"
                            class="mt-2 inline-flex rounded-full px-3 py-1.5 text-sm font-bold {{ $statusClasses[$borrowing->status] ?? 'bg-gray-100 text-gray-700' }}"
                            x-text="statusLabel"
                        ></span>
                        <p class="mt-2 text-[11px] text-green-200">Updates automatically every 4 seconds</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Request Details</h2>
                            <p class="mt-1 text-sm text-gray-500">Submitted as a guest borrower.</p>
                        </div>
                        <span class="inline-flex w-fit rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700">{{ $borrowing->guestBorrower->role_label }}</span>
                    </div>

                    <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Borrower</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $borrowing->guestBorrower->full_name }}</dd>
                            <dd class="text-sm text-gray-500">{{ $borrowing->guestBorrower->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">University Information</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $borrowing->guestBorrower->id_number ?: 'No ID number required' }}</dd>
                            @if ($borrowing->guestBorrower->academic_details)
                                <dd class="text-sm text-gray-500">{{ $borrowing->guestBorrower->academic_details }}</dd>
                            @endif
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Your Room</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $borrowing->guestBorrower->room ?: 'Not specified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Borrow Date and Time</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $borrowing->borrow_at?->format('M d, Y h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Expected Return</dt>
                            <dd class="mt-1 text-sm text-gray-800">{{ $borrowing->expected_return_at?->format('M d, Y h:i A') }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Purpose</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $borrowing->purpose }}</dd>
                        </div>
                        @if ($borrowing->request_notes)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Additional Notes</dt>
                                <dd class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $borrowing->request_notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5 sm:px-8">
                        <h2 class="text-xl font-bold text-gray-900">Requested Equipment</h2>
                        <p class="mt-1 text-sm text-gray-500">Status changes are reflected automatically.</p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach ($borrowing->items as $line)
                            <div class="flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $line->itemUnit?->item?->display_name }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $line->itemUnit?->asset_number }} · {{ ucfirst(str_replace('_', ' ', $line->itemUnit?->condition)) }}</p>
                                </div>
                                <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-bold uppercase text-gray-700">
                                    {{ ucfirst(str_replace('_', ' ', $line->itemUnit?->availability_status)) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section x-show="rejectionReason || adminNotes" x-cloak class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="font-bold text-gray-900">LabTech Update</h2>
                    <p x-show="adminNotes" class="mt-3 whitespace-pre-line text-sm text-gray-700" x-text="adminNotes"></p>
                    <p x-show="rejectionReason" class="mt-3 whitespace-pre-line rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-text="rejectionReason"></p>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-gray-200 bg-white p-6 text-center shadow-sm">
                    <h2 class="font-bold text-gray-900">Guest Borrowing QR Code</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500">Present this QR code to the LabTech staff for request verification, release, and return.</p>
                    <div class="mx-auto mt-5 w-fit rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                            ->size(260)
                            ->margin(1)
                            ->errorCorrection('H')
                            ->generate($borrowing->borrowing_code) !!}
                    </div>
                    <p class="mt-4 rounded-xl bg-gray-50 px-4 py-3 font-mono text-sm font-bold text-gray-800">{{ $borrowing->borrowing_code }}</p>
                    <a href="{{ route('guest-borrowings.qr', $borrowing->public_token) }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-green-700 px-5 py-3 text-sm font-bold text-white hover:bg-green-800">Download QR Code</a>
                </section>

                <section class="rounded-3xl border border-blue-200 bg-blue-50 p-6 text-sm leading-6 text-blue-900">
                    <h2 class="font-bold">What happens next?</h2>
                    <ol class="mt-3 list-decimal space-y-2 pl-5">
                        <li>LabTech staff receives the request automatically.</li>
                        <li>The request is reviewed and approved or rejected.</li>
                        <li>Present the QR code when the equipment is released.</li>
                        <li>Return the equipment on or before the expected return time.</li>
                    </ol>
                </section>

                <a href="{{ route('guest-borrowings.create') }}" class="flex w-full items-center justify-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700 hover:bg-white">Create Another Guest Request</a>
            </aside>
        </div>
    </div>

    <script>
        function guestTracking(config) {
            return {
                status: config.initialStatus,
                statusLabel: config.initialStatus.charAt(0).toUpperCase() + config.initialStatus.slice(1),
                updatedAt: config.initialUpdatedAt,
                adminNotes: @js($borrowing->admin_notes),
                rejectionReason: @js($borrowing->rejection_reason),
                timer: null,
                requestRunning: false,

                start() {
                    if (this.timer) {
                        return;
                    }

                    this.refresh();

                    this.timer = window.setInterval(() => {
                        if (!document.hidden) {
                            this.refresh();
                        }
                    }, 5000);
                },

                async refresh() {
                    if (this.requestRunning) {
                        return;
                    }

                    this.requestRunning = true;

                    try {
                        const response = await fetch(config.statusUrl, {
                            headers: { 'Accept': 'application/json' },
                            cache: 'no-store',
                        });

                        if (response.status === 429 || !response.ok) {
                            return;
                        }

                        const data = await response.json();
                        const changed = data.updated_at && data.updated_at !== this.updatedAt;
                        this.status = data.status;
                        this.statusLabel = data.status_label;
                        this.updatedAt = data.updated_at;
                        this.adminNotes = data.admin_notes;
                        this.rejectionReason = data.rejection_reason;
                        this.updateBadge();
                        if (changed && ['released', 'returned'].includes(data.status)) {
                            window.setTimeout(() => window.location.reload(), 500);
                        }
                    } catch (error) {
                        console.debug('Live status update unavailable.', error);
                    } finally {
                        this.requestRunning = false;
                    }
                },

                destroy() {
                    if (this.timer) {
                        window.clearInterval(this.timer);
                        this.timer = null;
                    }
                },

                updateBadge() {
                    const badge = document.getElementById('guestStatusBadge');
                    if (!badge) return;
                    const classes = {
                        pending: 'bg-amber-100 text-amber-800',
                        approved: 'bg-blue-100 text-blue-800',
                        released: 'bg-violet-100 text-violet-800',
                        returned: 'bg-green-100 text-green-800',
                        rejected: 'bg-red-100 text-red-800',
                        cancelled: 'bg-gray-100 text-gray-700',
                        overdue: 'bg-red-100 text-red-800',
                    };
                    badge.className = 'mt-2 inline-flex rounded-full px-3 py-1.5 text-sm font-bold ' + (classes[this.status] || 'bg-gray-100 text-gray-700');
                },
            };
        }
    </script>
</x-public-layout>
