<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\DailyWorkLog;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function dashboard()
    {
        $developer = Auth::user();

        // Get assigned tasks
        $tasks = Task::where('assigned_to', $developer->id)
            ->with(['client', 'assignee', 'creator'])
            ->latest()
            ->paginate(10);

        // Get stats
        $totalTasks = Task::where('assigned_to', $developer->id)->count();
        $inProgressTasks = Task::where('assigned_to', $developer->id)
            ->where('status', 'inprogress')
            ->count();
        $completedTasks = Task::where('assigned_to', $developer->id)
            ->where('status', 'completed')
            ->count();

        // Deadlines only matter while the work is still with the developer
        $openTasks = Task::where('assigned_to', $developer->id)
            ->whereIn('status', ['todo', 'inprogress'])
            ->whereNotNull('dev_deadline');

        $upcomingDeadlines = (clone $openTasks)
            ->whereBetween('dev_deadline', [today()->toDateString(), today()->addDays(7)->toDateString()])
            ->count();
        $overdueTasks = (clone $openTasks)
            ->where('dev_deadline', '<', today()->toDateString())
            ->count();
        $deadlineTasks = (clone $openTasks)
            ->orderBy('dev_deadline')
            ->limit(5)
            ->get();

        $todayLog = DailyWorkLog::where('user_id', $developer->id)
            ->whereDate('log_date', today())
            ->withSum('entries', 'hours')
            ->first();

        return view('developer.dashboard', compact(
            'tasks', 'totalTasks', 'inProgressTasks', 'completedTasks',
            'upcomingDeadlines', 'overdueTasks', 'deadlineTasks', 'todayLog'
        ));
    }

    public function tasks(Request $request)
    {
        $developer = Auth::user();
        $scope = $request->get('scope', 'my');

        $query = Task::with(['client', 'assignee', 'creator', 'comments.user'])
            ->withSum('workLogEntries as logged_hours', 'hours');

        if ($scope !== 'all') {
            $query->where('assigned_to', $developer->id);
        } else {
            // "All team tasks" means the development team, not every task in the company
            $query->whereHas('assignee', fn ($q) => $q->where('role', 'developer'));
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

        // Active work first, then waiting, then finished; earliest deadline first, no deadline last
        $tasks = $query->orderByRaw("CASE
                WHEN status = 'inprogress' THEN 1
                WHEN status = 'todo' THEN 2
                WHEN status = 'review' THEN 3
                WHEN status = 'pending_approval' THEN 4
                WHEN status = 'on_hold' THEN 5
                WHEN status = 'completed' THEN 6
                ELSE 7
            END")
            ->orderByRaw('dev_deadline IS NULL, dev_deadline ASC')
            ->get();

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $strategists = User::whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin'])->orderBy('name')->get();
        $developers = User::where('role', 'developer')->orderBy('name')->get();

        return view('developer.tasks', compact('tasks', 'clients', 'strategists', 'developers', 'scope'));
    }

    public function calendar()
    {
        $developer = Auth::user();

        $tasks = Task::where('assigned_to', $developer->id)
            ->with(['client'])
            ->get()
            ->map(function ($task) {
                $task->calendar_date = $task->dev_deadline ?? $task->deadline;
                return $task;
            });

        $ongoingTasks = $tasks->filter(fn ($task) => in_array($task->status, ['todo', 'inprogress']))
            ->sortBy(fn ($task) => $task->calendar_date ?? now()->addYears(10))
            ->values();

        $ongoingTodoCount = $ongoingTasks->where('status', 'todo')->count();
        $ongoingInProgressCount = $ongoingTasks->where('status', 'inprogress')->count();

        $tasksByDate = $tasks
            ->filter(fn ($task) => !empty($task->calendar_date))
            ->groupBy(fn ($task) => $task->calendar_date->format('Y-m-d'));

        return view('developer.calendar', [
            'tasks' => $tasks,
            'ongoingTasks' => $ongoingTasks,
            'ongoingTodoCount' => $ongoingTodoCount,
            'ongoingInProgressCount' => $ongoingInProgressCount,
            'tasksByDate' => $tasksByDate,
        ]);
    }

    public function start(Request $request, Task $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            return $this->failure($request, 'You are not assigned to this task.', 403);
        }

        if ($task->status !== 'todo') {
            return $this->failure($request, 'Task is not in To Do status.', 422);
        }

        $task->status = 'inprogress';
        $task->started_at = now();
        $task->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task started successfully.'
            ]);
        }

        return back()->with('success', 'Task started successfully.');
    }

    public function submit(Request $request, Task $task)
    {
        $request->validate([
            'link' => 'required|url',
            'notes' => 'nullable|string',
        ]);

        if ($task->assigned_to !== Auth::id()) {
            return $this->failure($request, 'You are not assigned to this task.', 403);
        }

        if ($task->status !== 'inprogress') {
            return $this->failure($request, 'Only tasks in progress can be submitted for review.', 422);
        }

        $task->dev_submission_link = $request->link;
        $task->dev_submission_notes = $request->notes;
        $task->status = 'review';
        $task->submitted_at = now();
        $task->save();

        // Notify whoever created the task; fall back to admins when there is no creator
        $recipients = $task->creator
            ? collect([$task->creator])
            : User::where('role', 'admin')->get();

        foreach ($recipients as $user) {
            Notification::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'icon' => 'task',
                'title' => 'New Task Submission',
                'subtitle' => "Developer submitted {$task->title} for review. Link: {$task->dev_submission_link}",
                'link' => $user->role === 'admin'
                    ? route('admin.tasks.show', $task)
                    : route('strategist.tasks.show', $task),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task submitted successfully.'
            ]);
        }

        return back()->with('success', 'Task submitted successfully.');
    }

    public function pauseTask(Request $request, Task $task)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'work_logged' => 'nullable|string|max:2000',
        ]);

        if ($task->assigned_to !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($task->is_paused || !in_array($task->status, ['todo', 'inprogress'])) {
            return response()->json(['success' => false, 'message' => 'Invalid task state for pausing.'], 400);
        }

        $task->is_paused = true;
        $task->paused_at = now();
        $task->pause_reason = $request->reason;
        $task->save();

        \App\Models\TaskPause::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'reason' => $request->reason,
            'work_logged' => $request->work_logged,
        ]);

        return response()->json(['success' => true, 'message' => 'Task paused successfully.']);
    }

    public function resumeTask(Request $request, Task $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$task->is_paused) {
            return response()->json(['success' => false, 'message' => 'Task is not paused.'], 400);
        }

        $pauseRecord = \App\Models\TaskPause::where('task_id', $task->id)
            ->whereNull('resumed_at')
            ->latest()
            ->first();

        if ($pauseRecord) {
            // Carbon 3 diffs are signed: measure from the pause start forward to stay positive
            $duration = (int) $pauseRecord->created_at->diffInSeconds(now());
            $pauseRecord->update([
                'resumed_at' => now(),
                'duration_seconds' => $duration
            ]);

            $task->total_paused_seconds += $duration;
        }

        $task->is_paused = false;
        $task->paused_at = null;
        $task->pause_reason = null;
        $task->save();

        return response()->json(['success' => true, 'message' => 'Task resumed successfully.']);
    }

    public function putOnHold(Request $request, Task $task)
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);

        if ($task->assigned_to !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        app(\App\Services\Strategist\TaskService::class)->putOnHold($task, $validated);

        return response()->json(['success' => true, 'message' => 'Task placed on hold.']);
    }

    public function resumeFromHold(Request $request, Task $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        app(\App\Services\Strategist\TaskService::class)->resumeFromHold($task);

        return response()->json(['success' => true, 'message' => 'Task resumed from hold.']);
    }

    public function addComment(Request $request, Task $task)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        if ($task->assigned_to !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        \App\Models\Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        if ($task->created_by) {
            Notification::create([
                'user_id' => $task->created_by,
                'task_id' => $task->id,
                'icon' => '💬',
                'title' => 'New Comment from Developer',
                'subtitle' => Auth::user()->name . ' commented on "' . $task->title . '"',
                'link' => route('strategist.tracking', ['task' => $task->id]),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added!',
            'user_name' => Auth::user()->name,
            'user_initial' => strtoupper(substr(Auth::user()->name, 0, 1)),
            'user_color' => Auth::user()->avatar_color ?? 'var(--primary)',
            'body' => $request->body,
            'time' => 'just now',
        ]);
    }

    public function uploadMedia(Request $request, Task $task)
    {
        $request->validate([
            'media' => 'required|array',
            'media.*' => 'required|file|max:51200',
        ]);

        if ($task->assigned_to !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $uploadedCount = 0;
        $failedFiles = [];
        $collection = 'task-media';

        foreach ($request->file('media') as $file) {
            try {
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $filename = time() . '_' . uniqid() . '_' . $safeName;
                
                if ($file->storeAs($collection, $filename, 'public')) {
                    $mimeType = $file->getMimeType();
                    $type = str_contains($mimeType, 'image') ? 'image' : (str_contains($mimeType, 'video') ? 'video' : 'document');
                    
                    \App\Models\Media::create([
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
                } else {
                    $failedFiles[] = $file->getClientOriginalName();
                }
            } catch (\Exception $e) {
                $failedFiles[] = $file->getClientOriginalName();
            }
        }

        return response()->json([
            'success' => $uploadedCount > 0,
            'message' => "$uploadedCount files uploaded.",
        ]);
    }

    public function deleteMedia(Request $request, Task $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            abort(403);
        }

        $media = \App\Models\Media::findOrFail($request->media_id);
        
        if ($media->model_type !== Task::class || $media->model_id !== $task->id) {
            abort(403);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($media->path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($media->path);
        }
        $media->delete();

        return response()->json(['success' => true, 'message' => 'Media deleted.']);
    }

    /**
     * Error response that works for both AJAX callers and normal form posts.
     */
    private function failure(Request $request, string $message, int $status)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], $status);
        }

        return back()->with('error', $message);
    }
}
