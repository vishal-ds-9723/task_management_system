<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if ($request->user() && $request->user()->role === 'client') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Client portal is temporarily disabled.']);
        }

        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $subRole) {
                $trimmed = trim($subRole);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        if (!$request->user() || !$request->user()->hasAnyRole($allowedRoles)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
