<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginService
{
    private const LOGIN_PROGRESS_SESSION_KEY = 'login_progress_state';

    public function authenticate(array $credentials, Request $request)
    {
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            $redirect = $this->getRoleRedirect($user);

            return $redirect;
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function authenticateWithProgressStream(array $credentials, Request $request, callable $emit): void
    {
        $dailyQuote = $this->getDailyMotivationQuote();

        if (!Auth::attempt($credentials)) {
            $emit([
                'pct' => 100,
                'status' => 'Login failed. Please check your email/password.',
                'eta' => '0.0s',
                'quote' => $dailyQuote,
                'done' => true,
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);

            return;
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $redirectUrl = $this->getRoleRedirectUrl($user);

        $emit([
            'pct' => 100,
            'status' => 'Authenticated! Redirecting to your dashboard...',
            'eta' => '0.0s',
            'quote' => $dailyQuote,
            'done' => true,
            'success' => true,
            'redirect_url' => $redirectUrl,
            'server_ms' => 15,
        ]);
    }

    public function startLoginProgress(array $credentials, Request $request): array
    {
        $startedAt = microtime(true);
        $dailyQuote = $this->getDailyMotivationQuote();

        if (!Auth::attempt($credentials)) {
            $failedState = [
                'pct' => 100,
                'status' => 'Login failed. Please check your email/password.',
                'eta' => '0.0s',
                'quote' => $dailyQuote,
                'done' => true,
                'success' => false,
                'message' => 'Invalid credentials.',
            ];

            $this->setLoginProgressState($request, $failedState);

            return $failedState;
        }

        $request->session()->regenerate();
        $user = Auth::user();
        $redirectUrl = $this->getRoleRedirectUrl($user);

        $elapsedMs = (int) ((microtime(true) - $startedAt) * 1000);

        $finalState = [
            'pct' => 100,
            'status' => 'Welcome! Redirecting to dashboard...',
            'eta' => '0.0s',
            'quote' => $dailyQuote,
            'done' => true,
            'success' => true,
            'redirect_url' => $redirectUrl,
            'server_ms' => $elapsedMs,
        ];

        $this->setLoginProgressState($request, $finalState);

        return $finalState;
    }

    public function getLoginProgressState(Request $request): array
    {
        $state = $request->session()->get(self::LOGIN_PROGRESS_SESSION_KEY);

        if (!$state || !is_array($state)) {
            return [
                'pct' => 100,
                'status' => 'Ready',
                'eta' => '0.0s',
                'quote' => $this->getDailyMotivationQuote(),
                'done' => true,
                'success' => false,
            ];
        }

        if (!empty($state['done'])) {
            $request->session()->forget(self::LOGIN_PROGRESS_SESSION_KEY);
        }

        return $state;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function getRoleRedirect(User $user)
    {
        return redirect()->to($this->getRoleRedirectUrl($user));
    }

    public function getRoleRedirectUrl(User $user): string
    {
        return match($user->role) {
            'admin'      => route('admin.dashboard'),
            'strategist', 'manager', 'editor', 'content_writer' => route('strategist.dashboard'),
            'designer'   => route('designer.dashboard'),
            'client'     => route('client.dashboard'),
            'developer'  => route('developer.dashboard'),
            default      => route('strategist.dashboard'),
        };
    }

    private function setLoginProgressState(Request $request, array $state): void
    {
        $request->session()->put(self::LOGIN_PROGRESS_SESSION_KEY, $state);
        $request->session()->save();
    }

    private function getDailyMotivationQuote(): string
    {
        $dailyQuotes = [
            '"Laravel is The PHP Framework for Web Artisans."',
            '"Small daily progress is still progress."',
            '"Build with patience. Ship with confidence."',
            '"Great products come from consistent effort."',
            '"Stay focused. Keep improving."',
            '"Discipline today, results tomorrow."',
            '"One clean commit at a time."',
        ];

        $dayIndex = ((int) now()->dayOfYear) % count($dailyQuotes);

        return $dailyQuotes[$dayIndex];
    }
}

