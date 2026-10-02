<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Return unread count + latest notifications for polling.
     */
    public function poll(Request $request)
    {
        $user = $request->user();
        $unreadCount = $user->visibleUnreadCustomNotifications()->count();
        $notifications = $user->visibleCustomNotifications()->take(30)->get()->map(fn ($n) => [
            'id'       => $n->id,
            'icon'     => $n->icon,
            'title'    => $n->title,
            'subtitle' => $n->subtitle,
            'link'     => $n->link,
            'read'     => (bool) $n->read_at,
            'time'     => $n->created_at->diffForHumans(),
        ]);

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()
            ->unreadCustomNotifications()
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function clearAll(Request $request)
    {
        $request->user()
            ->customNotifications()
            ->delete();

        return response()->json(['ok' => true]);
    }
}
