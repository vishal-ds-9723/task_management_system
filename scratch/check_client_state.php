<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Task;
use App\Models\User;

$clientReviewTasks = Task::where('status', 'client_review')->get();
echo "Client review tasks count: " . $clientReviewTasks->count() . "\n";
foreach ($clientReviewTasks as $t) {
    echo " - ID: {$t->id}, Title: {$t->title}\n";
}

$clientUsers = User::where('role', 'client')->get();
echo "Client users count: " . $clientUsers->count() . "\n";
foreach ($clientUsers as $u) {
    echo " - ID: {$u->id}, Name: {$u->name}, Email: {$u->email}\n";
}
