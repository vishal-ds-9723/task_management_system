<?php

namespace App\Services\Strategist;

use App\Models\Task;
use App\Models\Client;
use App\Models\User;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Media;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Http\Resources\TaskResource;

class TaskService
{
    public function createTask(array $validated)
    {
        // Process platform links if platforms exist
        if (!empty($validated['platform']) && is_array($validated['platform'])) {
            if (empty($validated['client_social_media_link_id'])) {
                // Logic to set client_social_media_link_id from request
                // This requires access to full request, so handled in controller
            }
        }

        $validated['created_by'] = Auth::id();
        $validated['reference_links'] = $validated['reference_links'] ?? null;

        // Boolean conversions
        $validated['logo_received'] = isset($validated['logo_received']) ? (bool) $validated['logo_received'] : false;
        $validated['images_received'] = isset($validated['images_received']) ? (bool) $validated['images_received'] : false;
        $validated['content_received'] = isset($validated['content_received']) ? (bool) $validated['content_received'] : false;
        $validated['has_wireframes'] = isset($validated['has_wireframes']) ? (bool) $validated['has_wireframes'] : false;
        $validated['domain_purchased'] = isset($validated['domain_purchased']) ? (bool) $validated['domain_purchased'] : false;
        $validated['hosting_access'] = isset($validated['hosting_access']) ? (bool) $validated['hosting_access'] : false;

        $type = strtolower((string) ($validated['type'] ?? ''));
        if (!in_array($type, ['website', 'software'], true) && !empty($validated['post_date'])) {
            $postDate = Carbon::parse($validated['post_date'])->startOfDay();
            if ($postDate->isToday()) {
                $validated['deadline'] = $postDate->toDateString();
            }
        }

        return Task::create($validated);
    }

