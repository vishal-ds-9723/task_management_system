<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\PublishingService;
use App\Http\Requests\Admin\PublishingIndexRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class PublishingController extends Controller
{
    protected $service;

    public function __construct(PublishingService $service)
    {
        $this->service = $service;
    }

    public function index(PublishingIndexRequest $request)
    {
        $data = $this->service->getPublishingIndexData($request);

        return view('admin.publishing.index', $data);
    }

    public function show(Task $task)
    {
        $data = $this->service->getPublishingShowData($task);

        return view('admin.publishing.show', $data);
    }
}

