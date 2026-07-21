<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->findOrFail($notification);
        $record->markAsRead();
        return redirect($record->data['url'] ?? route('notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }
}