    public function updateTask(Task $task, array $validated)
    {
        $oldAssignee = $task->assigned_to;
        $oldPostDate = $task->post_date ? Carbon::parse($task->post_date)->format('Y-m-d') : null;
        $oldDeadline = $task->deadline ? Carbon::parse($task->deadline)->format('Y-m-d') : null;
        $oldDevDeadline = $task->dev_deadline ? Carbon::parse($task->dev_deadline)->format('Y-m-d') : null;
        $oldLaunchDate = $task->launch_date ? Carbon::parse($task->launch_date)->format('Y-m-d') : null;

        $task->update($validated);

        $newPostDate = $task->post_date ? Carbon::parse($task->post_date)->format('Y-m-d') : null;
        $newDeadline = $task->deadline ? Carbon::parse($task->deadline)->format('Y-m-d') : null;
        $newDevDeadline = $task->dev_deadline ? Carbon::parse($task->dev_deadline)->format('Y-m-d') : null;
        $newLaunchDate = $task->launch_date ? Carbon::parse($task->launch_date)->format('Y-m-d') : null;

        $dateChanged = ($oldPostDate !== $newPostDate)
            || ($oldDeadline !== $newDeadline)
            || ($oldDevDeadline !== $newDevDeadline)
            || ($oldLaunchDate !== $newLaunchDate);

        if ($dateChanged) {
            $changedParts = [];
            if ($oldPostDate !== $newPostDate) {
                $changedParts[] = 'Post Date: ' . ($oldPostDate ? Carbon::parse($oldPostDate)->format('d M Y') . ' → ' : '') . ($newPostDate ? Carbon::parse($newPostDate)->format('d M Y') : 'None');
            }
            if ($oldDeadline !== $newDeadline) {
                $changedParts[] = 'Deadline: ' . ($oldDeadline ? Carbon::parse($oldDeadline)->format('d M Y') . ' → ' : '') . ($newDeadline ? Carbon::parse($newDeadline)->format('d M Y') : 'None');
            }
            if ($oldDevDeadline !== $newDevDeadline) {
                $changedParts[] = 'Dev Deadline: ' . ($oldDevDeadline ? Carbon::parse($oldDevDeadline)->format('d M Y') . ' → ' : '') . ($newDevDeadline ? Carbon::parse($newDevDeadline)->format('d M Y') : 'None');
            }
            if ($oldLaunchDate !== $newLaunchDate) {
                $changedParts[] = 'Launch Date: ' . ($oldLaunchDate ? Carbon::parse($oldLaunchDate)->format('d M Y') . ' → ' : '') . ($newLaunchDate ? Carbon::parse($newLaunchDate)->format('d M Y') : 'None');
            }

            $changerName = Auth::user()->name ?? 'Strategist';
            $dateSubtitle = $changerName . ' changed ' . implode(', ', $changedParts) . ' for "' . $task->title . '"';

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id'  => $admin->id,
                    'task_id'  => $task->id,
                    'icon'     => '📅',
                    'title'    => 'Task date changed by strategist',
                    'subtitle' => $dateSubtitle,
                    'link'     => '/admin/tasks/' . $task->id,
                ]);
            }
        }

        if (!empty($validated['assigned_to']) && $validated['assigned_to'] != $oldAssignee) {
            $this->notifyReassignment($validated['assigned_to'], $task);
        }
    }

    public function reassignTask(Task $task, int $userId)
    {
        $oldAssignee = $task->assigned_to;
        $task->update(['assigned_to' => $userId]);

        if ($userId != $oldAssignee) {
            $this->notifyReassignment($userId, $task);
        }
    }

    protected function notifyReassignment(int $userId, Task $task)
    {
        $assignedUser = User::find($userId);
        $link = '/designer/tasks';
        if ($assignedUser && $assignedUser->role === 'developer') {
            $link = '/developer/tasks';
        }

        Notification::create([
            'user_id' => $userId,
            'task_id' => $task->id,
            'icon'    => '🔄',
            'title'   => 'Task reassigned to you',
            'subtitle' => $task->title,
            'link'    => $link,
        ]);
    }

    public function addComment(Task $task, array $validated)
    {
        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body'    => $validated['body'],
        ]);

        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'icon'    => '💬',
                'title'   => 'New comment on task',
                'subtitle' => Auth::user()->name . ' commented on "' . $task->title . '"',
                'link'    => '/designer/tasks/' . $task->id,
            ]);
        }

        return $comment;
    }

    public function updateCaptionHashtags(Task $task, array $validated)
    {
        $task->update([
            'caption'  => $validated['caption'] ?? $task->caption,
            'hashtags' => $validated['hashtags'] ?? $task->hashtags,
        ]);

        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'icon'    => '📕',
                'title'   => 'Caption/hashtags updated',
                'subtitle' => Auth::user()->name . ' updated "' . $task->title . '"',
                'link'    => '/designer/tasks/' . $task->id,
            ]);
        }
    }

    public function requestDateChange(Task $task, array $validated)
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id'  => $admin->id,
                'task_id'  => $task->id,
                'icon'     => '📅',
                'title'    => 'Post date change requested',
                'subtitle' => Auth::user()->name . ' requests "' . $task->title . '" post date → ' . $validated['requested_date'],
                'link'     => '/admin/tasks/' . $task->id,
            ]);
        }

        Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body'    => '📅 Requested post date change to ' . $validated['requested_date'] . '. Reason: ' . $validated['reason'],
        ]);
    }

    public function updateHashtagsOnly(Task $task, array $validated)
    {
        $task->update(['hashtags' => $validated['hashtags']]);
    }

    public function approveTask(Task $task, Request $request)
    {
        $isDeveloperTask = in_array($task->type, ['software', 'website']);

        if (!$isDeveloperTask && (empty($task->caption) || empty($task->hashtags))) {
            $errorMsg = 'Please add caption and hashtags before approving.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return back()->with('error', $errorMsg);
        }

        $task->update(['status' => 'pending_approval']);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'task_id' => $task->id,
                'icon'    => '👀',
                'title'   => 'Task ready for approval',
                'subtitle' => 'Strategist approved "' . $task->title . '" - review media and approve to complete',
                'link'    => route('admin.approvals.show', $task),
            ]);
        }
    }

    public function requestRevision(Task $task, array $validated)
    {
        $oldStatus = $task->status;
        $task->update([
            'status'         => 'todo',
            'revision_count' => $task->revision_count + 1,
        ]);

        Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body'    => '🔄 Revision requested: ' . $validated['revision_note'],
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => 'revision_requested',
            'old_values' => ['status' => $oldStatus, 'revision_count' => $task->revision_count - 1],
            'new_values' => ['status' => 'inprogress', 'revision_count' => $task->revision_count, 'note' => $validated['revision_note']],
        ]);

        if ($task->assigned_to) {
            $assignedUser = User::find($task->assigned_to);
            $link = '/designer/tasks/' . $task->id;
            if ($assignedUser && $assignedUser->role === 'developer') {
                $link = '/developer/tasks';
            }

            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'icon'    => '🔄',
                'title'   => 'Revision requested',
                'subtitle' => Auth::user()->name . ' requested changes on "' . $task->title . '"',
                'link'    => $link,
            ]);
        }
    }

    public function uploadMedia(Task $task, Request $request)
    {
        $request->validate([
            'media'   => ['required', 'array'],
            'media.*' => ['required', 'file', 'mimes:jpg,jpeg,jfif,png,gif,webp,svg,mp4,mov,webm,mkv,avi,m4v,3gp,ts,pdf,doc,docx,zip,rar,7z', 'max:512000'],
        ]);

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

                $mimeType = $file->getMimeType() ?: 'application/octet-stream';
                $ext = strtolower($file->getClientOriginalExtension());
                $isVideo = str_contains($mimeType, 'video') || in_array($ext, ['mp4', 'mov', 'webm', 'mkv', 'avi', 'm4v', '3gp', 'ts', 'ogv', 'wmv', 'flv']);
                $isImage = str_contains($mimeType, 'image') || in_array($ext, ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'svg', 'bmp']);
                $type = $isVideo ? 'video' : ($isImage ? 'image' : ($ext === 'pdf' ? 'pdf' : 'document'));

                Media::create([
                    'model_type'      => Task::class,
                    'model_id'        => $task->id,
                    'collection_name' => $collection,
                    'name'            => $file->getClientOriginalName(),
                    'file_name'       => $filename,
                    'mime_type'       => $mimeType,
                    'disk'            => 'public',
                    'path'            => "{$collection}/{$filename}",
                    'size'            => $file->getSize(),
                    'type'            => $type,
                    'metadata'        => [],
                ]);

                $uploadedCount++;
            } catch (\Exception $e) {
                $failedFiles[] = $file->getClientOriginalName();
            }
        }

        if ($uploadedCount === 0) {
            return response()->json(['success' => false, 'message' => 'All files failed.'], 422);
        }

        $successMessage = "$uploadedCount file(s) uploaded successfully";
        if (!empty($failedFiles)) {
            $successMessage .= '. Failed: ' . implode(', ', $failedFiles);
        }

        return response()->json([
            'success' => true,
            'message' => $successMessage,
            'uploaded_count' => $uploadedCount,
            'failed_count' => count($failedFiles),
        ]);
    }

    public function deleteMedia(Task $task, int $mediaId)
    {
        $media = Media::findOrFail($mediaId);

        if ($media->model_type !== Task::class || $media->model_id !== $task->id) {
            abort(403);
        }

        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return response()->json(['success' => true, 'message' => 'Media removed.']);
    }

    public function getTrackingData(Request $request)
    {
        $perPage = $request->input('per_page', 25);
        // ... rest of method

        $query = Task::with(['client', 'assignee', 'comments', 'creator'])
            ->whereHas('creator', fn($q) => $q->where('role', 'strategist'));

        // Filters...
        // (all filter logic)

        $tasks = $query->paginate($perPage)->withQueryString();

        $stats = $this->getTrackingStats();

        return compact('tasks', 'clients', 'designers', 'stats');
    }

    protected function getTrackingStats()
    {
        $allTasks = Task::whereHas('creator', fn($q) => $q->where('role', 'strategist'));
        return [
            'totalCount' => $allTasks->count(),
            'todoCount'  => $allTasks->where('status', 'todo')->count(),
            // ... other stats
        ];
    }

    public function putOnHold(Task $task, array $validated)
    {
        $task->update([
            'status_before_hold' => $task->status,
            'status' => 'on_hold',
            'on_hold_reason' => $validated['reason'],
            'on_hold_since' => now(),
        ]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => 'placed_on_hold',
            'new_values' => ['reason' => $validated['reason']],
        ]);
        
        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'icon'    => '⏸️',
                'title'   => 'Task put on hold',
                'subtitle' => Auth::user()->name . ' put "' . $task->title . '" on hold.',
                'link'    => '/designer/tasks/' . $task->id,
            ]);
        }
    }

    public function resumeFromHold(Task $task)
    {
        $daysOnHold = 0;
        if ($task->on_hold_since) {
            $daysOnHold = max(0, (int) $task->on_hold_since->diffInDays(now()));
        }

        $updates = [
            'status' => $task->status_before_hold ?: 'todo',
            'status_before_hold' => null,
            'on_hold_reason' => null,
            'on_hold_since' => null,
        ];

        // Shift deadlines
        if ($daysOnHold > 0) {
            if ($task->deadline) $updates['deadline'] = $task->deadline->addDays($daysOnHold);
            if ($task->dev_deadline) $updates['dev_deadline'] = $task->dev_deadline->addDays($daysOnHold);
            if ($task->design_deadline) $updates['design_deadline'] = $task->design_deadline->addDays($daysOnHold);
            if ($task->post_date) $updates['post_date'] = $task->post_date->addDays($daysOnHold);
            if ($task->launch_date) $updates['launch_date'] = $task->launch_date->addDays($daysOnHold);
            if ($task->project_start_date) $updates['project_start_date'] = $task->project_start_date->addDays($daysOnHold);
        }

        $task->update($updates);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => 'resumed_from_hold',
            'new_values' => ['days_shifted' => $daysOnHold],
        ]);
        
        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'icon'    => '▶️',
                'title'   => 'Task resumed from hold',
                'subtitle' => Auth::user()->name . ' resumed "' . $task->title . '". Deadlines shifted by ' . $daysOnHold . ' days.',
                'link'    => '/designer/tasks/' . $task->id,
            ]);
        }
    }

    public function createMaintenanceTask(Task $task, array $validated)
    {
        $newTask = $task->replicate([
            'status', 'status_before_hold', 'on_hold_reason', 'on_hold_since', 
            'started_at', 'submitted_at', 'completed_at', 'admin_approved_at', 
            'admin_approved_by', 'admin_approval_status', 'revision_count', 
            'total_paused_seconds', 'is_paused', 'paused_at', 'pause_reason',
            'dev_submission_link', 'dev_submission_notes', 'caption', 'hashtags', 'reference_links'
        ]);

        $newTask->parent_task_id = $task->id;
        $newTask->type = 'maintenance';
        $newTask->status = 'todo';
        $newTask->title = "Maintenance: " . $task->title;
        $newTask->brief = $validated['brief'];
        $newTask->priority = $validated['priority'] ?? $task->priority;
        $newTask->deadline = $validated['deadline'] ?? null;
        $newTask->dev_deadline = $validated['dev_deadline'] ?? null;
        if (isset($validated['assigned_to'])) {
            $newTask->assigned_to = $validated['assigned_to'];
        }
        $newTask->created_by = Auth::id();
        
        $newTask->save();

        if ($newTask->assigned_to) {
            $this->notifyReassignment($newTask->assigned_to, $newTask);
        }

        return $newTask;
    }
}

