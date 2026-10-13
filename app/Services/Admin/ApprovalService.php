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
use App\Models\Client;
use App\Models\User;

class ApprovalService
{
    use \App\Support\Concerns\StoresTaskMedia;

    /**
     * Get pending approval tasks with accurate stats and filter datasets
     */
    public function getPendingTasksData(Request $request): array
    {
        $baseQuery = Task::whereIn('status', ['review', 'pending_approval']);

        // Calculate accurate global stats across the database
        $totalPending = (clone $baseQuery)->count();
        $urgentCount = (clone $baseQuery)->where(function ($q) {
            $q->where('is_urgent_task', true)->orWhere('priority', 'urgent');
        })->count();
        $withMediaCount = (clone $baseQuery)->has('media')->count();
        $awaitingFilesCount = max(0, $totalPending - $withMediaCount);

        $stats = [
            'totalPending'       => $totalPending,
            'urgentCount'        => $urgentCount,
            'withMediaCount'     => $withMediaCount,
            'awaitingFilesCount' => $awaitingFilesCount,
        ];

        // Filtered query for pagination
        $query = (clone $baseQuery)
            ->with(['creator', 'assignee', 'client', 'media'])
            ->latest('updated_at');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('caption', 'like', $search)
                  ->orWhere('brief', 'like', $search)
                  ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', $search))
                  ->orWhereHas('assignee', fn ($q3) => $q3->where('name', 'like', $search));
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', (int) $request->input('client_id'));
        }

        if ($request->filled('assignee_id')) {
            $query->where('assigned_to', (int) $request->input('assignee_id'));
        }

        if ($request->input('urgent_only') === '1' || $request->input('urgent_only') === 'yes') {
            $query->where(function ($q) {
                $q->where('is_urgent_task', true)->orWhere('priority', 'urgent');
            });
        }

        if ($request->filled('has_media')) {
            if ($request->input('has_media') === 'yes') {
                $query->has('media');
            } elseif ($request->input('has_media') === 'no') {
                $query->doesntHave('media');
            }
        }

        $tasks = $query->paginate(20)->withQueryString();

