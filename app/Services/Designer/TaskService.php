<?php

namespace App\Services\Designer;

use App\Http\Requests\Designer\StoreUrgentTaskRequest;
use App\Models\Media;
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
use Illuminate\Support\Facades\Storage;
use App\Services\Designer\TaskLinkHelperTrait;

class TaskService
{
    use TaskLinkHelperTrait;
    public function getIndexData(Request $request)
    {
        $scope = $request->get('scope', 'my');

        $query = Task::with(['client', 'creator', 'assignee']);

        if ($scope !== 'all') {
            $query->assignedTo(Auth::id());
        }

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
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('creator_id')) {
            $query->where('created_by', $request->get('creator_id'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->get('assigned_to'));
        }

        $tasks = $query->orderByRaw("CASE status WHEN 'inprogress' THEN 0 WHEN 'todo' THEN 1 WHEN 'review' THEN 2 WHEN 'completed' THEN 3 END")
            ->orderByRaw('post_date IS NULL, post_date ASC')
            ->get();

        $firstUnlockedId = $tasks->first(fn ($t) => !in_array($t->status, ['completed', 'review', 'pending_approval', 'published']))?->id;

        $tasks->each(function ($task) use ($firstUnlockedId, $scope) {
            $task->is_locked = ($scope === 'my')
                && !in_array($task->status, ['completed', 'review', 'pending_approval', 'published', 'inprogress'])
                && !$task->is_paused
                && $task->id !== $firstUnlockedId;
        });

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $strategists = User::whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin'])->orderBy('name')->get();
        $designers = User::whereIn('role', ['designer', 'developer', 'editor', 'content_writer'])->orderBy('name')->get();

        return compact('tasks', 'clients', 'strategists', 'designers', 'scope');
    }

    public function show(Task $task)
    {
        $isOwner = ($task->assigned_to === Auth::id());
        $isReadOnly = !$isOwner;

        if ($isOwner && $this->isTaskLocked($task)) {
            abort(403, 'Submit the current task first to unlock this one.');
        }

        $task->load(['client.socialMediaLinks', 'comments.user', 'creator', 'assignee', 'socialMediaLink']);

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

        if ($isOwner && $task->status === 'todo') {
            $task->update([
                'status'     => 'inprogress',
                'started_at' => now(),
            ]);

            AuditLog::create([
                'user_id'    => Auth::id(),
                'task_id'    => $task->id,
                'action'     => 'Auto-started task',
                'old_values' => ['status' => 'todo'],
                'new_values' => ['status' => 'inprogress'],
                'ip_address' => request()->ip(),
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

            $task->refresh();
        }

        $workflowSteps = $this->getWorkflowSteps($task);

        return compact('task', 'workflowSteps', 'selectedSocialMediaLinks', 'isReadOnly');
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
        if (in_array($task->status, ['completed', 'review', 'pending_approval', 'published', 'inprogress'])) {
            return false;
        }

        if ($task->is_paused) {
            return false;
        }

        $firstIncomplete = Task::where('assigned_to', $task->assigned_to)
            ->whereNotIn('status', ['completed', 'review', 'pending_approval', 'published'])
            ->where('is_paused', false)
            ->orderByRaw("CASE status WHEN 'inprogress' THEN 0 WHEN 'todo' THEN 1 END")
            ->orderByRaw('post_date IS NULL, post_date ASC')
            ->first();

        return $firstIncomplete && $firstIncomplete->id !== $task->id;
    }

    public function submitForReview(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);
        abort_if($task->status !== 'inprogress' || $task->is_paused, 403, 'Only active in-progress tasks can be submitted.');

        abort_if($task->media()->count() === 0, 422, 'Media upload is required before submission. Please upload your design file.');

        $oldStatus = $task->status;
        $isUrgentTask = (bool) $task->is_urgent_task;
        $awayMode = Setting::isStrategistAway();

        $newStatus = $isUrgentTask ? 'completed' : ($awayMode ? 'pending_approval' : 'review');

        $updateData = [
            'status'       => $newStatus,
            'submitted_at' => now(),
        ];

        if ($isUrgentTask) {
            $updateData['completed_at'] = now();
        }

        $task->update($updateData);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => $isUrgentTask
                ? 'Marked urgent task as delivered to client'
                : ($awayMode
                ? 'Submitted task directly for admin approval (away mode)'
                : 'Submitted task for review'),
            'old_values' => ['status' => $oldStatus],
            'new_values' => $isUrgentTask
                ? ['status' => $newStatus, 'completed_at' => $task->completed_at]
                : ['status' => $newStatus],
            'ip_address' => $request->ip(),
        ]);

