<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $query = $request->user()->notifications()->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(25)->withQueryString();
        $unreadCount   = $request->user()->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->where('id', $id)->delete();

        return back()->with('success', 'Notification dismissed.');
    }
}
