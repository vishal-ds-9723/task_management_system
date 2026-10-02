<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->getDashboardData(request());

        if (request()->ajax()) {
            $sections = view('admin.dashboard', $data)->renderSections();
            return response($sections['content'] ?? '');
        }

        return view('admin.dashboard', $data);
    }

    public function teamActivity()
    {
        $data = $this->service->teamActivity();

        return $data;
    }

    // Delegate other methods to service
    public function getChatbotTasksSummary()
    {
        return $this->service->getChatbotTasksSummary();
    }

    public function getChatbotClientsSummary()
    {
        return $this->service->getChatbotClientsSummary();
    }

    public function getChatbotReportsSummary()
    {
        return $this->service->getChatbotReportsSummary();
    }

    public function chatbotMessage()
    {
        return $this->service->chatbotMessage(request());
    }

    public function getChatbotTabData()
    {
        return $this->service->getChatbotTabData(request());
    }

    public function getChatbotWizardData()
    {
        return $this->service->getChatbotWizardData();
    }
}

