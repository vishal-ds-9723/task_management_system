<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.force_https') || str_starts_with((string) config('app.url'), 'https://') || request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https') {
            if (config('app.url')) {
                URL::forceRootUrl(rtrim((string) config('app.url'), '/'));
            }
            URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            if (!Auth::check()) {
                $view->with('createTaskClients', collect());
                $view->with('createTaskMembers', collect());
                return;
            }

            $createTaskClients = Client::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'logo', 'emoji']);

            $createTaskMembers = User::whereNotIn('role', ['admin', 'client'])
                ->orderBy('name')
                ->get(['id', 'name', 'role']);

            $view->with('createTaskClients', $createTaskClients);
            $view->with('createTaskMembers', $createTaskMembers);
        });
    }
}
