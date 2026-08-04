<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
            <tr>
                <th class="px-5 py-3">Code</th>
                <th class="px-5 py-3">Borrower</th>
                <th class="px-5 py-3">Schedule</th>
                <th class="px-5 py-3">Units</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($borrowings as $borrowing)
                @php
                    $badge = [
                        'pending' => 'bg-amber-100 text-amber-800',
                        'approved' => 'bg-blue-100 text-blue-800',
                        'released' => 'bg-violet-100 text-violet-800',
                        'returned' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'cancelled' => 'bg-gray-100 text-gray-700',
                        'overdue' => 'bg-red-100 text-red-800',
                    ][$borrowing->status] ?? 'bg-gray-100 text-gray-700';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-4">
                        <div class="font-semibold text-gray-900">{{ $borrowing->borrowing_code }}</div>
                        <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $borrowing->is_guest ? 'bg-cyan-100 text-cyan-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $borrowing->is_guest ? 'Guest' : 'Account' }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="font-medium text-gray-900">{{ $borrowing->borrower_name }}</div>
                        <div class="text-xs text-gray-500">{{ $borrowing->borrower_identifier ?: $borrowing->borrower_email }}</div>
                        <div class="mt-1 text-[11px] font-semibold text-green-700">{{ $borrowing->campus }}</div>
                    </td>
                    <td class="px-5 py-4 text-gray-600">
                        {{ $borrowing->borrow_at?->format('M d, Y h:i A') }}
                        <div class="text-xs text-gray-400">Return {{ $borrowing->expected_return_at?->format('M d, Y h:i A') }}</div>
                    </td>
                    <td class="px-5 py-4">{{ $borrowing->items->count() }}</td>
                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ ucfirst($borrowing->status) }}</span></td>
                    <td class="px-5 py-4 text-right"><a href="{{ route('borrowings.show', $borrowing) }}" class="font-semibold text-green-700 hover:text-green-900">View</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-gray-500">No borrowing records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="border-t border-gray-100 px-5 py-4">{{ $borrowings->links() }}</div>
