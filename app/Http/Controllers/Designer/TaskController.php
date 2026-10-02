<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Designer\IndexTasksRequest;
use App\Http\Requests\Designer\StoreUrgentTaskRequest;
use App\Http\Requests\Designer\AddCommentRequest;
use App\Services\Designer\TaskService;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    public function index(IndexTasksRequest $request)
    {
        $data = $this->service->getIndexData($request);

        return view('designer.tasks', $data);
    }

    public function show(Task $task)
    {
        $data = $this->service->show($task);

        return view('designer.task-detail', $data);
    }

    public function submitForReview(Task $task)
    {
        $data = $this->service->submitForReview(request(), $task);

        return $data;
    }

    public function updateStatus(Request $request, Task $task)
    {
        $data = $this->service->updateStatus($request, $task);

        return $data;
    }

    public function addComment(AddCommentRequest $request, Task $task)
    {
        $data = $this->service->addComment($request, $task);

        return $data;
    }

    public function uploadMedia(Request $request, Task $task)
    {
        $data = $this->service->uploadMedia($request, $task);

        return $data;
    }

    public function deleteMedia(Request $request, Task $task)
    {
        $data = $this->service->deleteMedia($request, $task);

        return $data;
    }

    public function pauseTask(Request $request, Task $task)
    {
        $data = $this->service->pauseTask($request, $task);

        return $data;
    }

    public function resumeTask(Request $request, Task $task)
    {
        $data = $this->service->resumeTask($request, $task);

        return $data;
    }

    public function urgentTaskPage()
    {
        $clients = \App\Models\Client::where('is_active', true)->orderBy('name')->get();
        $currentActiveTask = Task::assignedTo(Auth::id())
            ->where('status', 'inprogress')
            ->where('is_paused', false)
            ->first();

        return view('designer.urgent-task', compact('clients', 'currentActiveTask'));
    }

    public function createUrgentTask(StoreUrgentTaskRequest $request)
    {
        $data = $this->service->createUrgentTask($request);

        return $data;
    }
}

