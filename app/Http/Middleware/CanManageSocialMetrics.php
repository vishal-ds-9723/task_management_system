<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanManageSocialMetrics
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->isStrategist() || $user->canManageSocialMetrics()) {
            return $next($request);
        }

        abort(403, 'You are not allowed to manage social metrics.');
    }
}
