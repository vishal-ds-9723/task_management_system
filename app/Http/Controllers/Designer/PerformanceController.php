<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Services\Designer\PerformanceService;

class PerformanceController extends Controller
{
    protected $service;

    public function __construct(PerformanceService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->getIndexData();

        return view('designer.performance', $data);
    }
}

