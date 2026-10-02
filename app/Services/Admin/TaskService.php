<?php

namespace App\Services\Admin;

use App\Models\Task;
use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use App\Models\Employee;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    public function getCreateData(): array
    {
        $clients = Client::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'logo', 'emoji']);

        $members = User::whereNotIn('role', ['admin', 'client'])
            ->orderBy('name')
            ->get();

        return compact('clients', 'members');
    }

    public function getIndexData(Request $request)
    {
        $search     = $request->get('search', '');
        $status     = $request->get('status', '');
        $client     = $request->get('client', '');
        $priority   = $request->get('priority', '');
        $platform   = $request->get('platform', '');
        $type       = $request->get('type', '');
        $assignedTo = $request->get('assigned_to', '');
        $overdue    = $request->get('overdue', '');
        $dateField  = in_array($request->get('date_field'), ['deadline', 'post_date', 'created_at']) ? $request->get('date_field') : 'deadline';
        $dateFrom   = $request->get('date_from', '');
        $dateTo     = $request->get('date_to', '');
        $sort       = $request->get('sort', 'latest');
        $perPageRaw = $request->get('per_page', 50);
        $perPage    = (int) $perPageRaw;
        if ($perPageRaw === 'custom') {
            $perPage = (int) $request->get('per_page_custom', 50);
        }
        if ($perPage < 1) $perPage = 50;

        $query = $this->buildIndexQuery($request);
        $tasks = $query->paginate($perPage)->appends($request->query());

        $clients   = Client::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'emoji', 'logo', 'color']);
        $designers = User::whereIn('role', ['designer', 'developer'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $columns = ['todo', 'inprogress', 'review', 'pending_approval', 'completed', 'published'];

        // Kanban: cap at 200 tasks total to avoid loading every task into memory on large datasets.
        $kanbanTasks = $this->buildIndexQuery($request, true)
            ->limit(200)
            ->get()
            ->groupBy('status');

        // Single query for all status counts instead of 7 separate COUNT queries
        $statusCounts = Task::selectRaw("
            COUNT(*) as total,
            SUM(status = 'todo') as todo,
            SUM(status = 'inprogress') as inprogress,
            SUM(status = 'review') as review,
            SUM(status = 'completed') as completed,
            SUM(status = 'published') as published,
            SUM(deadline < NOW() AND status NOT IN ('completed','published')) as overdue
        ")->first();

        $taskStats = [
            'total'      => (int) $statusCounts->total,
            'todo'       => (int) $statusCounts->todo,
            'inprogress' => (int) $statusCounts->inprogress,
            'review'     => (int) $statusCounts->review,
            'completed'  => (int) $statusCounts->completed,
            'published'  => (int) $statusCounts->published,
            'overdue'    => (int) $statusCounts->overdue,
        ];

        $recentTasks = Task::with(['client', 'assignee'])
            ->latest()
            ->take(5)
            ->get();

        $recentlyCompleted = Task::with(['client', 'assignee'])
            ->whereIn('status', ['completed', 'published'])
            ->latest('completed_at')
            ->take(5)
            ->get();

        return compact(
            'tasks', 'clients', 'designers', 'kanbanTasks', 'columns', 'taskStats',
            'search', 'status', 'client', 'priority', 'platform', 'type', 'assignedTo',
            'dateField', 'dateFrom', 'dateTo', 'sort', 'perPage', 'overdue',
            'recentTasks', 'recentlyCompleted', 'perPageRaw'
        );
    }

    public function getExportTasks(Request $request)
    {
        return $this->buildIndexQuery($request)->get();
    }

    private function buildIndexQuery(Request $request, $forKanban = false)
    {
        $search     = $request->get('search', '');
        $status     = $request->get('status', '');
        $client     = $request->get('client', '');
        $priority   = $request->get('priority', '');
        $platform   = $request->get('platform', '');
        $type       = $request->get('type', '');
        $assignedTo = $request->get('assigned_to', '');
        $overdue    = $request->get('overdue', '');
        $dateField  = in_array($request->get('date_field'), ['deadline', 'post_date', 'created_at']) ? $request->get('date_field') : 'deadline';
        $dateFrom   = $request->get('date_from', '');
        $dateTo     = $request->get('date_to', '');
        $sort       = $request->get('sort', 'latest');

        $query = Task::with(['client', 'assignee']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('caption', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($client)) {
            $query->where('client_id', $client);
        }

        if (!empty($priority)) {
            $query->where('priority', $priority);
        }

        if (!empty($platform)) {
            $query->whereJsonContains('platform', $platform);
        }

        if (!empty($type)) {
            $query->where('type', $type);
        }

        if (!empty($assignedTo)) {
            $query->where('assigned_to', $assignedTo);
        }

        if ($overdue === 'yes') {
            $query->where('deadline', '<', now())->where('status', '!=', 'completed')->where('status', '!=', 'published');
        }

        if (!empty($dateFrom)) {
            $query->whereDate($dateField, '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate($dateField, '<=', $dateTo);
        }

        if ($forKanban) {
            $query->orderByRaw('deadline IS NULL, deadline ASC');
        } else {
            switch ($sort) {
                case 'oldest':
                    $query->oldest();
                    break;
                case 'latest':
                    $query->latest();
                    break;
                case 'deadline_asc':
                    $query->orderByRaw('deadline IS NULL, deadline ASC');
                    break;
                case 'deadline_desc':
                    $query->orderByRaw('deadline IS NULL DESC, deadline DESC');
                    break;
                case 'post_date_asc':
                    $query->orderByRaw('post_date IS NULL, post_date ASC');
                    break;
                case 'post_date_desc':
                    $query->orderByRaw('post_date IS NULL DESC, post_date DESC');
                    break;
                default:
                    $query->orderByRaw('post_date IS NULL, post_date ASC');
            }
        }

        return $query;
    }

    public function createTask(array $data)
    {
        $data['created_by'] = Auth::id();

        // Platform must be stored as array to match the model's array cast
        if (isset($data['platform']) && !is_array($data['platform'])) {
            $data['platform'] = [$data['platform']];
        }

        $designDeadline = Task::calculateDesignDeadline(
            $data['deadline'] ?? null,
            $data['post_date'] ?? null
        );
        if ($designDeadline) {
            $data['design_deadline'] = $designDeadline->toDateString();
        }

        $task = Task::create($data);

        $this->logAudit('Created', $task);

        if (!empty($task->assigned_to)) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'icon' => '📌',
                'title' => 'New task assigned',
                'subtitle' => ($task->title ?? 'Untitled') . ' · ' . ($task->client?->name ?? 'Unknown Client'),
                'link' => '/designer/tasks',
            ]);
        }

        return $task;
    }

    public function updateTask(Task $task, array $data)
    {
        $previousAssignee = $task->assigned_to;
        $previousDeadline = $task->getRawOriginal('deadline');
        $previousStatus   = $task->status;
        $oldValues        = $task->toArray();

        // Platform must be stored as array to match the model's array cast
        if (isset($data['platform']) && !is_array($data['platform'])) {
            $data['platform'] = [$data['platform']];
        }

        // Calculate design_deadline before the update so it's one atomic write
        if (isset($data['deadline']) || isset($data['post_date'])) {
            $deadlineVal = $data['deadline'] ?? $task->getRawOriginal('deadline');
            $postDateVal = $data['post_date'] ?? $task->getRawOriginal('post_date');
            $designDeadline = Task::calculateDesignDeadline($deadlineVal, $postDateVal);
            if ($designDeadline) {
                $data['design_deadline'] = $designDeadline->toDateString();
            }
        }

        $task->update($data);

        $changes = $this->getChangesDescription($data, $previousAssignee, $previousStatus, $previousDeadline);
        if (!empty($changes)) {
            $this->logAudit('Updated: ' . implode(', ', $changes), $task, $oldValues);
        }

        if (!empty($data['assigned_employees'])) {
            $task->employees()->sync(json_decode($data['assigned_employees'], true) ?? []);
        }

        if (isset($data['assigned_to'])
            && !empty($data['assigned_to'])
            && (int) $data['assigned_to'] !== (int) $previousAssignee) {
            Notification::create([
                'user_id' => $data['assigned_to'],
                'task_id' => $task->id,
                'icon'    => '📌',
                'title'   => 'Task reassigned to you',
                'subtitle' => ($task->title ?? 'Untitled') . ' · ' . ($task->client?->name ?? 'Unknown Client'),
                'link'    => '/designer/tasks',
            ]);
        }

        return $task;
    }

    public function deleteTask(Task $task)
    {
        $taskTitle = $task->title ?? 'Untitled';

        DB::transaction(function () use ($task) {
            $this->logAudit('Deleted', $task);
            $task->comments()->delete();
            Notification::where('task_id', $task->id)->delete();
            $task->delete();
        });

        return $taskTitle;
    }

    public function bulkUpdateStatus(array $data)
    {
        $count = Task::whereIn('id', $data['task_ids'])->update(['status' => $data['status']]);

        $tasks = Task::whereIn('id', $data['task_ids'])->get();
        foreach ($tasks as $task) {
            $this->logAudit('Bulk status update to ' . $data['status'], $task);
        }

        return $count;
    }

    // Additional methods for other controller methods...
    public function getEditData(Task $task)
    {
        $task->load(['client', 'assignee', 'employees']);
        $clients = Client::where('is_active', true)->get();
        $designers = User::whereIn('role', ['designer', 'developer'])
            ->orderBy('name')
            ->get();
        $members = User::whereNotIn('role', ['admin', 'client'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'additional_roles']);
        $allEmployees = Employee::where('status', 'active')->orderBy('name')->get();

        return compact('task', 'clients', 'designers', 'members', 'allEmployees');
    }

    public function getShowData(Task $task)
    {
        $task->load(['client.socialMediaLinks', 'assignee', 'creator', 'comments.user', 'adminApprover', 'media', 'socialMediaPosts.poster', 'employees', 'socialMediaLink']);
        return $task;
    }

    private function getChangesDescription(array $data, $previousAssignee, $previousStatus, $previousDeadline)
    {
        $changes = [];
        foreach ($data as $key => $value) {
            if ($key === 'assigned_to' && $value !== $previousAssignee) {
                $changes[] = "assigned from user #{$previousAssignee} to #{$value}";
            } elseif ($key === 'status' && $value !== $previousStatus) {
                $changes[] = "status changed from {$previousStatus} to {$value}";
            } elseif ($key === 'deadline' && $value !== $previousDeadline) {
                $changes[] = "deadline updated";
            } else {
                $changes[] = "{$key} updated";
            }
        }
        return $changes;
    }

private function logAudit($action, Task $task, $oldValues = null)
    {
        try {
            DB::table('audit_logs')->insert([
                'user_id' => Auth::id(),
                'task_id' => $task->id,
                'action' => $action,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => json_encode($task->toArray()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::debug('Audit log failed: ' . $e->getMessage());
        }
    }

    public function getMetricTasks(Request $request)
    {
        $metric = $request->get('metric', 'total');
        $perPage = 100; // Limit results for performance

        // Build query based on metric
        $query = Task::with(['client', 'assignee']);

        // Apply dashboard filters
        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }
        if ($assignedTo = $request->get('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }
        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }
        if ($platform = $request->get('platform')) {
            $query->whereJsonContains('platform', $platform);
        }
        if ($dateRange = $request->get('date_range')) {
            $rangeStart = $this->getRangeStart($dateRange);
            $rangeEnd = $this->getRangeEnd($dateRange);
            $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        }

        // Apply metric-specific filters
        switch ($metric) {
            case 'total':
                // No additional filter
                break;
            case 'inprogress':
                $query->where('status', 'inprogress');
                break;
            case 'review':
                $query->whereIn('status', ['review', 'pending_approval']);
                break;
            case 'completed_week':
                $query->where('status', 'completed')
                    ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()->endOfDay()]);
                break;
            case 'overdue':
                $query->where('deadline', '<', now())
                    ->whereNotIn('status', ['completed', 'published']);
                break;
            case 'todo':
                $query->where('status', 'todo');
                break;
            case 'unassigned':
                $query->whereNull('assigned_to')
                    ->whereIn('status', ['todo', 'inprogress']);
                break;
            default:
                // No additional filter
                break;
        }

        // Get only specific status filter if provided separately
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tasks = $query->orderBy('deadline')->take($perPage)->get();

        return [
            'tasks' => $tasks->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'client' => $task->client->name ?? null,
                'client_logo' => $task->client->logo ?? null,
                'client_emoji' => $task->client->emoji ?? null,
                'type' => $task->type,
                'platform' => $task->platform,
                'status' => $task->status,
                'priority' => $task->priority,
                'deadline' => $task->deadline?->format('Y-m-d'),
                'assigned_to' => $task->assignee->name ?? null,
                'url' => route('admin.tasks.show', $task->id),
            ]),
            'count' => $tasks->count(),
        ];
    }

    private function getRangeStart(string $dateRange): \Carbon\Carbon
    {
        $today = now();
        return match ($dateRange) {
            'today' => $today->copy()->startOfDay(),
            'yesterday' => $today->copy()->subDay()->startOfDay(),
            'last_week' => $today->copy()->subWeek()->startOfWeek(),
            'this_month' => $today->copy()->startOfMonth(),
            'last_month' => $today->copy()->subMonth()->startOfMonth(),
            'this_quarter' => $today->copy()->firstOfQuarter(),
            'this_year' => $today->copy()->startOfYear(),
            default => $today->copy()->startOfWeek(),
        };
    }

    private function getRangeEnd(string $dateRange): \Carbon\Carbon
    {
        $today = now();
        return match ($dateRange) {
            'today' => $today->copy()->endOfDay(),
            'yesterday' => $today->copy()->subDay()->endOfDay(),
            'last_week' => $today->copy()->subWeek()->endOfWeek()->endOfDay(),
            'this_month' => $today->copy()->endOfMonth()->endOfDay(),
            'last_month' => $today->copy()->subMonth()->endOfMonth()->endOfDay(),
            'this_quarter' => $today->copy()->lastOfQuarter()->endOfDay(),
            'this_year' => $today->copy()->endOfYear()->endOfDay(),
            default => $today->copy()->endOfWeek()->endOfDay(),
        };
    }
}
