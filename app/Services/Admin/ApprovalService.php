<?php

namespace App\Services\Admin;

use App\Models\Task;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApprovalService
{
    /**
     * Get pending approval tasks with filters
     */
    public function getPendingTasks(Request $request)
    {
        $query = Task::whereIn('status', ['review', 'pending_approval'])
            ->where('is_urgent_task', false)
            ->with('creator', 'assignee', 'client')
            ->latest('updated_at');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('caption', 'like', $search)
                  ->orWhere('brief', 'like', $search)
                  ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', $search));
            });
        }

        return $query->paginate(20)->withQueryString();
    }

    /**
     * Load task for approval view
     */
    public function getTaskForApproval(Task $task)
    {
        abort_if(!in_array($task->status, ['review', 'pending_approval']), 403);
        $task->load('creator', 'assignee', 'client.socialMediaLinks', 'comments.user', 'socialMediaLink', 'media');
        return $task;
    }

    /**
     * Approve task — single code path used by both ApprovalController and TaskController.
     */
    public function approveTask(Task $task)
    {
        abort_if(!in_array($task->status, ['review', 'pending_approval']), 403);

        $isDeveloperTask = in_array($task->type, ['software', 'website']);
        $caption = trim((string) ($task->caption ?? ''));
        $hashtags = trim((string) ($task->hashtags ?? ''));

        if (!$isDeveloperTask && ($caption === '' || $hashtags === '')) {
            abort(422, 'Cannot approve: caption and hashtags are required before approving this task.');
        }

        $task->update([
            'status'                => 'completed',
            'completed_at'          => now(),
            'admin_approval_status' => 'approved',
            'admin_approved_by'     => Auth::id(),
            'admin_approved_at'     => now(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'task_id' => $task->id,
            'action'  => 'Approved task for completion',
            'details' => 'Admin final approval completed',
        ]);

        // Notify designer
        if ($task->assignee) {
            $designerLink = $task->assignee->role === 'developer'
                ? '/developer/tasks'
                : '/designer/tasks/' . $task->id;

            Notification::create([
                'user_id'  => $task->assignee->id,
                'task_id'  => $task->id,
                'icon'     => '✅',
                'title'    => 'Task Approved & Completed!',
                'subtitle' => '"' . $task->title . '" has been approved by admin and marked complete',
                'link'     => $designerLink,
            ]);
        }

        // Notify strategist only when not in away mode — they can't act on it otherwise
        if (!Setting::isStrategistAway() && $task->creator) {
            Notification::create([
                'user_id'  => $task->creator->id,
                'task_id'  => $task->id,
                'icon'     => '📢',
                'title'    => 'Task Approved — Upload Proof!',
                'subtitle' => '"' . $task->title . '" is approved. Upload social media proof links to mark as published.',
                'link'     => '/strategist/publishing',
            ]);
        }

        return $task;
    }

    /**
     * Reject task for revision.
     */
    public function rejectTask(array $data, Task $task)
    {
        abort_if(!in_array($task->status, ['review', 'pending_approval']), 403);

        $awayMode = Setting::isStrategistAway();

        // Atomic DB-level increment prevents double-count on concurrent requests
        $task->increment('revision_count');
        $task->update([
            'status'                => 'inprogress',
            'admin_approval_status' => 'rejected',
            'admin_approved_by'     => Auth::id(),
            'admin_approved_at'     => now(),
        ]);

        // Add comment
        Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body'    => '❌ Revision requested by admin: ' . $data['rejection_reason'],
        ]);

        AuditLog::create([
            'user_id'  => Auth::id(),
            'task_id'  => $task->id,
            'action'   => 'Rejected task — sent back for revision',
            'details'  => $data['rejection_reason'],
        ]);

        $reason = $data['rejection_reason'];

        // Notify assignee
        if ($task->assigned_to) {
            Notification::create([
                'user_id'  => $task->assigned_to,
                'task_id'  => $task->id,
                'icon'     => '🔄',
                'title'    => 'Revision requested',
                'subtitle' => 'Admin needs changes on "' . $task->title . '": ' . Str::limit($reason, 80),
                'link'     => '/designer/tasks/' . $task->id,
            ]);
        }

        // Notify creator
        if (!$awayMode && $task->created_by && $task->created_by !== Auth::id()) {
            Notification::create([
                'user_id'  => $task->created_by,
                'task_id'  => $task->id,
                'icon'     => '❌',
                'title'    => 'Task rejected by admin',
                'subtitle' => Auth::user()->name . ' rejected "' . $task->title . '": ' . Str::limit($reason, 80),
                'link'     => '/strategist/tasks/' . $task->id,
            ]);
        }

        return $task;
    }
}

