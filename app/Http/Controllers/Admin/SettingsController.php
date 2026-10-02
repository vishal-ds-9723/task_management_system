<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SettingsService;

class SettingsController extends Controller
{
    protected $service;

    public function __construct(SettingsService $service)
    {
        $this->service = $service;
    }

    public function toggleAwayMode()
    {
        $status = $this->service->toggleAwayMode();

        return back()->with('success', "Strategist Away Mode turned {$status}");
    }
}

