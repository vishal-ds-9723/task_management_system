<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\Notification;
use Illuminate\Http\Request;

class MobileNotificationController extends Controller
{
    /**
     * Register (or refresh) this device's FCM token against the logged-in user.
     */
    public function registerDevice(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|string',
        ]);

        $user = $request->user();

        // A token can only ever belong to one user at a time — if it was
        // previously registered under a different account (shared/reset
        // device), move it rather than erroring on the unique constraint.
        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            ['user_id' => $user->id, 'platform' => $request->platform ?: 'android']
        );

        return response()->json(['success' => true]);
    }

    /**
     * Unregister a device token (called on logout) so it stops receiving pushes.
     */
    public function unregisterDevice(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        DeviceToken::where('token', $request->token)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['success' => true]);
    }
    /**
     * Get user notifications.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->visibleCustomNotifications()
            ->take(40)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'subtitle' => $n->subtitle,
                    'icon' => $n->icon ?: '🔔',
                    'link' => $n->link,
                    'task_id' => $n->task_id,
                    'is_read' => !empty($n->read_at),
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadCustomNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadCustomNotifications()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
            'message' => 'All notifications marked as read',
        ]);
    }
}
