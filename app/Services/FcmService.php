<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends push notifications via the Firebase Cloud Messaging HTTP v1 API,
 * authenticating as the service account at config('services.fcm.credentials_path')
 * (no firebase/kreait package required — just a signed JWT + OAuth2 token
 * exchange, both plain HTTP).
 */
class FcmService
{
    protected ?array $credentials = null;

    public function __construct()
    {
        $path = config('services.fcm.credentials_path');
        if ($path && file_exists($path)) {
            $this->credentials = json_decode(file_get_contents($path), true);
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->credentials['private_key']) && !empty($this->credentials['client_email']);
    }

    /**
     * Send a notification to a single device token. Returns true on success.
     * Silently no-ops (returns false) if FCM isn't configured yet, or logs
     * and returns false if the token is invalid/expired so callers can prune it.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $projectId = $this->credentials['project_id'];

        $response = Http::withToken($accessToken)->post(
            "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
            [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map('strval', $data),
                    'android' => [
                        'priority' => 'high',
                    ],
                ],
            ]
        );

        if (!$response->successful()) {
            Log::warning('FCM send failed', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        }

        return true;
    }

    /**
     * Send the same notification to every registered device of a user.
     * Prunes tokens FCM reports as unregistered/invalid.
     */
    public function sendToUser($user, string $title, string $body, array $data = []): void
    {
        if (!$this->isConfigured()) {
            return;
        }

        foreach ($user->deviceTokens as $deviceToken) {
            $ok = $this->sendToToken($deviceToken->token, $title, $body, $data);
            if (!$ok) {
                // Token likely stale (app reinstalled, uninstalled, etc.) — drop it.
                $deviceToken->delete();
            }
        }
    }

    protected function getAccessToken(): ?string
    {
        $cached = Cache::get('fcm_access_token');
        if ($cached) {
            return $cached;
        }

        $jwt = $this->buildAssertionJwt();
        if (!$jwt) return null;

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            Log::warning('FCM OAuth token exchange failed', ['body' => $response->body()]);
            return null;
        }

        $accessToken = $response->json('access_token');
        if ($accessToken) {
            // Only cache real, successful tokens — never cache a failure,
            // or every send stays broken until the cache entry expires.
            Cache::put('fcm_access_token', $accessToken, 3000);
        }

        return $accessToken;
    }

    protected function buildAssertionJwt(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($claims)),
        ];

        $signingInput = implode('.', $segments);

        $signature = '';
        $ok = openssl_sign($signingInput, $signature, $this->credentials['private_key'], OPENSSL_ALGO_SHA256);
        if (!$ok) {
            Log::error('FCM JWT signing failed');
            return null;
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
