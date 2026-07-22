<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ $borrowing->borrowing_code }}
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Borrowing transaction details and actions.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('borrowings.receipt', $borrowing) }}"
                    target="_blank"
                    class="text-sm font-semibold text-green-700 hover:text-green-800"
                >
                    Print Receipt
                </a>

                <a
                    href="{{ route('borrowings.index') }}"
                    class="text-sm font-semibold text-green-700 hover:text-green-800"
                >
                    Back to Borrowings
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
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

        <div class="grid gap-6 xl:grid-cols-3">
            {{-- Request details --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm xl:col-span-2">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="font-bold text-gray-900">
                            Request Details
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Submitted by {{ $borrowing->user?->full_name }}
                        </p>
                    </div>

                    <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-bold uppercase text-gray-700">
                        {{ $borrowing->status }}
                    </span>
                </div>

                <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-400">
                            Purpose
                        </dt>

                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $borrowing->purpose }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-400">
                            Borrower
                        </dt>

                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $borrowing->user?->full_name }}
                            ·
                            {{ $borrowing->user?->id_number }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-400">
                            Borrow Schedule
                        </dt>

                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $borrowing->borrow_at?->format('M d, Y h:i A') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-400">
                            Expected Return
                        </dt>

                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $borrowing->expected_return_at?->format('M d, Y h:i A') }}
                        </dd>
                    </div>

                    @if ($borrowing->request_notes)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase text-gray-400">
                                Request Notes
                            </dt>

                            <dd class="mt-1 whitespace-pre-line text-sm text-gray-800">
                                {{ $borrowing->request_notes }}
                            </dd>
                        </div>
                    @endif

                    @if ($borrowing->admin_notes)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase text-gray-400">
                                Administrative Notes
                            </dt>

                            <dd class="mt-1 whitespace-pre-line text-sm text-gray-800">
                                {{ $borrowing->admin_notes }}
                            </dd>
                        </div>
                    @endif

                    @if ($borrowing->rejection_reason)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase text-red-500">
                                Rejection Reason
                            </dt>

                            <dd class="mt-1 whitespace-pre-line text-sm text-red-700">
                                {{ $borrowing->rejection_reason }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>

            <div class="space-y-6">
                {{-- Borrowing QR code --}}
                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <h2 class="font-bold text-gray-900">
                            Borrowing QR Code
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Present this QR code to the Laboratory Technician
                            during equipment release and return.
                        </p>
                    </div>

                    <div class="flex flex-col items-center px-6 py-6 text-center">
                        <div class="max-w-full rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                                ->size(220)
                                ->margin(1)
                                ->errorCorrection('H')
                                ->generate($borrowing->borrowing_code) !!}
                        </div>

                        <p class="mt-5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                            Borrowing Code
                        </p>

                        <p class="mt-1 break-all text-lg font-bold tracking-wide text-gray-900">
                            {{ $borrowing->borrowing_code }}
                        </p>

                        <p class="mt-2 text-sm font-medium text-gray-700">
                            {{ $borrowing->user?->full_name }}
                        </p>

                        @if ($borrowing->user?->id_number)
                            <p class="mt-1 text-xs text-gray-500">
                                ID Number: {{ $borrowing->user->id_number }}
                            </p>
                        @endif

                        <div class="mt-5 w-full rounded-xl bg-blue-50 px-4 py-3 text-left text-sm text-blue-800">
                            <p class="font-semibold">
                                Scanner instructions
                            </p>

                            <p class="mt-1">
                                Scan this QR code first, then scan every assigned
                                equipment barcode.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Actions --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="font-bold text-gray-900">
                        Actions
                    </h2>

                    <div class="mt-4 space-y-3">
                        @can('approve borrowings')
                            @if ($borrowing->status === 'pending')
                                <form
                                    method="POST"
                                    action="{{ route('borrowings.approve', $borrowing) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                                    >
                                        Approve Request
                                    </button>
                                </form>
                            @endif
                        @endcan

                        @can('reject borrowings')
                            @if (in_array($borrowing->status, ['pending', 'approved'], true))
                                <form
                                    method="POST"
                                    action="{{ route('borrowings.reject', $borrowing) }}"
                                    class="space-y-2"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <textarea
                                        name="rejection_reason"
                                        required
                                        maxlength="1500"
                                        rows="3"
                                        placeholder="Reason for rejection"
                                        class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"
                                    >{{ old('rejection_reason') }}</textarea>

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700"
                                    >
                                        Reject Request
                                    </button>
                                </form>
                            @endif
                        @endcan

                        @can('release borrowings')
                            @if ($borrowing->status === 'approved')
                                <form
                                    method="POST"
                                    action="{{ route('borrowings.release', $borrowing) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700"
                                    >
                                        Release Equipment
                                    </button>
                                </form>
                            @endif
                        @endcan

                        @if ($borrowing->canBeCancelledBy(auth()->user()))
                            <form
                                method="POST"
                                action="{{ route('borrowings.cancel', $borrowing) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                                >
                                    Cancel Request
                                </button>
                            </form>
                        @endif

                        @if (! in_array($borrowing->status, ['pending', 'approved'], true))
                            <p class="text-sm text-gray-500">
                                No request actions are currently available.
                            </p>
                        @endif

                        @can('extend borrowing due dates')
                            @if (in_array($borrowing->status, ['approved', 'released', 'overdue'], true))
                                <form
                                    method="POST"
                                    action="{{ route('borrowings.extend', $borrowing) }}"
                                    class="space-y-2 border-t border-gray-100 pt-4"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <label
                                        for="expected_return_at"
                                        class="text-xs font-semibold uppercase text-gray-500"
                                    >
                                        Extend Due Date
                                    </label>

                                    <input
                                        id="expected_return_at"
                                        type="datetime-local"
                                        name="expected_return_at"
                                        value="{{ old('expected_return_at') }}"
                                        required
                                        class="w-full rounded-xl border-gray-300 text-sm focus:border-amber-500 focus:ring-amber-500"
                                    >

                                    <textarea
                                        name="extension_reason"
                                        required
                                        maxlength="1500"
                                        rows="3"
                                        placeholder="Reason for extension"
                                        class="w-full rounded-xl border-gray-300 text-sm focus:border-amber-500 focus:ring-amber-500"
                                    >{{ old('extension_reason') }}</textarea>

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600"
                                    >
                                        Extend Due Date
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </section>
            </div>
        </div>

        {{-- Equipment units --}}
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-bold text-gray-900">
                            Equipment Units
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $borrowing->items->count() }}
                            {{ Str::plural('unit', $borrowing->items->count()) }}
                            assigned to this transaction.
                        </p>
                    </div>

                    <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                        {{ ucfirst($borrowing->status) }}
                    </span>
                </div>
            </div>

            @can('receive returns')
                @if (in_array($borrowing->status, ['released', 'overdue'], true))
                    <form
                        method="POST"
                        action="{{ route('borrowings.receive', $borrowing) }}"
                    >
                        @csrf
                        @method('PATCH')
                @endif
            @endcan

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3">
                                Equipment
                            </th>

                            <th class="px-5 py-3">
                                Asset
                            </th>

                            <th class="px-5 py-3">
                                Barcode
                            </th>

                            <th class="px-5 py-3">
                                Condition Out
                            </th>

                            <th class="px-5 py-3">
                                Condition In
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($borrowing->items as $line)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-medium text-gray-900">
                                        {{ $line->itemUnit->item->display_name }}
                                    </p>

                                    @if ($line->itemUnit->item->category)
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $line->itemUnit->item->category->name }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-gray-600">
                                    {{ $line->itemUnit->asset_number ?: '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="break-all rounded-lg bg-gray-100 px-2 py-1 font-mono text-xs text-gray-600">
                                        {{ $line->itemUnit->barcode_value ?: '—' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-gray-600">
                                    {{ $line->condition_out
                                        ? ucfirst(str_replace('_', ' ', $line->condition_out))
                                        : '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    @can('receive returns')
                                        @if (in_array($borrowing->status, ['released', 'overdue'], true))
                                            <div class="min-w-48 space-y-2">
                                                <select
                                                    name="conditions[{{ $line->id }}]"
                                                    required
                                                    class="w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                                                >
                                                    @foreach ([
                                                        'excellent',
                                                        'good',
                                                        'fair',
                                                        'damaged',
                                                        'for_repair',
                                                        'unserviceable',
                                                    ] as $condition)
                                                        <option
                                                            value="{{ $condition }}"
                                                            @selected(
                                                                old(
                                                                    'conditions.'.$line->id,
                                                                    $line->itemUnit->condition
                                                                ) === $condition
                                                            )
                                                        >
                                                            {{ ucfirst(str_replace('_', ' ', $condition)) }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <input
                                                    type="text"
                                                    name="remarks[{{ $line->id }}]"
                                                    value="{{ old('remarks.'.$line->id) }}"
                                                    maxlength="1500"
                                                    placeholder="Return remarks"
                                                    class="block w-full rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600"
                                                >
                                            </div>
                                        @else
                                            <div class="text-gray-600">
                                                {{ $line->condition_in
                                                    ? ucfirst(str_replace('_', ' ', $line->condition_in))
                                                    : '—' }}
                                            </div>

                                            @if ($line->remarks_in)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $line->remarks_in }}
                                                </p>
                                            @endif
                                        @endif
                                    @else
                                        <div class="text-gray-600">
                                            {{ $line->condition_in
                                                ? ucfirst(str_replace('_', ' ', $line->condition_in))
                                                : '—' }}
                                        </div>

                                        @if ($line->remarks_in)
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $line->remarks_in }}
                                            </p>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="px-5 py-10 text-center text-sm text-gray-500"
                                >
                                    No equipment units are assigned to this borrowing.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @can('receive returns')
                @if (in_array($borrowing->status, ['released', 'overdue'], true))
                        <div class="border-t border-gray-100 px-6 py-4 text-right">
                            <button
                                type="submit"
                                class="rounded-xl bg-green-700 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800"
                            >
                                Complete Return
                            </button>
                        </div>
                    </form>
                @endif
            @endcan
        </section>

        {{-- Transaction timeline --}}
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="font-bold text-gray-900">
                Transaction Timeline
            </h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-400">
                        Requested
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $borrowing->created_at->format('M d, Y h:i A') }}
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-400">
                        Approved
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $borrowing->approved_at?->format('M d, Y h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-400">
                        Released
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $borrowing->released_at?->format('M d, Y h:i A') ?? '—' }}
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase text-gray-400">
                        Returned
                    </div>

                    <div class="mt-1 text-sm font-semibold text-gray-900">
                        {{ $borrowing->returned_at?->format('M d, Y h:i A') ?? '—' }}
                    </div>
                </div>
            </div>

            @if ($borrowing->extended_at)
                <div class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                    <strong>Due date extended:</strong>

                    {{ $borrowing->extension_reason }}

                    ·

                    {{ $borrowing->extended_at->format('M d, Y h:i A') }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>