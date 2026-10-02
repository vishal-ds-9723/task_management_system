<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrackUserActivity
{
    // Minimum seconds between DB writes per user (reduces write load on busy servers)
    private const THROTTLE_SECONDS = 60;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $userId   = Auth::id();
            $cacheKey = "user_activity_{$userId}";

            // Only write to DB if the cache key has expired (i.e. > THROTTLE_SECONDS since last write)
            if (!Cache::has($cacheKey)) {
                DB::table('users')
                    ->where('id', $userId)
                    ->update([
                        'last_active_at' => now(),
                        'current_url'    => $request->path(),
                    ]);

                Cache::put($cacheKey, true, self::THROTTLE_SECONDS);
            }
        }

        return $next($request);
    }
}
