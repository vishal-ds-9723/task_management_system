<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$schedules = App\Models\ClientMonthlySchedule::where('client_id', 14)->get();
echo "Client 14 Schedules Count: " . $schedules->count() . "\n";
foreach($schedules as $s) {
    echo "ID: {$s->id}, Month: {$s->month}, Year: {$s->year}, Posts: {$s->posts}, Reels: {$s->reels}\n";
}
