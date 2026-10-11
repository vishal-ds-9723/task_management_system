<?php

namespace App\Services\Designer;

use App\Http\Requests\Designer\StoreUrgentTaskRequest;
use App\Models\Task;
use App\Models\TaskPause;
use App\Models\User;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\Client;
use App\Models\ClientSocialMediaLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Designer\TaskLinkHelperTrait;
use App\Support\Concerns\StoresTaskMedia;

class TaskService
{
    use TaskLinkHelperTrait;
    use StoresTaskMedia;

    public function getIndexData(Request $request)
    {
        $query = Task::with(['client', 'creator', 'assignee'])
            ->assignedTo(Auth::id());

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('name', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'revisions') {
                $query->where('revision_count', '>', 0)->whereNotIn('status', ['completed', 'published']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('creator_id')) {
            $query->where('created_by', $request->get('creator_id'));
        }

        $tasks = $query->orderByRaw("CASE status WHEN 'inprogress' THEN 0 WHEN 'todo' THEN 1 WHEN 'review' THEN 2 WHEN 'completed' THEN 3 END")
            ->orderByRaw('post_date IS NULL, post_date ASC')
            ->get();

        $firstFocusId = $tasks->first(fn ($t) => !in_array($t->status, ['completed', 'review', 'pending_approval', 'published']))?->id;

        $tasks->each(function ($task) use ($firstFocusId) {
            $task->is_locked = false;
            $task->is_focus = ($task->id === $firstFocusId);
        });

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $strategists = User::whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin'])->orderBy('name')->get();
        $designers = User::whereIn('role', ['designer', 'developer', 'editor', 'content_writer'])->orderBy('name')->get();

        return compact('tasks', 'clients', 'strategists', 'designers');
    }

    public function show(Task $task)
    {
        $isOwner = ($task->assigned_to === Auth::id());
        $isReadOnly = !$isOwner;

        $task->load(['client.socialMediaLinks', 'comments.user', 'creator', 'assignee', 'socialMediaLink', 'media']);

        $selectedSocialMediaLinks = collect();

        $selectedIds = collect((array) ($task->selected_social_media_link_ids ?? []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedIds->isNotEmpty()) {
            $linksById = ClientSocialMediaLink::query()
                ->where('client_id', '=', (int) $task->client_id, 'and')
                ->whereIn('id', $selectedIds->all())
                ->get()
                ->keyBy('id');

            $selectedSocialMediaLinks = $selectedIds
                ->map(fn ($id) => $linksById->get($id))
                ->filter()
                ->values();
        } elseif ($task->socialMediaLink) {
            $selectedSocialMediaLinks = collect([$task->socialMediaLink]);
        }

        // Extracted prominent revision data (if task has revisions)
        $latestRevisionComment = null;
        $latestRevisionNotes   = null;
        $latestRevisionMedia   = collect();

        if ($task->revision_count > 0) {
            $revisionCommentModel = $task->comments
                ->filter(function ($c) {
                    $body = strtolower($c->body ?? '');
                    return str_contains($body, 'revision requested')
                        || str_contains($body, 'revision')
                        || str_contains($body, 'changes requested');
                })
                ->last();

            if (!$revisionCommentModel) {
                $revisionCommentModel = $task->comments
                    ->filter(fn($c) => in_array($c->user?->role, ['admin', 'strategist']))
                    ->last();
            }

            if ($revisionCommentModel) {
                $latestRevisionComment = $revisionCommentModel;
                $clean = preg_replace('/^(?:🔄|✍️|🔁)?\s*(?:Visual\s+)?Revision requested(?:\s+by\s+[A-Za-z]+)?:\s*/iu', '', $revisionCommentModel->body);
                $latestRevisionNotes = trim($clean);
            }

            $allMedia = $task->relationLoaded('media') ? $task->media : $task->media()->get();
            $latestRevisionMedia = $allMedia->filter(function ($m) use ($task) {
                return $m->isReference() && $m->getRevisionCount() === (int) $task->revision_count;
            })->values();
        }

        $workflowSteps = $this->getWorkflowSteps($task);

        return compact(
            'task',
            'workflowSteps',
            'selectedSocialMediaLinks',
            'isReadOnly',
            'latestRevisionComment',
            'latestRevisionNotes',
            'latestRevisionMedia'
        );
    }

    protected function getWorkflowSteps(Task $task): array
    {
        $reviewLabel = $task->is_urgent_task
            ? 'Client Delivery'
            : ($task->status === 'pending_approval'
            ? 'Pending Admin Approval'
            : 'In Review');

        $statusToStep = [
            'todo' => 0,
            'inprogress' => 1,
            'review' => 2,
            'pending_approval' => 2,
            'completed' => 3,
            'published' => 3,
        ];

        $currentStep = $statusToStep[$task->status] ?? 0;
        $isTerminal = in_array($task->status, ['completed', 'published'], true);

        $steps = [
            [
                'label' => 'Assigned',
                'icon' => '<i class="fas fa-clipboard-list"></i>',
                'time' => $task->created_at,
                'order' => 0,
            ],
            [
                'label' => 'In Progress',
                'icon' => '<i class="fas fa-pen-ruler"></i>',
                'time' => $task->started_at,
                'order' => 1,
            ],
            [
                'label' => $reviewLabel,
                'icon' => '<i class="fas fa-hourglass-half"></i>',
                'time' => $task->submitted_at,
                'order' => 2,
            ],
            [
                'label' => 'Completed',
                'icon' => '<i class="fas fa-circle-check"></i>',
                'time' => $task->completed_at,
                'order' => 3,
            ],
        ];

        return array_map(function (array $step) use ($currentStep, $isTerminal) {
            $order = $step['order'];
            $step['done'] = $isTerminal ? $order <= $currentStep : $order < $currentStep;
            $step['current'] = !$isTerminal && $order === $currentStep;
            unset($step['order']);

            return $step;
        }, $steps);
    }

    private function isTaskLocked(Task $task): bool
    {
        // Tasks are no longer artificially blocked; designers can preview and prepare any assigned task
        return false;
    }

    public function startTask(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);
        abort_if($task->status !== 'todo', 400, 'Task is not in queue/to-do status.');

        $task->update([
            'status'     => 'inprogress',
            'started_at' => $task->started_at ?? now(),
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => 'Started work on task',
            'old_values' => ['status' => 'todo'],
            'new_values' => ['status' => 'inprogress'],
            'ip_address' => $request->ip(),
        ]);

        if ($task->created_by && $task->created_by !== Auth::id()) {
            Notification::create([
                'user_id'  => $task->created_by,
                'task_id'  => $task->id,
                'icon'     => '🚀',
                'title'    => 'Work started on task',
                'subtitle' => Auth::user()->name . ' started working on "' . $task->title . '"',
                'link'     => route('strategist.tasks.show', $task),
            ]);
        }

        $msg = 'Task started! You are now actively working on this deliverable.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'task'    => $task,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function submitForReview(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);
        abort_if($task->status !== 'inprogress' || $task->is_paused, 403, 'Only active in-progress tasks can be submitted.');

        $hasDeliverable = $task->deliverableMedia()->count() > 0 
            || $task->media()->where('collection_name', 'task-media')->exists();
        abort_if(!$hasDeliverable, 422, 'Notice: You must upload at least one design image or deliverable file before submitting this task for review.');

        $oldStatus = $task->status;
        $isUrgentTask = (bool) $task->is_urgent_task;
        $awayMode = Setting::isStrategistAway();

        // Urgent tasks go through the same review → admin approval flow as every other task
        $newStatus = $awayMode ? 'pending_approval' : 'review';

        $task->update([
            'status'       => $newStatus,
            'submitted_at' => now(),
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => ($isUrgentTask ? 'Submitted urgent task' : 'Submitted task')
                . ($awayMode ? ' directly for admin approval (away mode)' : ' for review'),
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'ip_address' => $request->ip(),
        ]);

        if ($awayMode) {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id'  => $admin->id,
                    'task_id'  => $task->id,
                    'icon'     => '🟠',
                    'title'    => 'Task ready for approval (away mode)',
                    'subtitle' => Auth::user()->name . ' submitted "' . $task->title . '" — strategist away, needs your direct review',
                    'link'     => route('admin.approvals.show', $task),
                ]);
            }
        } else {
            if ($isUrgentTask) {
                // The designer created this one themselves, so every strategist gets it to review
                foreach (User::where('role', 'strategist')->get() as $strategist) {
                    Notification::create([
                        'user_id'  => $strategist->id,
                        'task_id'  => $task->id,
                        'icon'     => '🚨',
                        'title'    => 'Urgent task ready for review',
                        'subtitle' => Auth::user()->name . ' submitted urgent task "' . $task->title . '" (requested by ' . ($task->urgent_requested_by ?: 'client') . ')',
                        'link'     => route('strategist.tasks.show', $task),
                    ]);
                }
            } elseif ($task->created_by && $task->created_by !== Auth::id()) {
                Notification::create([
                    'user_id'  => $task->created_by,
                    'task_id'  => $task->id,
                    'icon'     => '📋',
                    'title'    => 'Task submitted for review',
                    'subtitle' => Auth::user()->name . ' submitted "' . $task->title . '" for your review',
                    'link'     => route('strategist.tasks.show', $task),
                ]);
            }

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id'  => $admin->id,
                    'task_id'  => $task->id,
                    'icon'     => '📋',
                    'title'    => 'Task pending approval',
                    'subtitle' => Auth::user()->name . ' submitted "' . $task->title . '" for admin approval',
                    'link'     => route('admin.approvals.show', $task),
                ]);
            }
        }

        $msg = $awayMode
            ? 'Task submitted directly for admin approval (strategist away mode).'
            : ($isUrgentTask
            ? 'Urgent task submitted for review! The strategists have been notified.'
            : 'Task submitted for review! Your strategist will be notified.');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'task' => $task,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function updateStatus(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:todo,inprogress,review,pending_approval,completed,published'],
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $task->status;

        if ($oldStatus === 'completed') {
            return back()->with('error', 'Completed tasks cannot be changed.');
        }
        if ($oldStatus === 'review' && $newStatus !== 'inprogress') {
            return back()->with('error', 'Task is under review. Wait for feedback.');
        }

        if (in_array($newStatus, ['review', 'pending_approval', 'completed', 'published'], true)) {
            $hasDeliverable = $task->deliverableMedia()->count() > 0 
                || $task->media()->where('collection_name', 'task-media')->exists();
            if (!$hasDeliverable) {
                $errorMsg = 'Notice: You must upload at least one design image or deliverable file before submitting this task for review.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return back()->with('error', $errorMsg);
            }
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'inprogress' && !$task->started_at) {
            $updateData['started_at'] = now();
        }
        if ($newStatus === 'review') {
            $updateData['submitted_at'] = now();
        }

        $task->update($updateData);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => 'Status changed from ' . $oldStatus . ' to ' . $newStatus,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'ip_address' => $request->ip(),
        ]);

        if ($task->created_by && $task->created_by !== Auth::id() && $oldStatus !== $newStatus) {
            $icons = ['inprogress' => '🔵', 'review' => '📋', 'completed' => '✅', 'todo' => '🔄'];
            $labels = ['inprogress' => 'In Progress', 'review' => 'Ready for Review', 'completed' => 'Completed', 'todo' => 'To Do'];

                Notification::create([
                    'user_id'  => $task->created_by,
                    'task_id'  => $task->id,
                    'icon'     => $icons[$newStatus] ?? '🔔',
                    'title'    => 'Task moved to ' . ($labels[$newStatus] ?? ucfirst($newStatus)),
                    'subtitle' => Auth::user()->name . ' updated "' . $task->title . '"',
                    'link'     => route('strategist.tasks.show', $task),
                ]);
        }

        return back()->with('success', 'Status updated.');
    }

    public function addComment(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $task->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $validated['body'],
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => 'Added comment',
            'old_values' => null,
            'new_values' => ['comment' => $validated['body']],
            'ip_address' => $request->ip(),
        ]);

        if ($task->created_by && $task->created_by !== Auth::id()) {
                Notification::create([
                    'user_id'  => $task->created_by,
                    'task_id'  => $task->id,
                    'icon'     => '💬',
                    'title'    => 'New comment on task',
                    'subtitle' => Auth::user()->name . ' commented on "' . $task->title . '"',
                    'link'     => route('strategist.tasks.show', $task),
                ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            $comment = $task->comments()->latest()->first();
            $comment->load('user');
            return response()->json([
                'success'      => true,
                'message'      => 'Comment added.',
                'user_name'    => $comment->user->name,
                'user_initial' => strtoupper(substr($comment->user->name, 0, 1)),
                'user_color'   => $comment->user->avatar_color ?? 'var(--primary)',
                'user_role'    => $comment->user->role,
                'content'      => $comment->body,
                'created_at'   => $comment->created_at->diffForHumans(),
            ]);
        }

        return back()->with('success', 'Comment added.');
    }

    public function uploadMedia(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);

        $request->validate([
            'media' => ['required', 'array'],
            'media.*' => ['required', 'file', 'mimes:jpg,jpeg,jfif,png,gif,webp,svg,mp4,mov,webm,mkv,avi,m4v,3gp,ts,pdf,doc,docx,zip,rar,7z', 'max:512000'],
        ]);

        $isReference = ($request->input('collection_name') === 'task-reference' || $request->boolean('is_reference'));
        $collection = $isReference ? 'task-reference' : 'task-media';
        $extraMeta = [
            'is_reference'   => $isReference,
            'revision_count' => (int) ($task->revision_count ?? 0),
        ];

        [$uploadedCount, $failedFiles] = $this->storeUploadedTaskMedia($task, $request->file('media'), $collection, $extraMeta);

        if ($uploadedCount === 0) {
            $message = 'All files failed to upload: ' . implode(', ', $failedFiles);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        $successMessage = $uploadedCount . ' file(s) uploaded successfully';
        if (!empty($failedFiles)) {
            $successMessage .= '. Failed: ' . implode(', ', $failedFiles);
        }

        if (!$isReference && $task->status === 'todo') {
            $task->update([
                'status'     => 'inprogress',
                'started_at' => $task->started_at ?? now(),
            ]);
            AuditLog::create([
                'user_id'    => Auth::id(),
                'task_id'    => $task->id,
                'action'     => 'Started work on task via deliverable upload',
                'old_values' => ['status' => 'todo'],
                'new_values' => ['status' => 'inprogress'],
                'ip_address' => $request->ip(),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'uploaded_count' => $uploadedCount,
                'failed_count' => count($failedFiles),
            ]);
        }

        return back()->with('success', $successMessage);
    }

    public function deleteMedia(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);

        $request->validate([
            'media_id' => ['required', 'integer'],
        ]);

        if (!$this->deleteTaskMediaRecord($task, (int) $request->input('media_id'))) {
            $message = 'Media not found or does not belong to this task.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 404);
            }
            return back()->with('error', $message);
        }

        $message = 'Media removed successfully.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return back()->with('success', $message);
    }

    public function pauseTask(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);
        abort_if($task->status !== 'inprogress', 403, 'Only in-progress tasks can be paused.');
        abort_if($task->is_paused, 403, 'Task is already paused.');

        $request->validate([
            'reason' => 'required|string|max:500',
            'work_logged' => 'nullable|string|max:2000',
        ]);

        $task->update([
            'is_paused'    => true,
            'paused_at'    => now(),
            'pause_reason' => $request->reason,
        ]);

        TaskPause::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'reason'  => $request->reason,
            'work_logged' => $request->work_logged,
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => 'Paused task',
            'old_values' => ['is_paused' => false],
            'new_values' => ['is_paused' => true, 'reason' => $request->reason],
            'ip_address' => request()->ip(),
        ]);

        if ($task->created_by && $task->created_by !== Auth::id()) {
                Notification::create([
                    'user_id'  => $task->created_by,
                    'task_id'  => $task->id,
                    'icon'     => '⏸️',
                    'title'    => 'Task paused',
                    'subtitle' => Auth::user()->name . ' paused "' . $task->title . '" — ' . $request->reason,
                    'link'     => route('strategist.tasks.show', $task),
                ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task paused successfully.']);
        }

        return back()->with('success', 'Task paused.');
    }

    public function resumeTask(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);
        abort_if(!$task->is_paused, 403, 'Task is not paused.');

        $pauseDuration = $task->paused_at ? $task->paused_at->diffInSeconds(now()) : 0;

        $task->update([
            'is_paused'            => false,
            'paused_at'            => null,
            'pause_reason'         => null,
            'total_paused_seconds' => $task->total_paused_seconds + $pauseDuration,
        ]);

        $activePause = TaskPause::where('task_id', $task->id)
            ->whereNull('resumed_at')
            ->latest()
            ->first();

        if ($activePause) {
            $activePause->update([
                'resumed_at'       => now(),
                'duration_seconds' => $pauseDuration,
            ]);
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => 'Resumed task',
            'old_values' => ['is_paused' => true],
            'new_values' => ['is_paused' => false, 'pause_duration_seconds' => $pauseDuration],
            'ip_address' => request()->ip(),
        ]);

        if ($task->created_by && $task->created_by !== Auth::id()) {
            $hours = intdiv($pauseDuration, 3600);
            $mins = intdiv($pauseDuration % 3600, 60);
            $durationStr = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";

                Notification::create([
                    'user_id'  => $task->created_by,
                    'task_id'  => $task->id,
                    'icon'     => '▶️',
                    'title'    => 'Task resumed',
                    'subtitle' => Auth::user()->name . ' resumed "' . $task->title . '" (paused ' . $durationStr . ')',
                    'link'     => route('strategist.tasks.show', $task),
                ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task resumed.']);
        }

        return back()->with('success', 'Task resumed.');
    }

    public function createUrgentTask(StoreUrgentTaskRequest $request)
    {
        $validated = $request->validated();
        $type = $validated['type'];
        $isSocialUrgent = Task::isSocialType($type);
        $isDesignUrgent = Task::isDesignType($type);
        $platforms = $isSocialUrgent
            ? array_values(array_filter($validated['platform'] ?? []))
            : null;
        $brief = trim((string) ($validated['brief'] ?? ''));
        $deadline = $validated['deadline'] ?? null;
        $designDeadline = $isDesignUrgent
            ? ($validated['design_deadline'] ?? $deadline)
            : null;

        $designer = Auth::user();

        $pausedTask = null;
        if (!empty($validated['auto_pause_task_id'])) {
            $pausedTask = Task::where('id', $validated['auto_pause_task_id'])
                ->where('assigned_to', $designer->id)
                ->where('status', 'inprogress')
                ->where('is_paused', false)
                ->first();

            if ($pausedTask) {
                $pausedTask->update([
                    'is_paused'    => true,
                    'paused_at'    => now(),
                    'pause_reason' => 'Auto-paused for urgent task: ' . $validated['title'],
                ]);

                TaskPause::create([
                    'task_id' => $pausedTask->id,
                    'user_id' => $designer->id,
                    'reason'  => 'Auto-paused for urgent task: ' . $validated['title'],
                ]);

                AuditLog::create([
                    'user_id'    => $designer->id,
                    'task_id'    => $pausedTask->id,
                    'action'     => 'Auto-paused for urgent task',
                    'old_values' => ['is_paused' => false],
                    'new_values' => ['is_paused' => true],
                    'ip_address' => request()->ip(),
                ]);
            }
        }

        $urgentTask = Task::create([
            'title'               => $validated['title'],
            'client_id'           => $validated['client_id'],
            'assigned_to'         => $designer->id,
            'created_by'          => $designer->id,
            'type'                => $type,
            'platform'            => $platforms,
            'priority'            => 'urgent',
            'status'              => 'inprogress',
            'started_at'          => now(),
            'brief'               => $brief !== '' ? $brief : null,
            'caption'             => null,
            'deadline'            => $deadline,
            'design_deadline'     => $designDeadline,
            'is_urgent_task'      => true,
            'urgent_requested_by' => $validated['urgent_requested_by'],
        ]);

        if ($pausedTask) {
            $activePause = TaskPause::where('task_id', $pausedTask->id)
                ->whereNull('resumed_at')
                ->latest()
                ->first();

            if ($activePause) {
                $activePause->update(['replacement_task_id' => $urgentTask->id]);
            }
        }

        AuditLog::create([
            'user_id'    => $designer->id,
            'task_id'    => $urgentTask->id,
            'action'     => 'Created urgent task',
            'old_values' => [],
            'new_values' => [
                'title'        => $urgentTask->title,
                'type'         => $urgentTask->type,
                'requested_by' => $validated['urgent_requested_by'],
                'paused_task'  => $pausedTask?->title,
            ],
            'ip_address' => request()->ip(),
        ]);

        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id'  => $admin->id,
                'task_id'  => $urgentTask->id,
                'icon'     => '🚨',
                'title'    => 'Urgent task created by designer',
                'subtitle' => $designer->name . ' created urgent task "' . $urgentTask->title . '" (requested by ' . $validated['urgent_requested_by'] . ')',
                'link'     => route('admin.tasks.show', $urgentTask),
            ]);
        }

        $strategists = \App\Models\User::where('role', 'strategist')->get();
        foreach ($strategists as $strat) {
            Notification::create([
                'user_id'  => $strat->id,
                'task_id'  => $urgentTask->id,
                'icon'     => '🚨',
                'title'    => 'Urgent task created by designer',
                'subtitle' => $designer->name . ' created urgent task "' . $urgentTask->title . '" (requested by ' . $validated['urgent_requested_by'] . ')',
                'link'     => route('strategist.tasks.show', $urgentTask),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Urgent task created' . ($pausedTask ? ' and "' . $pausedTask->title . '" paused.' : '.'),
                'task_id'      => $urgentTask->id,
                'redirect_url' => route('designer.tasks.show', $urgentTask),
            ]);
        }

        return redirect()->route('designer.tasks.show', $urgentTask)
            ->with('success', 'Urgent task created' . ($pausedTask ? ' and "' . $pausedTask->title . '" paused.' : '.'));
    }

    /**
     * AJAX Live Sync: Fast fingerprint change detection & fragment renderer
     */
    public function getLiveSyncData(Request $request)
    {
        $userId = Auth::id();
        $clientFingerprint = (string) $request->get('fingerprint', '');

        // 1. Ultra-fast indexed signature query
        $stats = Task::assignedTo($userId)
            ->selectRaw('COUNT(*) as total_count, COALESCE(MAX(id), 0) as max_id, COALESCE(MAX(updated_at), "") as max_updated, SUM(CASE WHEN revision_count > 0 AND status NOT IN ("completed", "published") THEN 1 ELSE 0 END) as revision_count')
            ->first();

        $totalCount = (int) ($stats->total_count ?? 0);
        $maxId      = (int) ($stats->max_id ?? 0);
        $maxUpdated = (string) ($stats->max_updated ?? '');
        $revCount   = (int) ($stats->revision_count ?? 0);

        $serverFingerprint = md5("{$totalCount}-{$maxId}-{$maxUpdated}-{$revCount}");

        // If client fingerprint matches, return immediately (no changes, < 2ms)
        if ($clientFingerprint !== '' && $clientFingerprint === $serverFingerprint) {
            return [
                'has_changes' => false,
                'fingerprint' => $serverFingerprint,
            ];
        }

        // 2. Changes detected: Fetch full dataset
        $data = $this->getIndexData($request);
        $tasks = $data['tasks'];

        // Pipeline groupings & counts
        $inProgress    = $tasks->where('status', 'inprogress')->where('is_paused', false);
        $todo          = $tasks->where('status', 'todo');
        $inReview      = $tasks->whereIn('status', ['review', 'pending_approval']);
        $completed     = $tasks->where('status', 'completed');
        $revisionTasks = $tasks->filter(fn($t) => $t->revision_count > 0 && !in_array($t->status, ['completed', 'published']));

        $activeCount   = $inProgress->count() + $inReview->count();
        $queueCount    = $todo->count();
        $doneCount     = $completed->count();
        $revisionCount = $revisionTasks->count();
        $allCount      = $tasks->count();

        // Medium counts
        $mediumCounts = [
            'all'       => $allCount,
            'post'      => $tasks->filter(fn($t) => in_array(strtolower((string)$t->type), ['post', 'static']))->count(),
            'video'     => $tasks->filter(fn($t) => in_array(strtolower((string)$t->type), ['reel', 'video']))->count(),
            'carousel'  => $tasks->filter(fn($t) => strtolower((string)$t->type) === 'carousel')->count(),
            'branding'  => $tasks->filter(fn($t) => in_array(strtolower((string)$t->type), ['logo', 'branding']))->count(),
            'story'     => $tasks->filter(fn($t) => strtolower((string)$t->type) === 'story')->count(),
            'print'     => $tasks->filter(fn($t) => in_array(strtolower((string)$t->type), ['flyer', 'banner', 'brochure']))->count(),
        ];

        // Render HTML fragments
        $tableHtml = view('designer.partials.task-rows', ['tasks' => $tasks])->render();
        $gridHtml  = view('designer.partials.task-cards', ['tasks' => $tasks])->render();

        return [
            'has_changes' => true,
            'fingerprint' => $serverFingerprint,
            'counts'      => [
                'all'       => $allCount,
                'active'    => $activeCount,
                'revisions' => $revisionCount,
                'queue'     => $queueCount,
                'done'      => $doneCount,
                'mediums'   => $mediumCounts,
            ],
            'table_html'  => $tableHtml,
            'grid_html'   => $gridHtml,
        ];
    }
}

