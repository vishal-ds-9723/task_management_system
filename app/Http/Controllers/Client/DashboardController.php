<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\DashboardService;
use App\Models\Task;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function approveTask(Task $task)
    {
        if ($task->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $task->update([
            'status'             => 'completed',
            'completed_at'       => now(),
            'client_approved_at' => now(),
        ]);

        Comment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body'    => '✅ Client approved the creative for publishing.',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'task_id' => $task->id,
            'action'  => 'Client approved creative',
            'details' => 'Client sign-off completed',
        ]);

        if ($task->created_by) {
            Notification::create([
                'user_id'  => $task->created_by,
                'task_id'  => $task->id,
                'icon'     => '🎉',
                'title'    => 'Client Approved Creative!',
                'subtitle' => 'Client approved "' . $task->title . '" — upload proof to mark as published.',
                'link'     => route('strategist.publishing.show', $task),
            ]);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Creative approved successfully!']);
        }

        return back()->with('success', 'Creative approved successfully! Our team will now proceed with publishing.');
    }

    public function requestChanges(Request $request, Task $task)
    {
        if ($task->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        $task->update([
            'status'          => 'review',
            'revision_target' => 'strategist',
        ]);

        Comment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body'    => '💬 Client requested changes: ' . $validated['notes'],
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'task_id' => $task->id,
            'action'  => 'Client requested changes',
            'details' => $validated['notes'],
        ]);

        if ($task->created_by) {
            Notification::create([
                'user_id'  => $task->created_by,
                'task_id'  => $task->id,
                'icon'     => '📝',
                'title'    => 'Client Feedback Received',
                'subtitle' => 'Client requested adjustments on "' . $task->title . '": ' . Str::limit($validated['notes'], 80),
                'link'     => route('strategist.tasks.show', $task),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Change request submitted to the team.']);
        }

        return back()->with('success', 'Your change request has been sent to our creative team.');
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

