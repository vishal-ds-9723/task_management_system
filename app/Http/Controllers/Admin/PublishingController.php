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

    public function bulkPublish(Request $request)
    {
        $request->validate([
            'task_ids'   => 'required|array',
            'task_ids.*' => 'integer|exists:tasks,id',
        ]);

        $taskIds = $request->input('task_ids', []);
        $count = $this->service->bulkPublish($taskIds);

        $message = "Successfully published {$count} task" . ($count !== 1 ? 's' : '') . '!';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $count,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function publish(Request $request, Task $task)
    {
        $this->service->publish($task);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task marked as Published successfully!',
                'status'  => 'published',
            ]);
        }

        return redirect()->back()->with('success', 'Task marked as Published successfully!');
    }

    public function unpublish(Request $request, Task $task)
    {
        $this->service->unpublish($task);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task publishing reverted to Ready to Publish.',
                'status'  => 'completed',
            ]);
        }

        return redirect()->back()->with('success', 'Task publishing reverted to Ready to Publish.');
    }
}

