<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-gray-900">Borrowings</h1>
                    <span id="liveBorrowingIndicator" class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-green-700">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-green-600"></span>
                        Live
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Guest requests and staff transactions update automatically without manual refreshing.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('guest-borrowings.create') }}" target="_blank" rel="noopener" class="rounded-xl border border-green-700 px-5 py-2.5 text-sm font-semibold text-green-700 hover:bg-green-50">Open Guest Portal</a>
                @can('create borrowing requests')
                    <a href="{{ route('borrowings.create') }}" class="rounded-xl bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-800">Internal Request</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        <form method="GET" class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_220px_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Search code, guest, ID, or email..." class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
            <select name="status" class="rounded-xl border-gray-300 text-sm focus:border-green-600 focus:ring-green-600">
                <option value="">All statuses</option>
                @foreach (['pending', 'approved', 'rejected', 'released', 'returned', 'cancelled', 'overdue'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Filter</button>
        </form>

        <div id="borrowingLiveTable" data-signature="{{ $liveSignature }}" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            @include('borrowings.partials.table', ['borrowings' => $borrowings])
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('borrowingLiveTable');
            const indicator = document.getElementById('liveBorrowingIndicator');
            if (!container) return;

            const endpoint = new URL(@json(route('borrowings.live-table')), window.location.origin);
            const current = new URL(window.location.href);
            current.searchParams.forEach((value, key) => endpoint.searchParams.set(key, value));

            async function refreshBorrowings() {
                if (document.hidden) return;
                try {
                    const response = await fetch(endpoint.toString(), {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store',
                    });
                    if (!response.ok) throw new Error('Live request failed');
                    const data = await response.json();
                    if (data.signature !== container.dataset.signature) {
                        container.innerHTML = data.html;
                        container.dataset.signature = data.signature;
                    }
                    indicator?.classList.remove('bg-red-100', 'text-red-700');
                    indicator?.classList.add('bg-green-100', 'text-green-700');
                } catch (error) {
                    indicator?.classList.remove('bg-green-100', 'text-green-700');
                    indicator?.classList.add('bg-red-100', 'text-red-700');
                }
            }

            window.setInterval(refreshBorrowings, 4000);
        });
    </script>
</x-app-layout>
