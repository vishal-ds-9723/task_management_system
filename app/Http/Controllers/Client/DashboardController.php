<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\DashboardService;
use App\Models\Task;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->getIndexData();

        return view('client.dashboard', $data);
    }

    public function getStatDetails($type)
    {
        $data = $this->service->getStatDetails($type);

        return response()->json($data);
    }

    public function showTask(Task $task)
    {
        $data = $this->service->getTaskData($task);

        return view('client.tasks.show', $data);
    }

    public function contentSchedule()
    {
        $data = $this->service->getContentScheduleData();
        return view('client.content-schedule', $data);
    }

    public function socialAnalysis()
    {
        $data = $this->service->getIndexData();
        return view('client.social-analysis', $data);
    }
}

