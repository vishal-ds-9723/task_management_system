<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Resetting passwords for all users with role 'client' to 'password'...\n";

$clients = User::where('role', 'client')->get();

foreach ($clients as $client) {
    $client->password = Hash::make('password');
    $client->save();
    echo "Updated: {$client->email}\n";
}

echo "Done! " . $clients->count() . " client passwords reset.\n";
