<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notifications:deadlines')->dailyAt('08:00');
Schedule::command('media:cleanup-old --months=12')->cron('0 3 1 1 *');
Schedule::command('notifications:cleanup-old --months=12')->cron('20 3 1 1 *');
