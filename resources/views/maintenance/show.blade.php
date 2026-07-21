<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><div><h2 class="text-xl font-bold text-gray-900">{{ $maintenance->maintenance_code }}</h2><p class="text-sm text-gray-500">{{ $maintenance->issue_title }}</p></div><a href="{{ route('maintenance.index') }}" class="text-sm font-semibold text-green-700">Back to Maintenance</a></div>
    </x-slot>
    <div class="space-y-6">
        @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900">Equipment and Issue</h3>
                    <dl class="mt-5 grid gap-5 sm:grid-cols-2 text-sm">
                        <div><dt class="text-gray-500">Item</dt><dd class="mt-1 font-semibold text-gray-900">{{ $maintenance->itemUnit->item->name }}</dd></div>
                        <div><dt class="text-gray-500">Asset / Barcode</dt><dd class="mt-1 font-semibold text-gray-900">{{ $maintenance->itemUnit->asset_number ?: $maintenance->itemUnit->barcode_value }}</dd></div>
                        <div><dt class="text-gray-500">Condition before</dt><dd class="mt-1 font-semibold text-gray-900">{{ ucwords(str_replace('_',' ',$maintenance->condition_before)) }}</dd></div>
                        <div><dt class="text-gray-500">Priority</dt><dd class="mt-1 font-semibold text-gray-900">{{ ucfirst($maintenance->priority) }}</dd></div>
                    </dl>
                    <div class="mt-5"><p class="text-sm text-gray-500">Description</p><p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $maintenance->issue_description ?: 'No description provided.' }}</p></div>
                    @if($maintenance->borrowing)<div class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Created from returned borrowing <a class="font-bold underline" href="{{ route('borrowings.show',$maintenance->borrowing) }}">{{ $maintenance->borrowing->borrowing_code }}</a>.</div>@endif
                </div>
                @if($maintenance->diagnosis || $maintenance->repair_action || $maintenance->completion_notes)
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"><h3 class="text-lg font-bold text-gray-900">Repair Details</h3><div class="mt-4 space-y-4 text-sm"><div><p class="text-gray-500">Diagnosis</p><p class="mt-1 whitespace-pre-line text-gray-800">{{ $maintenance->diagnosis ?: '—' }}</p></div><div><p class="text-gray-500">Repair action</p><p class="mt-1 whitespace-pre-line text-gray-800">{{ $maintenance->repair_action ?: '—' }}</p></div><div><p class="text-gray-500">Completion notes</p><p class="mt-1 whitespace-pre-line text-gray-800">{{ $maintenance->completion_notes ?: '—' }}</p></div></div></div>
                @endif
            </div>
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"><h3 class="font-bold text-gray-900">Status</h3><p class="mt-3 inline-flex rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">{{ ucwords(str_replace('_',' ',$maintenance->status)) }}</p><dl class="mt-5 space-y-3 text-sm"><div><dt class="text-gray-500">Reported by</dt><dd class="font-medium text-gray-900">{{ $maintenance->reporter?->full_name ?? 'System' }}</dd></div><div><dt class="text-gray-500">Assigned to</dt><dd class="font-medium text-gray-900">{{ $maintenance->assignee?->full_name ?? 'Unassigned' }}</dd></div><div><dt class="text-gray-500">Started</dt><dd class="font-medium text-gray-900">{{ $maintenance->started_at?->format('M d, Y h:i A') ?? '—' }}</dd></div><div><dt class="text-gray-500">Completed</dt><dd class="font-medium text-gray-900">{{ $maintenance->completed_at?->format('M d, Y h:i A') ?? '—' }}</dd></div><div><dt class="text-gray-500">Repair cost</dt><dd class="font-medium text-gray-900">{{ $maintenance->repair_cost !== null ? '₱'.number_format($maintenance->repair_cost,2) : '—' }}</dd></div></dl></div>
                @can('manage maintenance')
                    @if(in_array($maintenance->status,['reported','assigned']))
                        <form method="POST" action="{{ route('maintenance.assign',$maintenance) }}" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">@csrf @method('PATCH')<label class="block text-sm font-semibold text-gray-700">Assign Technician</label><select name="assigned_to" required class="mt-2 w-full rounded-xl border-gray-300 text-sm">@foreach($technicians as $tech)<option value="{{ $tech->id }}" @selected($maintenance->assigned_to===$tech->id)>{{ $tech->full_name }}</option>@endforeach</select><button class="mt-3 w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Assign</button></form>
                        <form method="POST" action="{{ route('maintenance.start',$maintenance) }}" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">@csrf @method('PATCH')<label class="block text-sm font-semibold text-gray-700">Initial Diagnosis</label><textarea name="diagnosis" rows="3" class="mt-2 w-full rounded-xl border-gray-300 text-sm">{{ old('diagnosis',$maintenance->diagnosis) }}</textarea><button class="mt-3 w-full rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Start Repair</button></form>
                    @endif
                    @if(in_array($maintenance->status,['reported','assigned','in_progress']))
                        <form method="POST" action="{{ route('maintenance.complete',$maintenance) }}" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">@csrf @method('PATCH')<h3 class="font-bold text-gray-900">Complete Repair</h3><label class="mt-3 block text-sm font-semibold text-gray-700">Condition after</label><select name="condition_after" required class="mt-2 w-full rounded-xl border-gray-300 text-sm">@foreach(['excellent','good','fair','damaged','for_repair','unserviceable'] as $condition)<option value="{{ $condition }}">{{ ucwords(str_replace('_',' ',$condition)) }}</option>@endforeach</select><label class="mt-3 block text-sm font-semibold text-gray-700">Repair action</label><textarea name="repair_action" required rows="3" class="mt-2 w-full rounded-xl border-gray-300 text-sm"></textarea><label class="mt-3 block text-sm font-semibold text-gray-700">Cost</label><input name="repair_cost" type="number" min="0" step="0.01" class="mt-2 w-full rounded-xl border-gray-300 text-sm"><label class="mt-3 block text-sm font-semibold text-gray-700">Notes</label><textarea name="completion_notes" rows="2" class="mt-2 w-full rounded-xl border-gray-300 text-sm"></textarea><button class="mt-4 w-full rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white">Complete Maintenance</button></form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
