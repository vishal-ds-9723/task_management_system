<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'designer')->first();
if (!$user) {
    echo "No designer user found\n";
    exit(1);
}

Auth::login($user);

$service = new App\Services\Designer\TaskService();
$request = Illuminate\Http\Request::create('/designer/tasks', 'GET');
$data = $service->getIndexData($request);

try {
    $rendered = view('designer.tasks', $data)->render();
    echo "SUCCESS: designer.tasks rendered successfully! Length: " . strlen($rendered) . " bytes\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
