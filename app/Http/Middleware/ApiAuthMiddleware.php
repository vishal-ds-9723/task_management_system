<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming API request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Missing authorization bearer token.',
            ], 401);
        }

        try {
            $decrypted = Crypt::decryptString($token);
            $payload = json_decode($decrypted, true);

            if (!isset($payload['user_id']) || !isset($payload['created_at'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authentication token structure.',
                ], 401);
            }

            $user = User::find($payload['user_id']);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User account not found.',
                ], 401);
            }

            // Set user on Auth and Request
            Auth::setUser($user);
            $request->setUserResolver(fn() => $user);

            // Update user activity
            $user->updateQuietly([
                'last_active_at' => now(),
            ]);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired authorization token.',
            ], 401);
        }
    }
}
