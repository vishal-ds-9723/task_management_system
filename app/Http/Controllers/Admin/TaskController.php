<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Http\Requests\Admin\UpdateTaskRequest;
use App\Http\Requests\Admin\BulkUpdateStatusRequest;
use App\Services\Admin\TaskService;
use App\Models\Client;
use App\Models\ClientSocialMediaLink;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getIndexData($request);

        return view('admin.tasks', $data);
    }

    public function create()
    {
        return view('admin.task-create', $this->service->getCreateData());
    }

    public function edit(Task $task)
    {
        $data = $this->service->getEditData($task);

        return view('admin.task-edit', $data);
    }

    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();

        $selectedSocialLinkIds = collect((array) $request->input('selected_social_media_link_ids', []));

        foreach ((array) ($validated['platform'] ?? []) as $platform) {
            $dynamicId = $request->input('social_media_link_id_' . $platform);
            if (!empty($dynamicId)) {
                $selectedSocialLinkIds->push($dynamicId);
            }
        }

        if (!empty($validated['client_social_media_link_id'])) {
            $selectedSocialLinkIds->push($validated['client_social_media_link_id']);
        }

        $selectedSocialLinkIds = $selectedSocialLinkIds
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedSocialLinkIds->isNotEmpty()) {
            $validLinkIds = ClientSocialMediaLink::query()
                ->where('client_id', '=', (int) $validated['client_id'], 'and')
                ->whereIn('id', $selectedSocialLinkIds->all())
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();

            $validated['selected_social_media_link_ids'] = $validLinkIds->isNotEmpty()
                ? $validLinkIds->all()
                : null;
            $validated['client_social_media_link_id'] = $validLinkIds->first();
        }

        $task = $this->service->createTask($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'task' => $task
            ]);
        }

        if ($request->boolean('_from_create_page')) {
            return redirect()->route('admin.tasks')->with('success', 'Task created successfully!');
        }

        return back()->with('success', 'Task created successfully!');
    }

    public function show(Task $task)
    {
        $task = $this->service->getShowData($task);

        return view('admin.task-detail', compact('task'));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task = $this->service->updateTask($task, $request->validated());

        return back()->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        $taskTitle = $this->service->deleteTask($task);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Task '{$taskTitle}' has been deleted."
            ]);
        }

        return redirect()->route('admin.tasks')->with('success', "Task '{$taskTitle}' has been deleted.");
    }

    public function kanbanUpdateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:todo,inprogress,review,completed,published'],
        ]);

        $task->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated.',
            'status'  => $task->status,
        ]);
    }

    public function bulkUpdateStatus(BulkUpdateStatusRequest $request)
    {
        $count = $this->service->bulkUpdateStatus($request->validated());

        return back()->with('success', "{$count} task(s) updated to {$request->status}!");
    }

    public function exportCsv(Request $request)
    {
        $tasks = $this->service->getExportTasks($request);
        $fileName = 'tasks_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($tasks) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM helps Excel display non-ASCII text correctly.
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Title',
                'Client',
                'Assigned To',
                'Status',
                'Priority',
                'Type',
                'Platform',
                'Deadline',
                'Post Date',
                'Created At',
            ]);

            foreach ($tasks as $task) {
                $platformText = is_array($task->platform)
                    ? implode(', ', $task->platform)
                    : (string) ($task->platform ?? '');

                fputcsv($handle, [
                    $task->id,
                    $task->title,
                    optional($task->client)->name,
                    optional($task->assignee)->name,
                    $task->status,
                    $task->priority,
                    $task->type,
                    $platformText,
                    optional($task->deadline)?->format('Y-m-d'),
                    optional($task->post_date)?->format('Y-m-d'),
                    optional($task->created_at)?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function getMetricTasks(Request $request)
    {
        $data = $this->service->getMetricTasks($request);

        return response()->json($data);
    }

    public function urgentTasks(Request $request)
    {
        $baseQuery = Task::query()
            ->with(['client', 'creator', 'assignee'])
            ->where('is_urgent_task', true);

        $totalUrgent = (clone $baseQuery)->count();
        $recentCount = (clone $baseQuery)->where('created_at', '>=', now()->subDays(2))->count();
        $processingCount = (clone $baseQuery)->whereNotIn('status', ['completed', 'published'])->count();
        $completedCount = (clone $baseQuery)->whereIn('status', ['completed', 'published'])->count();

        $tasksQuery = clone $baseQuery;

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            if (in_array($status, ['completed', 'published'], true)) {
                $tasksQuery->whereIn('status', ['completed', 'published']);
            } else {
                $tasksQuery->where('status', $status);
            }
        }

        if ($request->filled('client')) {
            $tasksQuery->where('client_id', (int) $request->input('client'));
        }

        if ($request->filled('role')) {
            $tasksQuery->whereHas('creator', fn ($q) => $q->where('role', $request->input('role')));
        }

        if ($request->filled('designer')) {
            $tasksQuery->where('created_by', (int) $request->input('designer'));
        }

        if ($request->filled('from')) {
            $tasksQuery->whereDate('created_at', '>=', $request->string('from')->toString());
        }

        if ($request->filled('to')) {
            $tasksQuery->whereDate('created_at', '<=', $request->string('to')->toString());
        }

        $perPageRaw = $request->input('per_page', 30);
        $perPage = $perPageRaw === 'custom' ? (int) $request->input('custom_per_page', 100) : (int) $perPageRaw;

        $tasks = $tasksQuery
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $clients = Client::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $roles = User::where('role', '!=', 'admin')->distinct()->orderBy('role')->pluck('role');
        $selectedRole = $request->input('role');

        $designers = User::query()
            ->where('role', '!=', 'admin')
            ->when($selectedRole, fn($q) => $q->where('role', $selectedRole))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.urgent-tasks', compact(
            'tasks',
            'totalUrgent',
            'recentCount',
            'processingCount',
            'completedCount',
            'clients',
            'designers',
            'roles',
            'selectedRole',
            'perPageRaw'
        ));
    }

    public function dateChangeRequests(Request $request)
    {
        $dateChangeRequests = Notification::with(['task.client', 'task.creator'])
            ->where('title', 'Post date change requested')
            ->whereNull('read_at')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.date-change-requests', compact('dateChangeRequests'));
    }

    public function approveDate(Request $request, Task $task)
    {
        $validated = $request->validate([
            'new_post_date' => ['required', 'date'],
            'notification_id' => ['nullable', 'integer'],
        ]);

        $previousDate = $task->getRawOriginal('post_date') ?: null;
        $newPostDate = \Illuminate\Support\Carbon::parse($validated['new_post_date'])->toDateString();

        $deadlineDate = $task->getRawOriginal('deadline') ?: null;
        $designDeadline = Task::calculateDesignDeadline($deadlineDate, $newPostDate);

        $updates = ['post_date' => $newPostDate];
        if ($designDeadline) {
            $updates['design_deadline'] = $designDeadline->toDateString();
        }
        $task->update($updates);

        if (!empty($validated['notification_id'])) {
            Notification::where('id', $validated['notification_id'])
                ->where('task_id', $task->id)
                ->update(['read_at' => now()]);
        }

        if ($task->creator) {
            Notification::create([
                'user_id'  => $task->creator->id,
                'task_id'  => $task->id,
                'icon'     => '✅',
                'title'    => 'Post date approved',
                'subtitle' => 'Admin approved post date for "' . $task->title . '" → ' . $newPostDate,
                'link'     => '/strategist/tasks/' . $task->id,
            ]);
        }

        Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body'    => '✅ Admin approved post date change: ' . ($previousDate ?: 'Not set') . ' → ' . $newPostDate,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post date change approved successfully.',
            ]);
        }

        return back()->with('success', 'Post date change approved successfully.');
    }

    public function rejectDate(Request $request, Task $task)
    {
        $validated = $request->validate([
            'notification_id' => ['nullable', 'integer'],
            'reject_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = trim((string) ($validated['reject_reason'] ?? ''));

        if (!empty($validated['notification_id'])) {
            Notification::where('id', $validated['notification_id'])
                ->where('task_id', $task->id)
                ->update(['read_at' => now()]);
        }

        if ($task->creator) {
            Notification::create([
                'user_id'  => $task->creator->id,
                'task_id'  => $task->id,
                'icon'     => '❌',
                'title'    => 'Post date rejected',
                'subtitle' => 'Admin declined post date change for "' . $task->title . '"' . ($reason ? ': ' . $reason : ''),
                'link'     => '/strategist/tasks/' . $task->id,
            ]);
        }

        Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body'    => '❌ Admin declined post date change' . ($reason ? ': ' . $reason : '.'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Post date change request declined.',
            ]);
        }

        return back()->with('success', 'Post date change request declined.');
    }

    // Delegate other methods to service as needed
    // ... (add other methods like urgentTasks, approveDate, etc. using service)
}
