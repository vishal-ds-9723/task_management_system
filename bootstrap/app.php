<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'can.manage.social.metrics' => \App\Http\Middleware\CanManageSocialMetrics::class,
            'no-cache' => \App\Http\Middleware\PreventBackHistory::class,
            'api.auth' => \App\Http\Middleware\ApiAuthMiddleware::class,
        ]);
        $middleware->append(\App\Http\Middleware\TrackUserActivity::class);
        $middleware->append(\App\Http\Middleware\PreventBackHistory::class);
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            '/logout',
            'logout',
            'api/*',
            '/api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
