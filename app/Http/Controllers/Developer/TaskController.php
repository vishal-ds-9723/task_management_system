<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\DeveloperSkill;
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
        $upcomingDeadlines = Task::where('assigned_to', $developer->id)
            ->whereNotNull('dev_deadline')
            ->whereBetween('dev_deadline', [now(), now()->addDays(7)])
            ->count();

        return view('developer.dashboard', compact('tasks', 'totalTasks', 'inProgressTasks', 'completedTasks', 'upcomingDeadlines'));
    }

    public function tasks(Request $request)
    {
        $developer = Auth::user();
        $scope = $request->get('scope', 'my');

        $query = Task::with(['client', 'assignee', 'creator']);

        if ($scope !== 'all') {
            $query->where('assigned_to', $developer->id);
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

        $tasks = $query->orderByRaw("CASE
                WHEN status = 'todo' THEN 1
                WHEN status = 'inprogress' THEN 2
                WHEN status = 'completed' THEN 3
                ELSE 4
            END")
            ->orderBy('dev_deadline', 'asc')
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

    public function skills()
    {
        $skills = DeveloperSkill::where('user_id', Auth::id())
            ->orderByRaw("CASE WHEN type = 'primary' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('developer.skills', compact('skills'));
    }

    public function storeSkill(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('developer_skills', 'name')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
            'type' => ['required', Rule::in(['primary', 'secondary'])],
        ], [
            'name.unique' => 'You already added this skill.',
        ]);

        DeveloperSkill::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
        ]);

        return redirect()
            ->route('developer.skills')
            ->with('success', 'Skill added successfully.');
    }

    public function deleteSkill(DeveloperSkill $skill)
    {
        if ($skill->user_id !== Auth::id()) {
            abort(403);
        }

        $skill->delete();

        return redirect()
            ->route('developer.skills')
            ->with('success', 'Skill removed successfully.');
    }

    public function start(Request $request, Task $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            return back()->with('error', 'You are not assigned to this task.');
        }

        if ($task->status !== 'todo') {
            return back()->with('error', 'Task is not in To Do status.');
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
            return back()->with('error', 'You are not assigned to this task.');
        }

        $task->dev_submission_link = $request->link;
        $task->dev_submission_notes = $request->notes;
        $task->status = 'review';
        $task->submitted_at = now();
        $task->save();

        // Notify admins and strategists
        $recipients = User::whereIn('role', ['admin', 'strategist'])->get();

        foreach ($recipients as $user) {
            Notification::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'icon' => 'task',
                'title' => 'New Task Submission',
                'subtitle' => "Developer submitted {$task->title} for review. Link: {$task->dev_submission_link}",
                'link' => route('strategist.tasks.show', $task),
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
            $duration = now()->diffInSeconds($pauseRecord->created_at);
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
}
