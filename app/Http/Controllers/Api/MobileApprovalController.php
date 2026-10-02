<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobileApprovalController extends Controller
{
    /**
     * Get pending approvals queue.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $query = Task::with(['client', 'assignee', 'creator', 'media'])
            ->whereIn('status', ['review', 'pending_approval']);

        if ($request->has('client_id') && !empty($request->client_id)) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)->orWhere('brief', 'like', $term);
            });
        }

        $tasks = $query->latest('submitted_at')->get();

        return response()->json([
            'success' => true,
            'count' => $tasks->count(),
            'approvals' => $tasks,
        ]);
    }

    /**
     * Get approval details.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $task = Task::with(['client', 'assignee', 'creator', 'comments.user', 'media'])
            ->findOrFail($id);

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
            'media' => $mediaItems,
        ]);
    }

    /**
     * Approve task.
     */
    public function approve(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $task = Task::findOrFail($id);
        $oldStatus = $task->status;

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'admin_approved_by' => $user->id,
            'admin_approved_at' => now(),
            'admin_approval_status' => 'approved',
        ]);

        // Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'action' => 'approved_task',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'completed', 'approved_by' => $user->name],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notification to assignee
        if ($task->assigned_to && $task->assigned_to != $user->id) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'title' => 'Task Approved 🎉',
                'subtitle' => "{$user->name} approved your task: {$task->title}",
                'icon' => '✅',
                'link' => "/tasks/{$task->id}",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task approved successfully',
            'task' => $task,
        ]);
    }

    /**
     * Request revision.
     */
    public function reject(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide revision feedback reason',
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = Task::findOrFail($id);
        $oldStatus = $task->status;

        $task->update([
            'status' => 'inprogress',
            'revision_count' => ($task->revision_count ?? 0) + 1,
            'admin_approval_status' => 'revision_requested',
        ]);

        // Add feedback comment
        Comment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => "⚠️ REVISION REQUESTED:\n" . $request->reason,
        ]);

        // Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'action' => 'revision_requested',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'inprogress', 'reason' => $request->reason],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notification to assignee
        if ($task->assigned_to && $task->assigned_to != $user->id) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'title' => 'Revision Requested ⚠️',
                'subtitle' => "{$user->name} requested revisions: {$request->reason}",
                'icon' => '🔄',
                'link' => "/tasks/{$task->id}",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Revision requested and sent to assignee',
            'task' => $task,
        ]);
    }
}
