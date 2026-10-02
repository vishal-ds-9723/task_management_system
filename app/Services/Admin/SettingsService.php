<?php

namespace App\Services\Admin;

use App\Models\Setting;

class SettingsService
{
    public function toggleAwayMode()
    {
        $current = Setting::isStrategistAway();
        Setting::set('strategist_away_mode', $current ? '0' : '1');

        $status = $current ? 'OFF' : 'ON';

        return $status;
    }
}

