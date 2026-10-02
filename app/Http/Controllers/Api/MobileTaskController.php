<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Task;
use App\Models\TaskPause;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobileTaskController extends Controller
{
    /**
     * Get paginated tasks with multi-filtering and search.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Task::with(['client', 'assignee', 'creator', 'media']);

        // Role-based filtering
        if ($user->isClient()) {
            $query->where('client_id', $user->client_id);
        } elseif ($user->isDesigner() || $user->isDeveloper()) {
            $scope = $request->query('scope', 'my');
            if ($scope !== 'all' && $request->query('all') !== 'true') {
                $query->where('assigned_to', $user->id);
            }
        } elseif ($user->isStrategist()) {
            $scope = $request->query('scope', 'all');
            if ($scope === 'my') {
                $query->where('created_by', $user->id);
            }
        }

        // Filters
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && !empty($request->priority)) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('client_id') && !empty($request->client_id)) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('creator_id') && !empty($request->creator_id)) {
            $query->where('created_by', $request->creator_id);
        }

        if ($request->has('assigned_to') && !empty($request->assigned_to)) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        if ($request->has('platform') && !empty($request->platform)) {
            $query->whereJsonContains('platform', $request->platform);
        }

        if ($request->has('urgent') && $request->urgent === 'true') {
            $query->where('is_urgent_task', true);
        }

        if ($request->has('search') && !empty($request->search)) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('brief', 'like', $term)
                  ->orWhereHas('client', fn($c) => $c->where('name', 'like', $term))
                  ->orWhereHas('assignee', fn($a) => $a->where('name', 'like', $term));
            });
        }

        $tasks = $query->orderBy('priority', 'desc')
                       ->orderBy('deadline', 'asc')
                       ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $tasks->items(),
            'current_page' => $tasks->currentPage(),
            'last_page' => $tasks->lastPage(),
            'total' => $tasks->total(),
        ]);
    }

    /**
     * Create new task.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'assigned_to' => 'nullable|exists:users,id',
            'type' => 'nullable|string',
            'priority' => 'nullable|string|in:normal,high,urgent',
            'platform' => 'nullable|string',
            'deadline' => 'nullable|date',
            'post_date' => 'nullable|date',
            'brief' => 'nullable|string',
            'caption' => 'nullable|string',
            'hashtags' => 'nullable|string',
            'is_urgent_task' => 'nullable|boolean',
            'tech_stack' => 'nullable|string',
            'client_social_media_link_id' => 'nullable|exists:client_social_media_links,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $isUrgent = $request->boolean('is_urgent_task', false) || $request->priority === 'urgent';

        $task = Task::create([
            'title' => $request->title,
            'client_id' => $request->client_id,
            'client_social_media_link_id' => $request->client_social_media_link_id,
            'assigned_to' => $request->assigned_to,
            'created_by' => $user->id,
            'type' => $request->type ?: 'post',
            'priority' => $request->priority ?: 'normal',
            'status' => 'todo',
            'is_urgent_task' => $isUrgent,
            'urgent_requested_by' => $isUrgent ? $user->name : null,
            'platform' => [$request->platform ?: 'instagram'],
            'brief' => $request->brief,
            'caption' => $request->caption,
            'hashtags' => $request->hashtags,
            'deadline' => $request->deadline,
            'post_date' => $request->post_date,
            'tech_stack' => $request->tech_stack,
        ]);

        // Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'action' => 'created_task',
            'new_values' => ['title' => $task->title, 'priority' => $task->priority, 'assigned_to' => $task->assigned_to],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notification to assignee
        if ($task->assigned_to && $task->assigned_to != $user->id) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'title' => 'New Task Assigned 📋',
                'subtitle' => "{$user->name} assigned you: {$task->title}",
                'icon' => '📌',
                'link' => "/tasks/{$task->id}",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'task' => $task->fresh(['client', 'assignee', 'creator', 'media']),
        ], 201);
    }

    /**
     * Get single task details.
     */
    public function show(Request $request, $id)
    {
        $task = Task::with(['client', 'assignee', 'creator', 'childTasks', 'media'])->find($id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $comments = Comment::with('user')
            ->where('task_id', $task->id)
            ->latest()
            ->get()
            ->map(function ($c) {
                $data = $c->toArray();
                $data['content'] = $c->body;
                return $data;
            });

        $pauses = TaskPause::where('task_id', $task->id)
            ->latest()
            ->get();

        $mediaItems = $task->media->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'file_name' => $m->file_name,
            'mime_type' => $m->mime_type,
            'type' => $m->type ?: ($m->isImage() ? 'image' : ($m->isVideo() ? 'video' : 'document')),
            'size' => $m->size,
            'url' => $m->url,
            'thumb_url' => $m->thumb_url,
            'is_image' => $m->isImage(),
            'is_video' => $m->isVideo(),
        ]);

        return response()->json([
            'success' => true,
            'task' => $task,
            'comments' => $comments,
            'pauses' => $pauses,
            'media' => $mediaItems,
        ]);
    }

    /**
     * Update task.
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isStrategist() && $task->assigned_to != $user->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $oldValues = $task->only(['title', 'status', 'priority', 'assigned_to', 'deadline', 'post_date']);

        $data = $request->only([
            'title', 'client_id', 'assigned_to', 'type', 'priority', 'status',
            'platform', 'deadline', 'post_date', 'brief', 'caption', 'hashtags',
            'tech_stack', 'is_urgent_task',
        ]);

        if (isset($data['platform']) && !is_array($data['platform'])) {
            $data['platform'] = [$data['platform']];
        }

        $task->update($data);

        // Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'action' => 'updated_task',
            'old_values' => $oldValues,
            'new_values' => $task->only(['title', 'status', 'priority', 'assigned_to', 'deadline', 'post_date']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // If dates changed by strategist or non-admin, notify admins
        if (!$user->isAdmin()) {
            $oldPostDate = !empty($oldValues['post_date']) ? Carbon::parse($oldValues['post_date'])->format('Y-m-d') : null;
            $newPostDate = $task->post_date ? Carbon::parse($task->post_date)->format('Y-m-d') : null;
            $oldDeadline = !empty($oldValues['deadline']) ? Carbon::parse($oldValues['deadline'])->format('Y-m-d') : null;
            $newDeadline = $task->deadline ? Carbon::parse($task->deadline)->format('Y-m-d') : null;

            if ($oldPostDate !== $newPostDate || $oldDeadline !== $newDeadline) {
                $changedParts = [];
                if ($oldPostDate !== $newPostDate) {
                    $changedParts[] = 'Post Date: ' . ($oldPostDate ? Carbon::parse($oldPostDate)->format('d M Y') . ' → ' : '') . ($newPostDate ? Carbon::parse($newPostDate)->format('d M Y') : 'None');
                }
                if ($oldDeadline !== $newDeadline) {
                    $changedParts[] = 'Deadline: ' . ($oldDeadline ? Carbon::parse($oldDeadline)->format('d M Y') . ' → ' : '') . ($newDeadline ? Carbon::parse($newDeadline)->format('d M Y') : 'None');
                }

                $dateSubtitle = $user->name . ' changed ' . implode(', ', $changedParts) . ' for "' . $task->title . '"';

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
        }

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'task' => $task->fresh(['client', 'assignee', 'creator']),
        ]);
    }

    /**
     * Delete task.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $task = Task::findOrFail($id);
        $taskTitle = $task->title;

        AuditLog::create([
            'user_id' => $user->id,
            'task_id' => null,
            'action' => 'deleted_task',
            'old_values' => ['title' => $taskTitle, 'id' => $id],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Reassign task.
     */
    public function reassign(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $oldAssignee = $task->assigned_to;
        $task->assigned_to = $request->assigned_to;
        $task->save();

        // Notification
        if ($task->assigned_to != $user->id) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'title' => 'Task Reassigned to You 📋',
                'subtitle' => "{$user->name} reassigned: {$task->title}",
                'icon' => '📌',
                'link' => "/tasks/{$task->id}",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task reassigned successfully',
            'task' => $task->fresh(['client', 'assignee']),
        ]);
    }

    /**
     * Get urgent tasks feed.
     */
    public function urgentTasks(Request $request)
    {
        $tasks = Task::with(['client', 'assignee'])
            ->where('is_urgent_task', true)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Get date change requests.
     */
    public function dateChangeRequests(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $notifications = Notification::with(['task.client', 'task.assignee'])
            ->where('title', 'like', '%date change%')
            ->whereNull('read_at')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'requests' => $notifications,
        ]);
    }

    /**
     * Approve date change.
     */
    public function approveDate(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $task = Task::findOrFail($id);

        if ($request->filled('new_post_date')) {
            $task->post_date = $request->new_post_date;
        }
        if ($request->filled('new_deadline')) {
            $task->deadline = $request->new_deadline;
        }
        $task->save();

        // Mark related notifications read
        Notification::where('task_id', $task->id)->where('title', 'like', '%date change%')->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Date change approved',
            'task' => $task->fresh(['client', 'assignee']),
        ]);
    }

    /**
     * Reject date change.
     */
    public function rejectDate(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $task = Task::findOrFail($id);

        // Mark related notifications read
        Notification::where('task_id', $task->id)->where('title', 'like', '%date change%')->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Date change rejected',
        ]);
    }

    /**
     * Start task.
     */
    public function start(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        if ($task->status === 'todo') {
            $task->status = 'inprogress';
            $task->started_at = now();
            $task->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Task started',
            'task' => $task->fresh(['client', 'assignee']),
        ]);
    }

    /**
     * Pause task timer.
     */
    public function pause(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $task->is_paused = true;
        $task->paused_at = now();
        $task->pause_reason = $request->reason ?? 'Paused by user';
        $task->save();

        TaskPause::create([
            'task_id' => $task->id,
            'paused_at' => now(),
            'reason' => $request->reason ?? 'Paused',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task paused',
            'task' => $task->fresh(['client', 'assignee']),
        ]);
    }

    /**
     * Resume paused task.
     */
    public function resume(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        if ($task->is_paused) {
            $lastPause = TaskPause::where('task_id', $task->id)->whereNull('resumed_at')->latest()->first();
            if ($lastPause) {
                $lastPause->resumed_at = now();
                $seconds = Carbon::parse($lastPause->paused_at)->diffInSeconds(now());
                $lastPause->duration_seconds = $seconds;
                $lastPause->save();

                $task->total_paused_seconds = ($task->total_paused_seconds ?? 0) + $seconds;
            }

            $task->is_paused = false;
            $task->paused_at = null;
            $task->pause_reason = null;
            $task->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Task resumed',
            'task' => $task->fresh(['client', 'assignee']),
        ]);
    }

    /**
     * Submit task for review.
     */
    public function submit(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $task->status = 'review';
        $task->submitted_at = now();
        if ($request->has('dev_submission_link')) {
            $task->dev_submission_link = $request->dev_submission_link;
        }
        if ($request->has('dev_submission_notes')) {
            $task->dev_submission_notes = $request->dev_submission_notes;
        }
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Task submitted for review',
            'task' => $task->fresh(['client', 'assignee']),
        ]);
    }

    /**
     * Approve task.
     */
    public function approve(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = $request->user();

        if ($user->isAdmin()) {
            $task->status = 'completed';
            $task->completed_at = now();
            $task->admin_approved_by = $user->id;
            $task->admin_approved_at = now();
            $task->admin_approval_status = 'approved';
        } else {
            $task->status = 'pending_approval';
        }
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Task approved successfully',
            'task' => $task->fresh(['client', 'assignee']),
        ]);
    }

    /**
     * Request revision / reject task.
     */
    public function requestRevision(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $task->status = 'inprogress';
        $task->revision_count = ($task->revision_count ?? 0) + 1;
        $task->save();

        if ($request->filled('feedback')) {
            Comment::create([
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'body' => 'Revision requested: ' . $request->feedback,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Revision requested',
            'task' => $task->fresh(['client', 'assignee']),
        ]);
    }

    /**
     * Add comment to task.
     */
    public function addComment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $comment = Comment::create([
            'task_id' => $id,
            'user_id' => $request->user()->id,
            'body' => $request->input('content'),
        ]);

        $comment->load('user');
        $formatted = $comment->toArray();
        $formatted['content'] = $comment->body;

        return response()->json([
            'success' => true,
            'comment' => $formatted,
        ]);
    }

    /**
     * Upload media (images/videos) to task.
     */
    public function uploadMedia(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'media' => 'required',
        ]);

        $files = $request->file('media');
        if (!is_array($files)) {
            $files = [$files];
        }

        $collection = 'task-media';
        $uploaded = [];

        foreach ($files as $file) {
            if (!$file) continue;
            try {
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $filename = time() . '_' . uniqid() . '_' . $safeName;
                $stored = $file->storeAs($collection, $filename, 'public');

                if ($stored) {
                    $mimeType = $file->getMimeType();
                    $type = str_contains($mimeType, 'image') ? 'image' : (str_contains($mimeType, 'video') ? 'video' : 'document');
                    $m = \App\Models\Media::create([
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
                    $uploaded[] = $m;
                }
            } catch (\Exception $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => count($uploaded) . ' file(s) uploaded successfully',
            'media' => $task->fresh('media')->media,
        ]);
    }

    /**
     * Delete media item.
     */
    public function deleteMedia(Request $request, $id, $mediaId)
    {
        $media = \App\Models\Media::where('id', $mediaId)
            ->where('model_type', Task::class)
            ->where('model_id', $id)
            ->firstOrFail();

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($media->path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media removed successfully',
        ]);
    }

    /**
     * Helper to resolve accurate mime type for streaming & preview.
     */
    private function resolveMimeType($filePath, $fallbackMime = null)
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $map = [
            'mp4'  => 'video/mp4',
            'mov'  => 'video/quicktime',
            'webm' => 'video/webm',
            'mkv'  => 'video/x-matroska',
            'm4v'  => 'video/x-m4v',
            'avi'  => 'video/x-msvideo',
            '3gp'  => 'video/3gpp',
            'ts'   => 'video/mp2t',
            'ogv'  => 'video/ogg',
            'wmv'  => 'video/x-ms-wmv',
            'flv'  => 'video/x-flv',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jfif' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'pdf'  => 'application/pdf',
        ];

        if (isset($map[$ext])) {
            return $map[$ext];
        }

        if ($fallbackMime && $fallbackMime !== 'application/octet-stream') {
            return $fallbackMime;
        }

        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($filePath);
            if ($detected && $detected !== 'application/octet-stream') {
                return $detected;
            }
        }

        return $fallbackMime ?: 'application/octet-stream';
    }

    /**
     * Stream media file by Media ID with cross-origin and inline headers.
     */
    public function viewMedia(Request $request, $id)
    {
        $media = \App\Models\Media::findOrFail($id);
        $cleanPath = str_replace('\\', '/', $media->path);

        $candidates = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            storage_path($cleanPath),
            public_path($cleanPath),
        ];

        $filePath = null;
        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if (!$filePath) {
            abort(404, 'Media file not found');
        }

        $mime = $this->resolveMimeType($filePath, $media->mime_type);
        $downloadName = $media->name ?: $media->file_name ?: basename($filePath);

        return response()->file($filePath, [
            'Content-Type'                => $mime,
            'Content-Disposition'         => 'inline; filename="' . addslashes($downloadName) . '"',
            'Accept-Ranges'               => 'bytes',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods'=> 'GET, OPTIONS',
            'Cache-Control'               => 'public, max-age=86400',
        ]);
    }

    /**
     * Stream media file by relative path with cross-origin and inline headers.
     */
    public function viewMediaFile(Request $request, $path)
    {
        $cleanPath = str_replace('\\', '/', $path);
        $cleanPath = preg_replace('#^/?(storage/|app/public/)?#', '', $cleanPath);

        $candidates = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            storage_path($cleanPath),
            public_path($cleanPath),
        ];

        $filePath = null;
        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if (!$filePath) {
            abort(404, 'File not found');
        }

        $mime = $this->resolveMimeType($filePath);
        $downloadName = basename($filePath);

        return response()->file($filePath, [
            'Content-Type'                => $mime,
            'Content-Disposition'         => 'inline; filename="' . addslashes($downloadName) . '"',
            'Accept-Ranges'               => 'bytes',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods'=> 'GET, OPTIONS',
            'Cache-Control'               => 'public, max-age=86400',
        ]);
    }
}
