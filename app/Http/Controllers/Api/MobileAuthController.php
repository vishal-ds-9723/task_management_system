<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MobileAuthController extends Controller
{
    /**
     * Mobile login endpoint.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Generate encrypted token with payload
        $payload = json_encode([
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => now()->timestamp,
        ]);
        $token = Crypt::encryptString($payload);

        $user->updateQuietly([
            'last_active_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar_color' => $user->avatar_color,
                'can_manage_social_metrics' => (bool)$user->can_manage_social_metrics,
                'client_id' => $user->client_id,
                'client' => $user->client ? [
                    'id' => $user->client->id,
                    'name' => $user->client->name,
                    'logo' => $user->client->logo,
                    'color' => $user->client->color,
                ] : null,
            ],
            'away_mode' => Setting::isStrategistAway(),
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar_color' => $user->avatar_color,
                'can_manage_social_metrics' => (bool)$user->can_manage_social_metrics,
                'client_id' => $user->client_id,
                'client' => $user->client ? [
                    'id' => $user->client->id,
                    'name' => $user->client->name,
                    'logo' => $user->client->logo,
                    'color' => $user->client->color,
                ] : null,
            ],
            'away_mode' => Setting::isStrategistAway(),
            'unread_notifications_count' => $user->unreadCustomNotifications()->count(),
        ]);
    }

    /**
     * Toggle away mode (Admin only).
     */
    public function toggleAwayMode(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $current = Setting::isStrategistAway();
        $new = !$current;
        Setting::set('strategist_away_mode', $new);

        return response()->json([
            'success' => true,
            'away_mode' => $new,
            'message' => $new ? 'Away mode enabled' : 'Away mode disabled',
        ]);
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
