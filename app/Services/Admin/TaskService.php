<?php

namespace App\Services\Admin;

use App\Models\Task;
use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use App\Models\Employee;
use App\Models\Comment;
use App\Models\AuditLog;
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

        // Single query for all status counts including pending_approval and urgent tasks
        $statusCounts = Task::selectRaw("
            COUNT(*) as total,
            SUM(status = 'todo') as todo,
            SUM(status = 'inprogress') as inprogress,
            SUM(status IN ('review', 'pending_approval')) as review,
            SUM(status = 'completed') as completed,
            SUM(status = 'published') as published,
            SUM(deadline < NOW() AND status NOT IN ('completed','published')) as overdue,
            SUM(is_urgent_task = 1 OR priority = 'urgent') as urgent
        ")->first();

        $taskStats = [
            'total'      => (int) ($statusCounts->total ?? 0),
            'todo'       => (int) ($statusCounts->todo ?? 0),
            'inprogress' => (int) ($statusCounts->inprogress ?? 0),
            'review'     => (int) ($statusCounts->review ?? 0),
            'completed'  => (int) ($statusCounts->completed ?? 0),
            'published'  => (int) ($statusCounts->published ?? 0),
            'overdue'    => (int) ($statusCounts->overdue ?? 0),
            'urgent'     => (int) ($statusCounts->urgent ?? 0),
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

        $query = Task::with(['client', 'assignee', 'creator', 'media']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('caption', 'LIKE', "%{$search}%");
            });
        }

        if ($status === 'urgent') {
            $query->where(function ($q) {
                $q->where('is_urgent_task', true)->orWhere('priority', 'urgent');
            });
        } elseif ($status === 'review') {
            $query->whereIn('status', ['review', 'pending_approval']);
        } elseif (!empty($status)) {
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

        if (Task::isDevType($data['type'] ?? null)) {
            // Designer views and workload sort by `deadline`; dev projects use their dev deadline there.
            // No social design deadline applies to a build project.
            if (empty($data['deadline']) && !empty($data['dev_deadline'])) {
                $data['deadline'] = $data['dev_deadline'];
            }
        } else {
            if (empty($data['deadline']) && !empty($data['post_date'])) {
                $postDate = Carbon::parse($data['post_date'])->startOfDay();
                $calcDeadline = $postDate->copy()->subDays(3);
                $data['deadline'] = $calcDeadline->lt(Carbon::today()) ? Carbon::today()->toDateString() : $calcDeadline->toDateString();
            }
            $designDeadline = Task::calculateDesignDeadline(
                $data['deadline'] ?? null,
                $data['post_date'] ?? null
            );
            if ($designDeadline) {
                $data['design_deadline'] = $designDeadline->toDateString();
            }
        }

        $task = Task::create($data);

        $this->logAudit('Created', $task);

        $assignee = $task->assigned_to ? User::find($task->assigned_to) : null;
        if ($assignee) {
            Notification::create([
                'user_id' => $assignee->id,
                'task_id' => $task->id,
                'icon' => Task::isDevType($task->type) ? '💻' : '📌',
                'title' => Task::isDevType($task->type) ? 'New project assigned' : 'New task assigned',
                'subtitle' => ($task->title ?? 'Untitled') . ' · ' . ($task->client?->name ?? 'Unknown Client'),
                // Link into the assignee's own panel (developers can't open /designer pages)
                'link' => $task->linkFor($assignee),
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
        $tasks = Task::whereIn('id', $data['task_ids'])->get();
        $icons = ['todo' => '🔄', 'inprogress' => '🔵', 'review' => '📋', 'completed' => '✅', 'published' => '📢'];
        $labels = ['todo' => 'To Do', 'inprogress' => 'In Progress', 'review' => 'Review', 'completed' => 'Completed', 'published' => 'Published'];

        foreach ($tasks as $task) {
            $this->logAudit('Bulk status update to ' . $data['status'], $task);

            if ($task->assigned_to && $task->assigned_to !== Auth::id() && $task->status !== $data['status']) {
                Notification::create([
                    'user_id'  => $task->assigned_to,
                    'task_id'  => $task->id,
                    'icon'     => $icons[$data['status']] ?? '🔔',
                    'title'    => 'Task moved to ' . ($labels[$data['status']] ?? ucfirst($data['status'])),
                    'subtitle' => 'Admin updated "' . $task->title . '" (bulk update)',
                    'link'     => route('admin.tasks.show', $task),
                ]);
            }
        }

        $count = Task::whereIn('id', $data['task_ids'])->update(['status' => $data['status']]);

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

    public function bulkUpdateStatus(array $taskIds, string $status): int
    {
        $validStatuses = ['todo', 'inprogress', 'review', 'pending_approval', 'completed', 'published'];
        if (!in_array($status, $validStatuses, true)) {
            throw new \InvalidArgumentException('Invalid task status');
        }

        $tasks = Task::whereIn('id', $taskIds)->get();
        $count = 0;

        foreach ($tasks as $task) {
            $oldStatus = $task->status;
            $task->status = $status;
            if (in_array($status, ['completed', 'published'], true) && is_null($task->completed_at)) {
                $task->completed_at = now();
            } elseif (!in_array($status, ['completed', 'published'], true)) {
                $task->completed_at = null;
            }
            $task->save();

            AuditLog::create([
                'task_id'    => $task->id,
                'user_id'    => Auth::id(),
                'action'     => 'status_changed',
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $status],
                'details'    => 'Status updated via bulk action by Admin',
            ]);

            $count++;
        }

        return $count;
    }
}
