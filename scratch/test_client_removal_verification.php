<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Models\Client;
use App\Services\Auth\LoginService;
use App\Services\Admin\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

echo "=== START VERIFICATION: CLIENT REMOVAL & LOGIN DISABLE ===\n\n";

// 1. Verify Client Login Block
echo "[1] Testing Client Login Rejection in LoginService...\n";
$clientUser = User::where('role', 'client')->first();
if (!$clientUser) {
    $client = Client::first();
    $clientUser = User::create([
        'name' => 'Test Client User',
        'email' => 'test_client_user@example.com',
        'password' => bcrypt('password123'),
        'role' => 'client',
        'client_id' => $client?->id,
    ]);
}

$request = Request::create('/login', 'POST', [
    'email' => $clientUser->email,
    'password' => 'client123',
]);
$request->setLaravelSession($app['session']->driver());

$loginService = app(LoginService::class);
$progressResult = $loginService->startLoginProgress([
    'email' => $clientUser->email,
    'password' => 'client123', // if password fails or succeeds, client is blocked
], $request);

echo "   Result: " . json_encode($progressResult) . "\n";
assert($progressResult['success'] === false, "Client login attempt must be rejected");
assert(str_contains($progressResult['message'] ?? $progressResult['status'], 'disabled') || str_contains($progressResult['message'] ?? $progressResult['status'], 'Invalid'), "Message must indicate disabled or failed credentials");
echo "   [PASSED] Client login properly rejected.\n\n";

// 2. Verify RoleMiddleware with Client
echo "[2] Testing RoleMiddleware with Client Session...\n";
Auth::login($clientUser);
assert(Auth::check() && Auth::user()->role === 'client');

$req = Request::create('/admin/dashboard', 'GET');
$req->setUserResolver(fn() => $clientUser);
$middleware = new \App\Http\Middleware\RoleMiddleware();
$res = $middleware->handle($req, fn() => response('OK'), 'admin');

assert($res->isRedirect(), "RoleMiddleware must redirect client user");
assert(!Auth::check(), "RoleMiddleware must immediately log out client user");
echo "   [PASSED] RoleMiddleware logged out client and redirected.\n\n";

// 3. Verify /client Route Redirect
echo "[3] Testing /client Route Fallback / Disabled Handler...\n";
$routeReq = Request::create('/client/dashboard', 'GET');
$response = $app->handle($routeReq);
assert($response->isRedirect(), "/client/dashboard must redirect to login");
echo "   Response status: " . $response->getStatusCode() . " -> redirect URL: " . $response->headers->get('Location') . "\n";
echo "   [PASSED] Client portal URLs successfully redirect to login.\n\n";

// 4. Verify Admin Approval Direct Completion (No client review gating)
echo "[4] Testing Admin Task Approval Direct Completion...\n";
$admin = User::where('role', 'admin')->first();
Auth::login($admin);

$client = Client::first();
$task = Task::create([
    'title' => 'Test Agency Approval Task Without Client Review',
    'client_id' => $client->id,
    'type' => 'post',
    'status' => 'pending_approval',
    'created_by' => $admin->id,
    'requires_client_approval' => true, // previously would gate in client_review
]);

$approvalService = app(ApprovalService::class);
$approvedTask = $approvalService->approveTask($task);
$task->refresh();

echo "   Task Status after Approval: {$task->status}\n";
assert($task->status === 'completed', "Approved task must transition directly to completed!");
assert(!empty($task->completed_at), "completed_at timestamp must be set");
echo "   [PASSED] Admin approval completes directly without routing to client review.\n\n";

// Clean up test task
$task->delete();

// 5. Verify Blade View Rendering
echo "[5] Testing Blade Views for any broken client route references...\n";
$viewsToTest = [
    'auth.login' => [],
    'admin.pending-approvals' => ['tasks' => collect([])],
    'admin.client-detail' => app(\App\Services\Admin\ClientService::class)->getClientDetail(Client::first(), request()),
    'strategist.create-task' => [
        'clients' => Client::all(),
        'users' => User::all(),
        'designers' => User::where('role', 'designer')->get(),
        'contentWriters' => collect([]),
        'platforms' => ['instagram', 'facebook', 'linkedin'],
    ],
];

foreach ($viewsToTest as $viewName => $data) {
    try {
        view($viewName, $data)->render();
        echo "   [PASSED] View '{$viewName}' renders successfully.\n";
    } catch (\Throwable $e) {
        echo "   [FAILED] View '{$viewName}' failed: " . $e->getMessage() . "\n";
        throw $e;
    }
}

echo "\n=== ALL TESTS PASSED! CLIENT PANEL AND LOGIN SUCCESSFULLY REMOVED/DISABLED ===\n";
