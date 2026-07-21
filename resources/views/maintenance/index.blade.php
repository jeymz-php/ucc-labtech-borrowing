<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Maintenance Management</h2>
                <p class="text-sm text-gray-500">Track damaged equipment, repairs, costs, and return-to-service status.</p>
            </div>
            @can('create maintenance')
                <a href="{{ route('maintenance.create') }}" class="rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">New Maintenance Record</a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Open records', $counts['open']],
                ['Critical', $counts['critical']],
                ['Completed', $counts['completed']],
                ['Total repair cost', '₱'.number_format($counts['cost'], 2)],
            ] as [$label,$value])
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <form method="GET" class="grid gap-3 md:grid-cols-4">
                <input name="search" value="{{ request('search') }}" placeholder="Code, item, asset or issue" class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                <select name="status" class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                    <option value="">All statuses</option>
                    @foreach(['reported','assigned','in_progress','completed','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>
                    @endforeach
                </select>
                <select name="priority" class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                    <option value="">All priorities</option>
                    @foreach(['low','medium','high','critical'] as $priority)
                        <option value="{{ $priority }}" @selected(request('priority')===$priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">Filter</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr><th class="px-5 py-3">Record</th><th class="px-5 py-3">Equipment</th><th class="px-5 py-3">Issue</th><th class="px-5 py-3">Priority</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Assigned</th><th class="px-5 py-3"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($records as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-semibold text-green-700">{{ $record->maintenance_code }}</td>
                                <td class="px-5 py-4"><div class="font-medium text-gray-900">{{ $record->itemUnit->item->name }}</div><div class="text-xs text-gray-500">{{ $record->itemUnit->asset_number ?: $record->itemUnit->barcode_value }}</div></td>
                                <td class="px-5 py-4 text-gray-700">{{ $record->issue_title }}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($record->priority) }}</span></td>
                                <td class="px-5 py-4"><span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">{{ ucwords(str_replace('_',' ',$record->status)) }}</span></td>
                                <td class="px-5 py-4 text-gray-600">{{ $record->assignee?->full_name ?? 'Unassigned' }}</td>
                                <td class="px-5 py-4 text-right"><a href="{{ route('maintenance.show',$record) }}" class="font-semibold text-green-700 hover:underline">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500">No maintenance records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-5 py-4">{{ $records->links() }}</div>
        </div>
    </div>
</x-app-layout>
