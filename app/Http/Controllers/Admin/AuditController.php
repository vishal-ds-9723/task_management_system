<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditSearchRequest;
use App\Models\AuditLog;
use App\Models\Task;
use App\Services\Admin\AuditService;

class AuditController extends Controller
{
    protected $service;

    public function __construct(AuditService $service)
    {
        $this->service = $service;
    }

    public function index(AuditSearchRequest $request)
    {
        $data = $this->service->getFilteredLogs($request);

        return view('admin.audit-logs', $data);
    }

    public function taskHistory(Task $task)
    {
        $data = $this->service->getTaskHistory($task);

        return view('admin.task-audit-history', $data);
    }

    public function show(AuditLog $auditLog)
    {
        $response = $this->service->getAuditLogDetail($auditLog);

        return $response;
    }
}

