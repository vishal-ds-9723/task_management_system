<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApprovalSearchRequest;
use App\Http\Requests\Admin\RejectTaskRequest;
use App\Models\Task;
use App\Services\Admin\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected $service;

    public function __construct(ApprovalService $service)
    {
        $this->service = $service;
    }

    public function index(ApprovalSearchRequest $request)
    {
        if ($request->filled('task_id')) {
            $task = Task::query()
                ->whereKey((int) $request->input('task_id'))
                ->first();

            if ($task) {
                $targetRoute = $task->is_urgent_task
                    ? 'admin.tasks.show'
                    : (in_array($task->status, ['review', 'pending_approval'], true)
                    ? 'admin.approvals.show'
                    : 'admin.tasks.show');

                return redirect()->route($targetRoute, $task);
            }
        }

        $tasks = $this->service->getPendingTasks($request);

        return view('admin.approvals.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        if ($task->is_urgent_task) {
            return redirect()->route('admin.tasks.show', $task);
        }

        $task = $this->service->getTaskForApproval($task);

        return view('admin.approvals.show', compact('task'));
    }

    public function approve(Request $request, Task $task)
    {
        try {
            $task = $this->service->approveTask($task);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 422) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }
                return back()->with('error', $e->getMessage());
            }
            throw $e;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task approved and marked as completed!',
                'task'    => $task,
            ]);
        }

        return back()->with('success', 'Task approved and marked as completed!');
    }

    public function reject(RejectTaskRequest $request, Task $task)
    {
        $task = $this->service->rejectTask($request->validated(), $task);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task sent back for revision (Revision #' . $task->revision_count . ')',
            ]);
        }

        return redirect()->route('admin.approvals')->with('success', 'Task sent back for revision (Revision #' . $task->revision_count . ')');
    }
}

