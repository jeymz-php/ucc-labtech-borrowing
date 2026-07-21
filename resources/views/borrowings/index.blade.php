<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><h1 class="text-xl font-bold text-gray-900">Borrowings</h1><p class="mt-1 text-sm text-gray-500">Track requests, approvals, releases, and returns.</p></div>
            @can('create borrowing requests')<a href="{{ route('borrowings.create') }}" class="rounded-xl bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-800">New Request</a>@endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
        <form method="GET" class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_220px_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Search code or borrower..." class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
            <select name="status" class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                <option value="">All statuses</option>
                @foreach(['pending','approved','rejected','released','returned','cancelled','overdue'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach
            </select>
            <button class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Filter</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"><tr><th class="px-5 py-3">Code</th><th class="px-5 py-3">Borrower</th><th class="px-5 py-3">Schedule</th><th class="px-5 py-3">Units</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($borrowings as $borrowing)
                    @php($badge=['pending'=>'bg-amber-100 text-amber-800','approved'=>'bg-blue-100 text-blue-800','released'=>'bg-violet-100 text-violet-800','returned'=>'bg-green-100 text-green-800','rejected'=>'bg-red-100 text-red-800','cancelled'=>'bg-gray-100 text-gray-700','overdue'=>'bg-red-100 text-red-800'][$borrowing->status] ?? 'bg-gray-100 text-gray-700')
                    <tr class="hover:bg-gray-50"><td class="px-5 py-4 font-semibold text-gray-900">{{ $borrowing->borrowing_code }}</td><td class="px-5 py-4"><div class="font-medium text-gray-900">{{ $borrowing->user?->full_name ?? 'Deleted user' }}</div><div class="text-xs text-gray-500">{{ $borrowing->user?->id_number }}</div></td><td class="px-5 py-4 text-gray-600">{{ $borrowing->borrow_at?->format('M d, Y h:i A') }}<div class="text-xs text-gray-400">Return {{ $borrowing->expected_return_at?->format('M d, Y h:i A') }}</div></td><td class="px-5 py-4">{{ $borrowing->items->count() }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ ucfirst($borrowing->status) }}</span></td><td class="px-5 py-4 text-right"><a href="{{ route('borrowings.show',$borrowing) }}" class="font-semibold text-green-700 hover:text-green-900">View</a></td></tr>
                @empty<tr><td colspan="6" class="px-5 py-12 text-center text-gray-500">No borrowing records found.</td></tr>@endforelse
                </tbody>
            </table></div>
            <div class="border-t border-gray-100 px-5 py-4">{{ $borrowings->links() }}</div>
        </div>
    </div>
</x-app-layout>