<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $borrowing->borrowing_code }}</h1>
                <p class="mt-1 text-sm text-gray-500">Borrowing transaction details and actions.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('borrowings.receipt', $borrowing) }}" target="_blank" class="text-sm font-semibold text-green-700">
                    Print Receipt
                </a>
                <a href="{{ route('borrowings.index') }}" class="text-sm font-semibold text-green-700">
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
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm xl:col-span-2">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="font-bold text-gray-900">Request Details</h2>
                        <p class="mt-1 text-sm text-gray-500">Submitted by {{ $borrowing->user?->full_name }}</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold uppercase text-gray-700">
                        {{ $borrowing->status }}
                    </span>
                </div>

                <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-400">Purpose</dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $borrowing->purpose }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-400">Borrower</dt>
                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $borrowing->user?->full_name }} · {{ $borrowing->user?->id_number }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-400">Borrow schedule</dt>
                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $borrowing->borrow_at?->format('M d, Y h:i A') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-400">Expected return</dt>
                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $borrowing->expected_return_at?->format('M d, Y h:i A') }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="font-bold text-gray-900">Actions</h2>

                <div class="mt-4 space-y-3">
                    @can('approve borrowings')
                        @if ($borrowing->status === 'pending')
                            <form method="POST" action="{{ route('borrowings.approve', $borrowing) }}">
                                @csrf
                                @method('PATCH')
                                <button class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">
                                    Approve Request
                                </button>
                            </form>
                        @endif
                    @endcan

                    @can('reject borrowings')
                        @if (in_array($borrowing->status, ['pending', 'approved']))
                            <form method="POST" action="{{ route('borrowings.reject', $borrowing) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <textarea name="rejection_reason" required placeholder="Reason for rejection" class="w-full rounded-xl border-gray-300 text-sm"></textarea>
                                <button class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white">
                                    Reject Request
                                </button>
                            </form>
                        @endif
                    @endcan

                    @can('release borrowings')
                        @if ($borrowing->status === 'approved')
                            <form method="POST" action="{{ route('borrowings.release', $borrowing) }}">
                                @csrf
                                @method('PATCH')
                                <button class="w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white">
                                    Release Equipment
                                </button>
                            </form>
                        @endif
                    @endcan

                    @if ($borrowing->canBeCancelledBy(auth()->user()))
                        <form method="POST" action="{{ route('borrowings.cancel', $borrowing) }}">
                            @csrf
                            @method('PATCH')
                            <button class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">
                                Cancel Request
                            </button>
                        </form>
                    @endif

                    @if (! in_array($borrowing->status, ['pending', 'approved']))
                        <p class="text-sm text-gray-500">No request actions are currently available.</p>
                    @endif

                    @can('extend borrowing due dates')
                        @if (in_array($borrowing->status, ['approved', 'released', 'overdue']))
                            <form method="POST" action="{{ route('borrowings.extend', $borrowing) }}" class="space-y-2 border-t border-gray-100 pt-3">
                                @csrf
                                @method('PATCH')
                                <label class="text-xs font-semibold uppercase text-gray-500">Extend due date</label>
                                <input type="datetime-local" name="expected_return_at" required class="w-full rounded-xl border-gray-300 text-sm">
                                <textarea name="extension_reason" required placeholder="Reason for extension" class="w-full rounded-xl border-gray-300 text-sm"></textarea>
                                <button class="w-full rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white">
                                    Extend Due Date
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5">
                <h2 class="font-bold text-gray-900">Equipment Units</h2>
            </div>

            @can('receive returns')
                @if (in_array($borrowing->status, ['released', 'overdue']))
                    <form method="POST" action="{{ route('borrowings.receive', $borrowing) }}">
                        @csrf
                        @method('PATCH')
                @endif
            @endcan

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Equipment</th>
                            <th class="px-5 py-3">Asset</th>
                            <th class="px-5 py-3">Condition Out</th>
                            <th class="px-5 py-3">Condition In</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($borrowing->items as $line)
                            <tr>
                                <td class="px-5 py-4 font-medium text-gray-900">{{ $line->itemUnit->item->display_name }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $line->itemUnit->asset_number }}</td>
                                <td class="px-5 py-4 text-gray-600">
                                    {{ $line->condition_out ? ucfirst(str_replace('_', ' ', $line->condition_out)) : '—' }}
                                </td>
                                <td class="px-5 py-4">
                                    @can('receive returns')
                                        @if (in_array($borrowing->status, ['released', 'overdue']))
                                            <select name="conditions[{{ $line->id }}]" class="rounded-xl border-gray-300 text-sm">
                                                @foreach (['excellent', 'good', 'fair', 'damaged', 'for_repair', 'unserviceable'] as $condition)
                                                    <option value="{{ $condition }}" @selected($line->itemUnit->condition === $condition)>
                                                        {{ ucfirst(str_replace('_', ' ', $condition)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input name="remarks[{{ $line->id }}]" placeholder="Return remarks" class="mt-2 block rounded-xl border-gray-300 text-sm">
                                        @else
                                            {{ $line->condition_in ? ucfirst(str_replace('_', ' ', $line->condition_in)) : '—' }}
                                        @endif
                                    @else
                                        {{ $line->condition_in ? ucfirst(str_replace('_', ' ', $line->condition_in)) : '—' }}
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @can('receive returns')
                @if (in_array($borrowing->status, ['released', 'overdue']))
                        <div class="border-t border-gray-100 px-6 py-4 text-right">
                            <button class="rounded-xl bg-green-700 px-6 py-2.5 text-sm font-semibold text-white">
                                Complete Return
                            </button>
                        </div>
                    </form>
                @endif
            @endcan
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="font-bold text-gray-900">Transaction Timeline</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <div class="text-xs uppercase text-gray-400">Requested</div>
                    <div class="mt-1 text-sm font-semibold">{{ $borrowing->created_at->format('M d, Y h:i A') }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-400">Approved</div>
                    <div class="mt-1 text-sm font-semibold">{{ $borrowing->approved_at?->format('M d, Y h:i A') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-400">Released</div>
                    <div class="mt-1 text-sm font-semibold">{{ $borrowing->released_at?->format('M d, Y h:i A') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-400">Returned</div>
                    <div class="mt-1 text-sm font-semibold">{{ $borrowing->returned_at?->format('M d, Y h:i A') ?? '—' }}</div>
                </div>
            </div>

            @if ($borrowing->extended_at)
                <div class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                    <strong>Due date extended:</strong>
                    {{ $borrowing->extension_reason }} · {{ $borrowing->extended_at->format('M d, Y h:i A') }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
