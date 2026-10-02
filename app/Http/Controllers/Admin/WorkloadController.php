<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\WorkloadService;

class WorkloadController extends Controller
{
    protected $service;

    public function __construct(WorkloadService $service)
    {
        $this->service = $service;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $data = $this->service->getIndexData($request);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.workload', $data)->fragment('workload_content'),
                'totalMembers' => $data['totalMembers'],
                'avgEfficiency' => $data['avgEfficiency'],
                'overloadedCount' => $data['overloadedCount']
            ]);
        }

        return view('admin.workload', $data);
    }
}

