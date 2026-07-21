<x-app-layout>
<x-slot name="header"><div class="flex items-center justify-between"><div><h1 class="text-xl font-bold text-gray-900">Notifications</h1><p class="mt-1 text-sm text-gray-500">Borrowing updates and reminders.</p></div><form method="POST" action="{{ route('notifications.read-all') }}">@csrf @method('PATCH')<button class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Mark all read</button></form></div></x-slot>
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
@forelse($notifications as $notification)
<a href="{{ route('notifications.read',$notification->id) }}" class="block border-b border-gray-100 px-6 py-5 transition hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-green-50/60' }}">
<div class="flex items-start justify-between gap-4"><div><div class="font-semibold text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</div><p class="mt-1 text-sm text-gray-600">{{ $notification->data['message'] ?? '' }}</p><p class="mt-2 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p></div>@unless($notification->read_at)<span class="mt-1 h-2.5 w-2.5 rounded-full bg-green-600"></span>@endunless</div>
</a>
@empty<div class="px-6 py-16 text-center text-sm text-gray-500">No notifications yet.</div>@endforelse
<div class="px-6 py-4">{{ $notifications->links() }}</div></div>
</x-app-layout>