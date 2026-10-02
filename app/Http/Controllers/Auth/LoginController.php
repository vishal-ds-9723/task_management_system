<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected $service;

    public function __construct(LoginService $service)
    {
        $this->service = $service;
    }

    public function showLogin()
    {
        if (auth()->check()) {
            return redirect($this->service->getRoleRedirectUrl(auth()->user()));
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        return $this->service->authenticate($request->only('email', 'password'), $request);
    }

    public function loginStream(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        return response()->stream(function () use ($credentials, $request) {
            $emit = static function (array $payload): void {
                echo json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n";

                if (ob_get_level() > 0) {
                    @ob_flush();
                }

                flush();
            };

            $this->service->authenticateWithProgressStream($credentials, $request, $emit);
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function startLoginProgress(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $result = $this->service->startLoginProgress($credentials, $request);

        $statusCode = !empty($result['success']) ? 200 : 422;

        return response()->json($result, $statusCode);
    }

    public function loginProgressState(Request $request): JsonResponse
    {
        return response()->json($this->service->getLoginProgressState($request));
    }

    public function logout(Request $request)
    {
        return $this->service->logout($request);
    }
}