        $clients = Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'emoji', 'logo']);
        $designers = User::whereIn('role', ['designer', 'developer'])->orderBy('name')->get(['id', 'name', 'role']);

        return compact('tasks', 'stats', 'clients', 'designers');
    }

    /**
     * Legacy method for backwards compatibility
     */
    public function getPendingTasks(Request $request)
    {
        return $this->getPendingTasksData($request)['tasks'];
    }

    /**
     * Bulk approve multiple review tasks
     */
    public function bulkApprove(array $taskIds): int
    {
        $tasks = Task::whereIn('id', $taskIds)
            ->whereIn('status', ['review', 'pending_approval'])
            ->get();

        $count = 0;
        foreach ($tasks as $task) {
            $this->approveTask($task);
            $count++;
        }

        return $count;
    }

    /**
     * Load task for approval view
     */
    public function getTaskForApproval(Task $task)
    {
        $task->load('creator', 'assignee', 'client.socialMediaLinks', 'comments.user', 'socialMediaLink', 'media');
        return $task;
    }

    /**
     * Approve task — single code path used by both ApprovalController and TaskController.
     */
    public function approveTask(Task $task)
    {
        // Allow Admin to approve from review, pending_approval, or active states
        abort_if(in_array($task->status, ['completed', 'published'], true), 400, 'Task is already approved.');

        // Soft warning is handled at UI level — Admin override allows approval even if caption/hashtags are not yet set

        // Client portal is removed for now — Admin approval completes the task directly
        $targetStatus = 'completed';

        $task->update([
            'status'                => $targetStatus,
            'completed_at'          => now(),
            'admin_approval_status' => 'approved',
            'admin_approved_by'     => Auth::id(),
            'admin_approved_at'     => now(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'task_id' => $task->id,
            'action'  => $targetStatus === 'client_review' ? 'Approved by Admin — sent to Client Review' : 'Approved task for completion',
            'details' => $targetStatus === 'client_review' ? 'Awaiting client portal sign-off' : 'Admin final approval completed',
        ]);

        // If sent to client review, notify strategist and designer
        if ($targetStatus === 'client_review') {
            if ($task->assignee) {
                Notification::create([
                    'user_id'  => $task->assignee->id,
                    'task_id'  => $task->id,
                    'icon'     => '👀',
                    'title'    => 'Approved internally — Sent to Client',
                    'subtitle' => '"' . $task->title . '" passed internal double-check and is now in client review.',
                    'link'     => '/designer/tasks/' . $task->id,
                ]);
            }
            if ($task->created_by && $task->created_by !== Auth::id()) {
                Notification::create([
                    'user_id'  => $task->created_by,
                    'task_id'  => $task->id,
                    'icon'     => '👔',
                    'title'    => 'Sent to Client Review',
                    'subtitle' => 'Admin approved "' . $task->title . '" — now awaiting client sign-off.',
                    'link'     => route('strategist.tasks.show', $task),
                ]);
            }
            return $task;
        }

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

        // Urgent tasks were raised by a designer, so the strategists handle publishing for them
        $publishers = $task->is_urgent_task
            ? \App\Models\User::where('role', 'strategist')->get()
            : collect([$task->creator])->filter();

        // Notify strategists only when not in away mode — they can't act on it otherwise
        if (!Setting::isStrategistAway() && Task::needsCaption($task->type)) {
            foreach ($publishers as $publisher) {
                Notification::create([
                    'user_id'  => $publisher->id,
                    'task_id'  => $task->id,
                    'icon'     => '📢',
                    'title'    => $task->is_urgent_task ? 'Urgent task approved — upload proof!' : 'Task Approved — Upload Proof!',
                    'subtitle' => '"' . $task->title . '" is approved. Upload social media proof links to mark as published.',
                    'link'     => $publisher->role === 'admin'
                        ? route('admin.publishing.show', $task)
                        : route('strategist.publishing.show', $task),
                ]);
            }
        } elseif (!Setting::isStrategistAway()) {
            // Print/design and dev work: nothing to publish, approval is the finish line
            foreach ($publishers as $publisher) {
                Notification::create([
                    'user_id'  => $publisher->id,
                    'task_id'  => $task->id,
                    'icon'     => '✅',
                    'title'    => 'Task approved & completed',
                    'subtitle' => '"' . $task->title . '" was approved by admin and is complete.',
                    'link'     => $task->linkFor($publisher),
                ]);
            }
        }

        return $task;
    }

    /**
     * Reject task for revision (targeted to either Designer or Strategist).
     */
    public function rejectTask(array $data, Task $task, ?array $files = null)
    {
        abort_if(!in_array($task->status, ['review', 'pending_approval']), 403);

        $awayMode = Setting::isStrategistAway();
        $target = $data['revision_target'] ?? 'designer';
        $reason = $data['rejection_reason'];

        if ($target === 'strategist') {
            // Copy / Strategy Revision: Stays in review, assigned strategist updates caption/strategy
            $task->update([
                'status'                => 'review',
                'revision_target'       => 'strategist',
                'admin_approval_status' => 'rejected',
                'admin_approved_by'     => Auth::id(),
                'admin_approved_at'     => now(),
            ]);

            Comment::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'body'    => '✍️ Copy/Strategy Revision requested by Admin: ' . $reason,
            ]);

            AuditLog::create([
                'user_id'  => Auth::id(),
                'task_id'  => $task->id,
                'action'   => 'Requested copy revision from Strategist',
                'details'  => $reason,
            ]);

            if ($task->created_by && $task->created_by !== Auth::id()) {
                Notification::create([
                    'user_id'  => $task->created_by,
                    'task_id'  => $task->id,
                    'icon'     => '✍️',
                    'title'    => 'Copy revision needed',
                    'subtitle' => 'Admin requested copy adjustments on "' . $task->title . '": ' . Str::limit($reason, 80),
                    'link'     => route('strategist.tasks.show', $task),
                ]);
            }
        } else {
            // Visual / Graphic Revision: Atomic increment and back to Designer
            $task->increment('revision_count');
            $task->update([
                'status'                => 'inprogress',
                'revision_target'       => 'designer',
                'admin_approval_status' => 'rejected',
                'admin_approved_by'     => Auth::id(),
                'admin_approved_at'     => now(),
            ]);

            if (!empty($files)) {
                $this->storeUploadedTaskMedia($task, $files, 'task-reference', [
                    'is_reference'     => true,
                    'revision_count'   => (int) $task->revision_count,
                    'rejection_reason' => $reason,
                ]);
            }

            Comment::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'body'    => '❌ Visual Revision requested by admin: ' . $reason,
            ]);

            AuditLog::create([
                'user_id'  => Auth::id(),
                'task_id'  => $task->id,
                'action'   => 'Rejected task — sent back for visual revision',
                'details'  => $reason,
            ]);

            if ($task->assigned_to) {
                Notification::create([
                    'user_id'  => $task->assigned_to,
                    'task_id'  => $task->id,
                    'icon'     => '🔄',
                    'title'    => 'Visual revision requested',
                    'subtitle' => 'Admin needs changes on "' . $task->title . '": ' . Str::limit($reason, 80),
                    'link'     => '/designer/tasks/' . $task->id,
                ]);
            }

            if (!$awayMode && $task->created_by && $task->created_by !== Auth::id()) {
                Notification::create([
                    'user_id'  => $task->created_by,
                    'task_id'  => $task->id,
                    'icon'     => '❌',
                    'title'    => 'Task sent to designer for revision',
                    'subtitle' => Auth::user()->name . ' requested visual changes on "' . $task->title . '": ' . Str::limit($reason, 80),
                    'link'     => '/strategist/tasks/' . $task->id,
                ]);
            }
        }

        return $task;
    }
}