        if ($isUrgentTask) {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id'  => $admin->id,
                    'task_id'  => $task->id,
                    'icon'     => '📦',
                    'title'    => 'Urgent task delivered',
                    'subtitle' => Auth::user()->name . ' marked "' . $task->title . '" as delivered to client',
                    'link'     => route('admin.tasks.show', $task),
                ]);
            }
        } elseif ($awayMode) {
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
            if ($task->created_by && $task->created_by !== Auth::id()) {
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

        $msg = $isUrgentTask
            ? 'Urgent task marked as delivered to client.'
            : ($awayMode
            ? 'Task submitted directly for admin approval (strategist away mode).'
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
        abort_if($this->isTaskLocked($task), 403, 'Complete the previous task first.');

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

        try {
            $files = $request->file('media');
            $uploadedCount = 0;
            $failedFiles = [];
            $collection = 'task-media';

            foreach ($files as $file) {
                try {
                    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                    $filename = time() . '_' . uniqid() . '_' . $safeName;

                    $stored = $file->storeAs($collection, $filename, 'public');

if (!$stored) {
    $failedFiles[] = $file->getClientOriginalName();
    continue;
}

// Hostinger Fix
if (!file_exists(storage_path($collection))) {
    mkdir(storage_path($collection), 0755, true);
}

$sourceFile = storage_path("app/public/{$collection}/{$filename}");
$targetFile = storage_path("{$collection}/{$filename}");

if (file_exists($sourceFile)) {
    copy($sourceFile, $targetFile);
}

                    $mimeType = $file->getMimeType() ?: 'application/octet-stream';
                    $ext = strtolower($file->getClientOriginalExtension());
                    $isVideo = str_contains($mimeType, 'video') || in_array($ext, ['mp4', 'mov', 'webm', 'mkv', 'avi', 'm4v', '3gp', 'ts', 'ogv', 'wmv', 'flv']);
                    $isImage = str_contains($mimeType, 'image') || in_array($ext, ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'svg', 'bmp']);
                    $type = $isVideo ? 'video' : ($isImage ? 'image' : ($ext === 'pdf' ? 'pdf' : 'document'));

                    Media::create([
                        'model_type' => Task::class,
                        'model_id' => $task->id,
                        'collection_name' => $collection,
                        'name' => $file->getClientOriginalName(),
                        'file_name' => $filename,
                        'mime_type' => $mimeType,
                        'disk' => 'public',
                        'path' => "{$collection}/{$filename}",
                        'size' => $file->getSize(),
                        'type' => $type,
                        'metadata' => [],
                    ]);

                    $uploadedCount++;
                } catch (\Exception $fileException) {
                    $failedFiles[] = $file->getClientOriginalName();
                }
            }

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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'uploaded_count' => $uploadedCount,
                    'failed_count' => count($failedFiles),
                ]);
            }

            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            $message = 'Failed to upload: ' . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return back()->with('error', $message);
        }
    }

    public function deleteMedia(Request $request, Task $task)
    {
        abort_if($task->assigned_to !== Auth::id(), 403);

        $request->validate([
            'media_id' => ['required', 'integer'],
        ]);

        try {
            $media = Media::find($request->input('media_id'));

            if (!$media || $media->model_type !== Task::class || $media->model_id !== $task->id) {
                $message = 'Media not found or does not belong to this task.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 404);
                }
                return back()->with('error', $message);
            }

            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }

            $extraFile = storage_path($media->path);

if (file_exists($extraFile)) {
    unlink($extraFile);
}
            $media->delete();

            $message = 'Media removed successfully.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', $message);
        } catch (\Exception $e) {
            $message = 'Failed to delete media: ' . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return back()->with('error', $message);
        }
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
}
