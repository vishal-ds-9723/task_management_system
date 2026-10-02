<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Notification;
use App\Models\SocialMediaPost;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    // Cache duration in seconds (5 minutes for better performance)
    const CACHE_DURATION = 300;

    public function getDashboardData(Request $request)
    {
        $cacheKey = $this->buildCacheKey($request);

        // Use the configured cache duration
        return Cache::remember($cacheKey, self::CACHE_DURATION, fn () => $this->buildDashboardData($request));
    }

    /**
     * Get dashboard data optimized for instant loading even with millions of records
     * Uses single efficient query with conditional aggregation
     */
    private function buildDashboardData(Request $request)
    {
        $today      = Carbon::today();
        $dateRange  = $request->input('date_range', '');
        $rangeStart = $this->getRangeStart($dateRange, $today);
        $rangeEnd   = $this->getRangeEnd($dateRange, $today);
        $weekStart  = $today->copy()->startOfWeek();
        $weekEnd    = $today->copy()->endOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd   = $today->copy()->endOfMonth();

        $filterClientId   = $request->input('client_id');
        $filterAssignedTo = $request->input('assigned_to');
        $filterStatus     = $request->input('status');
        $filterPriority   = $request->input('priority');
        $filterPlatform   = $request->input('platform');
        $hasFilters       = $filterClientId || $filterAssignedTo || $filterStatus || $filterPriority || $filterPlatform || $dateRange;

        // Build base query conditions once
        $baseConditions = [
            'client_id' => $filterClientId,
            'assigned_to' => $filterAssignedTo,
            'priority' => $filterPriority,
        ];
        $baseConditions = array_filter($baseConditions);

        // OPTIMIZATION: Use single optimized query with conditional aggregation for status counts
        // This replaces dozens of individual count() calls
        $statusCounts = Task::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "todo" THEN 1 ELSE 0 END) as todo_count,
            SUM(CASE WHEN status = "inprogress" THEN 1 ELSE 0 END) as inprogress_count,
            SUM(CASE WHEN status = "review" THEN 1 ELSE 0 END) as review_count,
            SUM(CASE WHEN status = "pending_approval" THEN 1 ELSE 0 END) as pending_approval_count,
            SUM(CASE WHEN status IN ("review", "pending_approval") THEN 1 ELSE 0 END) as pending_review_total,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = "published" THEN 1 ELSE 0 END) as published_count,
            SUM(CASE WHEN status NOT IN ("completed", "published") AND deadline < NOW() THEN 1 ELSE 0 END) as delayed_count,
            SUM(CASE WHEN assigned_to IS NULL AND status IN ("todo", "inprogress") THEN 1 ELSE 0 END) as unassigned_count,
            SUM(CASE WHEN created_at >= ? AND status NOT IN ("completed", "published") THEN 1 ELSE 0 END) as created_today_count
        ', [$today->startOfDay()])
        ->when(!empty($baseConditions), fn ($q) => $q->where($baseConditions))
        ->when($filterPlatform, fn ($q) => $q->whereJsonContains('platform', $filterPlatform))
        ->when($dateRange, fn ($q) => $q->whereBetween('created_at', [$rangeStart, $rangeEnd]))
        ->first();

        // Extract counts from the single optimized query
        $totalTasks    = (int) ($statusCounts->total ?? 0);
        $pendingReview = (int) ($statusCounts->pending_review_total ?? 0);
        $delayedTasks   = (int) ($statusCounts->delayed_count ?? 0);
        $inProgress     = (int) ($statusCounts->inprogress_count ?? 0);
        $todoTasks      = (int) ($statusCounts->todo_count ?? 0);
        $unassignedTasks = (int) ($statusCounts->unassigned_count ?? 0);
        $tasksCreatedToday = (int) ($statusCounts->created_today_count ?? 0);

        // Date boundaries for the 6 completed-count buckets below.
        $lastWeekStart  = $weekStart->copy()->subWeek();
        $lastWeekEnd    = $weekEnd->copy()->subWeek()->endOfDay();
        $lastMonthStart = $monthStart->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $monthStart->copy()->subDay()->endOfDay();
        $yesterday      = $today->copy()->subDay();

        // Single query replaces 6 separate count() calls — all "completed/published" buckets
        // by date range, with the same base filters applied.
        $completedBuckets = Task::selectRaw('
            SUM(CASE WHEN DATE(updated_at) = ? THEN 1 ELSE 0 END) as today_count,
            SUM(CASE WHEN updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as week_count,
            SUM(CASE WHEN updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as month_count,
            SUM(CASE WHEN updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as last_week_count,
            SUM(CASE WHEN updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as last_month_count,
            SUM(CASE WHEN DATE(updated_at) = ? THEN 1 ELSE 0 END) as yesterday_count
        ', [
            $today->toDateString(),
            $weekStart, $weekEnd->copy()->endOfDay(),
            $monthStart, $monthEnd->copy()->endOfDay(),
            $lastWeekStart, $lastWeekEnd,
            $lastMonthStart, $lastMonthEnd,
            $yesterday->toDateString(),
        ])
            ->whereIn('status', ['completed', 'published'])
            ->when(!empty($baseConditions), fn ($q) => $q->where($baseConditions))
            ->when($filterPlatform, fn ($q) => $q->whereJsonContains('platform', $filterPlatform))
            ->first();

        $completedToday     = (int) ($completedBuckets->today_count ?? 0);
        $completedWeek      = (int) ($completedBuckets->week_count ?? 0);
        $completedMonth     = (int) ($completedBuckets->month_count ?? 0);
        $lastWeekCompleted  = (int) ($completedBuckets->last_week_count ?? 0);
        $lastMonthCompleted = (int) ($completedBuckets->last_month_count ?? 0);
        $yesterdayCompleted = (int) ($completedBuckets->yesterday_count ?? 0);

        $kpiTrends = [
            'completed_week'  => $this->calcTrendPct($completedWeek,  $lastWeekCompleted),
            'completed_month' => $this->calcTrendPct($completedMonth, $lastMonthCompleted),
            'completed_today' => $this->calcTrendPct($completedToday, $yesterdayCompleted),
            'delayed'         => $this->calcTrendPct($delayedTasks,   max(1, $delayedTasks)),
        ];

        // Weekly trend - optimized (single date query per day via groupBy)
        $weeklyTrend = Task::selectRaw('DATE(created_at) as date, COUNT(*) as created_count')
            ->whereBetween('created_at', [$weekStart, $weekEnd->copy()->endOfDay()])
            ->when(!empty($baseConditions), fn ($q) => $q->where($baseConditions))
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at) asc')
            ->get()
            ->map(function ($row) use ($baseConditions) {
                $rowDate = Carbon::parse($row->date);

                return [
                    'day' => $rowDate->format('D'),
                    'date' => $rowDate->format('M d'),
                    'created' => (int) $row->created_count,
                    'completed' => Task::whereIn('status', ['completed', 'published'])
                        ->whereDate('updated_at', $row->date)
                        ->when(!empty($baseConditions), fn ($q) => $q->where($baseConditions))
                        ->count(),
                ];
            })
            ->toArray();

        // Ensure all 7 days are present
        $weeklyTrend = array_map(fn ($i) => $weeklyTrend[$i] ?? [
            'day' => $weekStart->copy()->addDays($i)->format('D'),
            'date' => $weekStart->copy()->addDays($i)->format('M d'),
            'created' => 0,
            'completed' => 0,
        ], range(0, 6));

        $statusBreakdown = [
            'todo'       => $todoTasks,
            'inprogress' => $inProgress,
            'review'    => $pendingReview - (int)($statusCounts->pending_approval_count ?? 0),
            'completed'=> (int) ($statusCounts->completed_count ?? 0),
        ];
        $statusTotal = max(1, array_sum(array_values($statusBreakdown)));

        // Priority breakdown - single optimized query
        $priorityBreakdown = Task::selectRaw('
            priority,
            COUNT(*) as count
        ')
            ->whereNotIn('status', ['completed', 'published'])
            ->when(!empty($baseConditions), fn ($q) => $q->where($baseConditions))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Platform breakdown - single query
        $platformBreakdown = Task::selectRaw('
            platform,
            COUNT(*) as count
        ')
            ->when(!empty($baseConditions), fn ($q) => $q->where($baseConditions))
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        // Type breakdown - single query
        $typeBreakdown = Task::selectRaw('
            type,
            COUNT(*) as count
        ')
            ->when(!empty($baseConditions), fn ($q) => $q->where($baseConditions))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $clientPerformance = $this->getClientPerformance($filterAssignedTo, $filterStatus, $filterPriority, $filterPlatform, $dateRange, $rangeStart, $rangeEnd, $filterClientId);
        $upcomingDeadlines = $this->getUpcomingDeadlines($today, $filterStatus, $baseConditions);
        $overdueTasks = $this->getOverdueTasks($filterStatus, $baseConditions);
        $recentActivity = $this->getRecentActivity($filterAssignedTo, $filterClientId, $filterStatus, $filterPriority, $filterPlatform, $dateRange, $rangeStart, $rangeEnd);

        $employees = $this->getEmployees($filterAssignedTo, $filterClientId, $filterStatus, $filterPriority, $filterPlatform, $dateRange, $rangeStart, $rangeEnd);
        $dateChangeRequests = Notification::with(['task.client', 'task.creator'])->where('title', 'Post date change requested')->whereNull('read_at')->oldest()->get();
        $workloadByDesigner = $this->getWorkloadByDesigner($today, $filterAssignedTo, $filterClientId, $filterStatus, $filterPriority, $filterPlatform, $dateRange, $rangeStart, $rangeEnd);
        $designerEfficiency = $this->getDesignerEfficiency($filterAssignedTo, $filterClientId, $filterPriority, $filterPlatform, $dateRange, $rangeStart, $rangeEnd);

        $activeClients = $this->getActiveClientsCount($filterClientId, $filterAssignedTo, $filterStatus, $filterPriority, $filterPlatform, $dateRange, $rangeStart, $rangeEnd);
        $clients = Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'logo', 'emoji']);
        $designers = User::where('role', 'designer')->orderBy('name')->get(['id', 'name']);

        // Publishing - optimized
        $publishingAwaitingCount = Task::where('is_urgent_task', false)->where('status', 'completed')->whereDoesntHave('socialMediaPosts')->count();
        $publishingPartialCount = Task::where('is_urgent_task', false)->where('status', 'completed')->whereHas('socialMediaPosts')->count();
        $publishingPublishedCount = Task::where('is_urgent_task', false)->where('status', 'published')->count();
        $publishingTotalCount = $publishingAwaitingCount + $publishingPartialCount + $publishingPublishedCount;

        $recentPublishing = SocialMediaPost::select('id', 'task_id', 'posted_by', 'platform', 'post_url', 'created_at')
            ->with([
                'task:id,title,client_id',
                'task.client:id,name,logo,emoji',
                'poster:id,name',
            ])
            ->whereHas('task', fn ($q) => $q->where('is_urgent_task', false))
            ->latest()
            ->take(5)
            ->get();
        $publishingByPlatform = SocialMediaPost::whereHas('task', fn ($q) => $q->where('is_urgent_task', false))
            ->selectRaw('platform, COUNT(*) as total')
            ->groupBy('platform')
            ->pluck('total', 'platform');

        $urgentTasksWhileAway = Task::select('id', 'title', 'client_id', 'assigned_to', 'created_at')
            ->with([
                'client:id,name,logo,emoji',
                'assignee:id,name,avatar_color',
            ])
            ->where('is_urgent_task', true)
            ->whereHas('creator', fn ($q) => $q->where('role', 'designer'))
            ->where('created_at', '>=', now()->subDays(2))
            ->latest()
            ->take(10)
            ->get();

        // Dev projects overview
        $devTasksActive = Task::whereIn('type', ['website', 'software', 'maintenance'])
            ->whereNotIn('status', ['completed', 'published'])
            ->count();
        $devTasksList = Task::with(['client:id,name,logo,emoji', 'assignee:id,name,avatar_color'])
            ->whereIn('type', ['website', 'software', 'maintenance'])
            ->whereNotIn('status', ['completed', 'published'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal')")
            ->orderByRaw('COALESCE(dev_deadline, launch_date, deadline) IS NULL, COALESCE(dev_deadline, launch_date, deadline) ASC')
            ->take(8)
            ->get();

        return compact(
            'totalTasks', 'pendingReview', 'activeClients', 'delayedTasks',
            'completedToday', 'completedWeek', 'completedMonth',
            'inProgress', 'todoTasks', 'unassignedTasks',
            'clients', 'designers', 'hasFilters', 'filterStatus',
            'kpiTrends',
            'weeklyTrend', 'statusBreakdown', 'statusTotal',
            'priorityBreakdown', 'platformBreakdown', 'typeBreakdown',
            'clientPerformance', 'upcomingDeadlines', 'overdueTasks',
            'recentActivity', 'employees',
            'dateChangeRequests', 'workloadByDesigner', 'designerEfficiency',
            'tasksCreatedToday',
            'publishingAwaitingCount', 'publishingPartialCount',
            'publishingPublishedCount', 'publishingTotalCount',
            'recentPublishing', 'publishingByPlatform',
            'urgentTasksWhileAway',
            'devTasksActive', 'devTasksList'
        );
    }

    public function teamActivity()
    {
        if (class_exists(\Barryvdh\Debugbar\Facades\Debugbar::class)) {
            \Barryvdh\Debugbar\Facades\Debugbar::disable();
        }

        $today = Carbon::today();

        $members = User::whereNotIn('role', ['admin', 'client'])
            ->with(['assignedTasks' => fn ($q) => $q
                ->whereIn('status', ['todo', 'inprogress', 'review'])
                ->with('client:id,name,emoji')
                ->orderByRaw("FIELD(status, 'inprogress', 'review', 'todo')")
                ->orderBy('deadline')
                ->select('id', 'title', 'client_id', 'assigned_to', 'type', 'platform', 'status', 'priority', 'deadline', 'design_deadline', 'updated_at'),
            ])
            ->withCount([
                'assignedTasks as total_assigned',
                'assignedTasks as completed_count'    => fn ($q) => $q->where('status', 'completed'),
                'assignedTasks as completed_today'    => fn ($q) => $q->where('status', 'completed')->whereDate('updated_at', $today),
                'assignedTasks as completed_this_week' => fn ($q) => $q->where('status', 'completed')
                    ->whereBetween('updated_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()->endOfDay()]),
                'assignedTasks as overdue_count'      => fn ($q) => $q->where('deadline', '<', now())->where('status', '!=', 'completed'),
                'comments as comments_today'          => fn ($q) => $q->whereDate('created_at', $today),
            ])
            ->orderBy('role')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'avatar_color']);

        $userIds = $members->pluck('id');

        // Single query for all users' recent audit logs — eliminates N+1
        $recentActions = AuditLog::with('task:id,title')
            ->whereIn('user_id', $userIds)
            ->latest()
            ->get()
            ->groupBy('user_id')
            ->map(fn ($logs) => $logs->take(5)->values());

        $result = $members->map(function ($member) use ($recentActions) {
            $memberLogs     = $recentActions->get($member->id, collect());
            $lastAction     = $memberLogs->first();
            $completionRate = $member->total_assigned > 0
                ? round(($member->completed_count / $member->total_assigned) * 100)
                : 0;
            $currentFocus   = $member->assignedTasks->firstWhere('status', 'inprogress');

            return [
                'id'           => $member->id,
                'name'         => $member->name,
                'email'        => $member->email,
                'role'         => $member->role,
                'initial'      => strtoupper(substr($member->name, 0, 1)),
                'avatar_color' => $member->avatar_color ?? '#555',
                'active_count' => $member->assignedTasks->count(),
                'stats' => [
                    'total_assigned'      => $member->total_assigned,
                    'completed'           => $member->completed_count,
                    'completed_today'     => $member->completed_today,
                    'completed_this_week' => $member->completed_this_week,
                    'overdue'             => $member->overdue_count,
                    'completion_rate'     => $completionRate,
                    'comments_today'      => $member->comments_today,
                ],
                'current_focus' => $currentFocus ? [
                    'id'        => $currentFocus->id,
                    'title'     => $currentFocus->title,
                    'client'    => ($currentFocus->client->emoji ?? '') . ' ' . ($currentFocus->client->name ?? ''),
                    'type'      => $currentFocus->type,
                    'platform'  => $currentFocus->platform,
                    'priority'  => $currentFocus->priority,
                    'deadline'  => $currentFocus->deadline?->format('M d, Y'),
                    'is_overdue' => $currentFocus->deadline && $currentFocus->deadline->isPast(),
                    'url'       => route('admin.tasks.show', $currentFocus->id),
                ] : null,
                'last_action' => $lastAction ? [
                    'action' => $lastAction->action,
                    'task'   => $lastAction->task?->title,
                    'time'   => $lastAction->created_at->diffForHumans(),
                ] : null,
                'recent_actions' => $memberLogs->map(fn ($log) => [
                    'action' => $log->action,
                    'task'   => $log->task?->title,
                    'time'   => $log->created_at->diffForHumans(),
                ])->values(),
                'tasks' => $member->assignedTasks->map(fn ($task) => [
                    'id'              => $task->id,
                    'title'           => $task->title,
                    'client'          => ($task->client->emoji ?? '') . ' ' . ($task->client->name ?? ''),
                    'type'            => $task->type,
                    'platform'        => $task->platform,
                    'status'          => $task->status,
                    'priority'        => $task->priority,
                    'deadline'        => $task->deadline?->format('M d, Y'),
                    'design_deadline' => $task->design_deadline?->format('M d, Y'),
                    'is_overdue'      => $task->deadline && $task->deadline->isPast(),
                    'updated'         => $task->updated_at->diffForHumans(),
                    'url'             => route('admin.tasks.show', $task->id),
                ]),
            ];
        });

        return response()->json(['members' => $result]);
    }

    public function getChatbotTasksSummary()
    {
        $tasks = Task::with('client')
            ->whereNotIn('status', ['completed', 'published'])
            ->orderByDesc('priority')
            ->orderBy('deadline')
            ->limit(10)
            ->get()
            ->map(fn ($task) => [
                'id'          => $task->id,
                'title'       => $task->title,
                'client_name' => $task->client->name ?? 'N/A',
                'status'      => $task->status,
                'priority'    => $task->priority,
                'deadline'    => $task->deadline?->format('M d'),
            ]);

        return response()->json(['tasks' => $tasks]);
    }

    public function getChatbotClientsSummary()
    {
        $clients = Client::withCount(['tasks'])
            ->orderByDesc('tasks_count')
            ->limit(10)
            ->get()
            ->map(fn ($client) => [
                'id'         => $client->id,
                'name'       => $client->name,
                'task_count' => $client->tasks_count,
            ]);

        return response()->json(['clients' => $clients]);
    }

    public function getChatbotReportsSummary()
    {
        $totalTasks    = Task::count();
        $completedTasks = Task::where('status', 'completed')->count();

        return response()->json([
            'total_tasks'          => $totalTasks,
            'completed_tasks'      => $completedTasks,
            'in_progress_tasks'    => Task::whereIn('status', ['inprogress', 'review'])->count(),
            'pending_review_tasks' => Task::where('status', 'review')->count(),
            'total_clients'        => Client::count(),
            'total_users'          => User::whereIn('role', ['designer', 'strategist'])->count(),
            'completion_rate'      => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0,
        ]);
    }

    public function chatbotMessage(Request $request)
    {
        return response()->json(['response' => $this->generateChatbotResponse($request->input('message', ''))]);
    }

    public function getChatbotTabData(Request $request)
    {
        $tab    = $request->input('tab', 'overview');
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $today  = Carbon::today();

        switch ($tab) {
            case 'overview':
                $counts = Task::selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'inprogress' THEN 1 ELSE 0 END) as inprogress,
                    SUM(CASE WHEN status = 'review' THEN 1 ELSE 0 END) as review,
                    SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN deadline < NOW() AND status NOT IN ('completed','published') THEN 1 ELSE 0 END) as overdue,
                    SUM(CASE WHEN assigned_to IS NULL THEN 1 ELSE 0 END) as unassigned,
                    SUM(CASE WHEN status = 'completed' AND updated_at >= ? THEN 1 ELSE 0 END) as completed_week
                ", [$today->copy()->startOfWeek()->toDateString()])->first();

                return response()->json([
                    'totalTasks'     => (int) ($counts->total ?? 0),
                    'inProgress'     => (int) ($counts->inprogress ?? 0),
                    'pendingReview'  => (int) ($counts->review ?? 0),
                    'completedWeek'  => (int) ($counts->completed_week ?? 0),
                    'delayedTasks'   => (int) ($counts->overdue ?? 0),
                    'activeClients'  => Client::count(),
                    'todoTasks'      => (int) ($counts->todo ?? 0),
                    'unassignedTasks'=> (int) ($counts->unassigned ?? 0),
                    'publishedTasks' => (int) ($counts->published ?? 0),
                ]);

            case 'tasks':
                $query = Task::with(['client', 'assignee']);
                $validStatuses = Task::STATUSES;

                if ($status === 'overdue') {
                    $query->where('deadline', '<', now())->whereNotIn('status', ['completed', 'published']);
                } elseif ($status === 'unassigned') {
                    $query->whereNull('assigned_to');
                } elseif ($status === 'today') {
                    $query->whereDate('deadline', $today)->whereNotIn('status', ['completed', 'published']);
                } elseif ($status === 'completed_week') {
                    $query->where('status', 'completed')->whereBetween('updated_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()->endOfDay()]);
                } elseif ($status && $status !== 'all' && in_array($status, $validStatuses)) {
                    $query->where('status', $status);
                } else {
                    $query->whereNotIn('status', ['completed', 'published']);
                }

                if ($search) {
                    $query->where(fn ($q) => $q
                        ->where('title', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('assignee', fn ($a) => $a->where('name', 'like', "%{$search}%"))
                    );
                }

                $tasks = $query->orderBy('deadline')->get()->map(fn ($task) => [
                    'id'       => $task->id,
                    'title'    => $task->title,
                    'client'   => $task->client?->name ?? 'N/A',
                    'assignee' => $task->assignee?->name ?? 'Unassigned',
                    'status'   => $task->status,
                    'priority' => $task->priority ?? 'normal',
                    'date'     => $task->deadline?->format('d M Y') ?? 'No deadline',
                    'overdue'  => $task->deadline && $task->deadline->isPast(),
                ]);

                return response()->json(['tasks' => $tasks, 'total' => $tasks->count()]);

            case 'clients':
                $query = Client::withCount('tasks')->orderByDesc('tasks_count');
                if ($search) {
                    $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                }
                $clients = $query->get()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'tasks' => $c->tasks_count ?? 0, 'phone' => $c->phone ?? null, 'email' => $c->email ?? null]);
                return response()->json(['clients' => $clients, 'total' => $clients->count()]);

            case 'reports':
                $totalTasks = Task::count();
                $completed  = Task::where('status', 'completed')->count();
                return response()->json([
                    'totalTasks'     => $totalTasks,
                    'todoTasks'      => Task::where('status', 'todo')->count(),
                    'inProgress'     => Task::where('status', 'inprogress')->count(),
                    'reviewTasks'    => Task::where('status', 'review')->count(),
                    'unassignedTasks' => Task::whereNull('assigned_to')->count(),
                    'completedWeek'  => Task::where('status', 'completed')->whereBetween('updated_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()->endOfDay()])->count(),
                    'completedTotal' => $completed,
                    'publishedTotal' => Task::where('status', 'published')->count(),
                    'delayedTasks'   => Task::where('deadline', '<', now())->whereNotIn('status', ['completed', 'published'])->count(),
                ]);

            default:
                return response()->json(['error' => 'Invalid tab'], 400);
        }
    }

    public function getChatbotWizardData()
    {
        return response()->json([
            'clients' => Client::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'emoji', 'logo']),
            'users'   => User::whereIn('role', ['designer', 'strategist', 'manager', 'editor', 'content_writer'])
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
        ]);
    }

    private function calcTrendPct(int $current, int $previous): array
    {
        if ($previous === 0) {
            return ['pct' => $current > 0 ? 100 : 0, 'dir' => $current > 0 ? 'up' : 'neutral'];
        }
        $pct = round((($current - $previous) / $previous) * 100);
        return ['pct' => abs($pct), 'dir' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'neutral')];
    }

    private function buildCacheKey(Request $request): string
    {
        $parts = [
            'client_id'   => (string) $request->input('client_id', ''),
            'assigned_to' => (string) $request->input('assigned_to', ''),
            'status'      => (string) $request->input('status', ''),
            'priority'    => (string) $request->input('priority', ''),
            'platform'    => (string) $request->input('platform', ''),
            'date_range'  => (string) $request->input('date_range', ''),
            'day'         => now()->format('Y-m-d'),
        ];

        return 'admin:dashboard:' . sha1(json_encode($parts));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getRangeStart(string $dateRange, Carbon $today): Carbon
    {
        return match ($dateRange) {
            'today'        => $today->copy()->startOfDay(),
            'yesterday'    => $today->copy()->subDay()->startOfDay(),
            'last_week'    => $today->copy()->subWeek()->startOfWeek(),
            'this_month'   => $today->copy()->startOfMonth(),
            'last_month'   => $today->copy()->subMonth()->startOfMonth(),
            'this_quarter' => $today->copy()->firstOfQuarter(),
            'this_year'    => $today->copy()->startOfYear(),
            default        => $today->copy()->startOfWeek(), // this_week
        };
    }

    private function getRangeEnd(string $dateRange, Carbon $today): Carbon
    {
        return match ($dateRange) {
            'today'        => $today->copy()->endOfDay(),
            'yesterday'    => $today->copy()->subDay()->endOfDay(),
            'last_week'    => $today->copy()->subWeek()->endOfWeek()->endOfDay(),
            'this_month'   => $today->copy()->endOfMonth()->endOfDay(),
            'last_month'   => $today->copy()->subMonth()->endOfMonth()->endOfDay(),
            'this_quarter' => $today->copy()->lastOfQuarter()->endOfDay(),
            'this_year'    => $today->copy()->endOfYear()->endOfDay(),
            default        => $today->copy()->endOfWeek()->endOfDay(),
        };
    }

    private function applyFilters($query, $clientId, $assignedTo, $status, $priority, $platform, $rangeStart, $rangeEnd, $dateRange)
    {
        if ($clientId)   $query->where('client_id', $clientId);
        if ($assignedTo) $query->where('assigned_to', $assignedTo);
        if ($status)     $query->where('status', $status);
        if ($priority)   $query->where('priority', $priority);
        if ($platform)   $query->whereJsonContains('platform', $platform);
        if ($dateRange)  $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        return $query;
    }

    private function applyFiltersNoStatus($query, $clientId, $assignedTo, $priority, $platform, $rangeStart, $rangeEnd, $dateRange)
    {
        if ($clientId)   $query->where('client_id', $clientId);
        if ($assignedTo) $query->where('assigned_to', $assignedTo);
        if ($priority)   $query->where('priority', $priority);
        if ($platform)   $query->whereJsonContains('platform', $platform);
        if ($dateRange)  $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        return $query;
    }

    private function getActiveClientsCount($clientId, $assignedTo, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd): int
    {
        return Client::where('is_active', true)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))
            ->when($assignedTo || $status || $priority || $platform || $dateRange, fn ($q) =>
                $q->whereHas('tasks', function ($tq) use ($assignedTo, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd) {
                    if ($assignedTo) $tq->where('assigned_to', $assignedTo);
                    if ($status)     $tq->where('status', $status);
                    if ($priority)   $tq->where('priority', $priority);
                    if ($platform)   $tq->whereJsonContains('platform', $platform);
                    if ($dateRange)  $tq->whereBetween('created_at', [$rangeStart, $rangeEnd]);
                })
            )
            ->count();
    }

    private function getDelayedTasksCount(?string $filterStatus, callable $applyFiltersNoStatus): int
    {
        if (in_array($filterStatus, ['completed', 'published'])) return 0;

        $q = $filterStatus
            ? Task::where('deadline', '<', now())->where('status', $filterStatus)
            : Task::where('deadline', '<', now())->whereNotIn('status', ['completed', 'published']);

        return $applyFiltersNoStatus($q)->count();
    }

    private function getCompletedCount(?string $filterStatus, callable $applyFiltersNoStatus, $dateOrRange): int
    {
        if ($filterStatus && !in_array($filterStatus, ['completed', 'published'])) return 0;

        $q = Task::whereIn('status', ['completed', 'published']);

        if ($dateOrRange instanceof Carbon) {
            $q->whereDate('updated_at', $dateOrRange);
        } elseif (is_array($dateOrRange)) {
            $q->whereBetween('updated_at', $dateOrRange);
        }

        return $applyFiltersNoStatus($q)->count();
    }

    private function getStatusCount(string $status, ?string $filterStatus, callable $applyFiltersNoStatus): int
    {
        if ($filterStatus && $filterStatus !== $status) return 0;
        return $applyFiltersNoStatus(Task::byStatus($status))->count();
    }

    private function getWeeklyTrend(callable $applyFilters, callable $applyFiltersNoStatus, Carbon $weekStart, ?string $filterStatus): array
    {
        $trend = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $trend[] = [
                'day'       => $day->format('D'),
                'date'      => $day->format('M d'),
                'created'   => $applyFilters(Task::whereDate('created_at', $day))->count(),
                'completed' => ($filterStatus && $filterStatus !== 'completed') ? 0
                    : $applyFiltersNoStatus(Task::where('status', 'completed')->whereDate('updated_at', $day))->count(),
            ];
        }
        return $trend;
    }

    private function getStatusBreakdown(int $todo, int $inProgress, int $pendingReview, ?string $filterStatus, callable $applyFiltersNoStatus): array
    {
        $completed = ($filterStatus && $filterStatus !== 'completed') ? 0
            : $applyFiltersNoStatus(Task::byStatus('completed'))->count();

        return [
            'todo'       => $todo,
            'inprogress' => $inProgress,
            'review'     => $pendingReview,
            'completed'  => $completed,
        ];
    }

    private function getPriorityBreakdown(callable $applyFiltersNoStatus, ?string $filterStatus): array
    {
        $build = function (string $priority) use ($applyFiltersNoStatus, $filterStatus) {
            $q = Task::where('priority', $priority);
            $filterStatus
                ? $q->where('status', $filterStatus)
                : $q->whereNotIn('status', ['completed', 'published']);
            return $applyFiltersNoStatus($q)->count();
        };

        return ['urgent' => $build('urgent'), 'high' => $build('high'), 'normal' => $build('normal')];
    }

    private function getClientPerformance($assignedTo, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd, $clientId)
    {
        return Client::where('is_active', true)
            ->select('id', 'name', 'emoji', 'logo')
            ->withCount([
                'tasks' => fn ($q) => $this->applyTaskFilters($q, $assignedTo, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd),
                'tasks as completed_tasks' => fn ($q) => $this->applyTaskFilters($q->where('status', 'completed'), $assignedTo, null, $priority, $platform, $dateRange, $rangeStart, $rangeEnd),
                'tasks as pending_tasks'   => fn ($q) => $this->applyTaskFilters($q->whereIn('status', ['todo', 'inprogress', 'review']), $assignedTo, null, $priority, $platform, $dateRange, $rangeStart, $rangeEnd),
                'tasks as overdue_tasks'   => fn ($q) => $this->applyTaskFilters($q->where('deadline', '<', now())->where('status', '!=', 'completed'), $assignedTo, null, $priority, $platform, $dateRange, $rangeStart, $rangeEnd),
            ])
            ->when($clientId, fn ($q) => $q->where('id', $clientId))
            ->having('tasks_count', '>', 0)
            ->orderByDesc('tasks_count')
            ->limit(10)
            ->get();
    }

    private function getUpcomingDeadlines(Carbon $today, ?string $filterStatus, array $baseConditions)
    {
        $q = Task::select('id', 'title', 'client_id', 'assigned_to', 'status', 'type', 'platform', 'deadline')
            ->with([
                'client:id,name,logo,emoji',
                'assignee:id,name,avatar_color',
            ])
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$today, $today->copy()->addDays(7)]);

        if (!empty($baseConditions)) {
            $q->where($baseConditions);
        }

        $filterStatus
            ? $q->where('status', $filterStatus)
            : $q->whereNotIn('status', ['completed', 'published']);

        return $q->orderBy('deadline')->limit(10)->get();
    }

    private function getOverdueTasks(?string $filterStatus, array $baseConditions)
    {
        if (in_array($filterStatus, ['completed', 'published'])) return collect();

        $q = Task::select('id', 'title', 'client_id', 'assigned_to', 'status', 'type', 'platform', 'deadline')
            ->with([
                'client:id,name,logo,emoji',
                'assignee:id,name,avatar_color',
            ])
            ->where('deadline', '<', now());

        if (!empty($baseConditions)) {
            $q->where($baseConditions);
        }

        $filterStatus
            ? $q->where('status', $filterStatus)
            : $q->whereNotIn('status', ['completed', 'published']);

        return $q->orderBy('deadline')->limit(8)->get();
    }

    private function getRecentActivity($assignedTo, $clientId, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd)
    {
        return AuditLog::select('id', 'user_id', 'task_id', 'action', 'created_at')
            ->with([
                'user:id,name',
                'task:id,title,client_id',
            ])
            ->when($assignedTo, fn ($q) => $q->where('user_id', $assignedTo))
            ->when($clientId || $status || $priority || $platform || $dateRange, fn ($q) =>
                $q->whereHas('task', function ($tq) use ($clientId, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd) {
                    if ($clientId)  $tq->where('client_id', $clientId);
                    if ($status)    $tq->where('status', $status);
                    if ($priority)  $tq->where('priority', $priority);
                    if ($platform)  $tq->whereJsonContains('platform', $platform);
                    if ($dateRange) $tq->whereBetween('created_at', [$rangeStart, $rangeEnd]);
                })
            )
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getAlerts(?string $filterStatus, callable $applyFiltersNoStatus)
    {
        $q = Task::select('id', 'title', 'client_id', 'status', 'type', 'platform', 'deadline')
            ->with('client:id,name,logo,emoji');

        if ($filterStatus) {
            $q->where('status', $filterStatus);
            if ($filterStatus !== 'review') {
                $q->where('deadline', '<', now());
            }
        } else {
            $q->where(fn ($q2) => $q2
                ->where('status', 'review')
                ->orWhere(fn ($q3) => $q3->where('deadline', '<', now())->where('status', '!=', 'completed'))
            );
        }

        return $applyFiltersNoStatus($q)->oldest()->take(5)->get();
    }

    private function getEmployees($assignedTo, $clientId, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd)
    {
        return User::whereIn('role', ['designer', 'strategist'])
            ->select('id', 'name', 'avatar_color', 'role')
            ->when($assignedTo, fn ($q) => $q->where('id', $assignedTo))
            ->withCount([
                'assignedTasks' => fn ($q) => $this->applyTaskFilters($q, $clientId, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd),
                'assignedTasks as completed_tasks_count' => fn ($q) => $this->applyTaskFilters($q->where('status', 'completed'), $clientId, null, $priority, $platform, $dateRange, $rangeStart, $rangeEnd),
            ])
            ->get();
    }

    private function getWorkloadByDesigner(Carbon $today, $assignedTo, $clientId, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd)
    {
        return User::where('role', 'designer')
            ->select('id', 'name', 'avatar_color')
            ->when($assignedTo, fn ($q) => $q->where('id', $assignedTo))
            ->withCount([
                'assignedTasks as active_tasks_count' => fn ($q) => $this->applyTaskFilters(
                    $status ? $q->where('status', $status) : $q->whereIn('status', ['todo', 'inprogress', 'review']),
                    $clientId, null, $priority, $platform, $dateRange, $rangeStart, $rangeEnd
                ),
                'assignedTasks as due_soon_tasks_count' => fn ($q) => $this->applyTaskFilters(
                    ($status ? $q->where('status', $status) : $q->whereIn('status', ['todo', 'inprogress', 'review']))->whereDate('deadline', '<=', $today->copy()->addDays(2)),
                    $clientId, null, $priority, $platform, $dateRange, $rangeStart, $rangeEnd
                ),
                'assignedTasks as overdue_tasks_count' => fn ($q) => $this->applyTaskFilters(
                    ($status ? $q->where('status', $status) : $q->whereIn('status', ['todo', 'inprogress', 'review']))->whereDate('deadline', '<', $today),
                    $clientId, null, $priority, $platform, $dateRange, $rangeStart, $rangeEnd
                ),
            ])
            ->orderByDesc('active_tasks_count')
            ->get()
            ->map(function (User $d) {
                $d->load_level = match (true) {
                    $d->active_tasks_count >= 10 => 'High',
                    $d->active_tasks_count >= 5  => 'Medium',
                    default                      => 'Low',
                };
                $d->load_color = match ($d->load_level) {
                    'High'   => 'var(--red)',
                    'Medium' => 'var(--yellow)',
                    default  => 'var(--teal)',
                };
                return $d;
            });
    }

    private function getDesignerEfficiency($assignedTo, $clientId, $priority, $platform, $dateRange, $rangeStart, $rangeEnd)
    {
        return User::where('role', 'designer')
            ->select('id', 'name', 'avatar_color')
            ->when($assignedTo, fn ($q) => $q->where('id', $assignedTo))
            ->withCount([
                'assignedTasks as total_assigned' => fn ($q) => $this->applyTaskFilters($q, $clientId, null, $priority, $platform, $dateRange, $rangeStart, $rangeEnd),
                'assignedTasks as total_completed' => fn ($q) => $this->applyTaskFilters($q->where('status', 'completed'), $clientId, null, $priority, $platform, $dateRange, $rangeStart, $rangeEnd),
            ])
            ->addSelect(['avg_turnaround_hours' => Task::selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at))')
                ->whereColumn('assigned_to', 'users.id')
                ->where('status', 'completed')
                ->when($clientId,  fn ($q) => $q->where('client_id', $clientId))
                ->when($priority,  fn ($q) => $q->where('priority', $priority))
                ->when($platform,  fn ($q) => $q->whereJsonContains('platform', $platform))
                ->when($dateRange, fn ($q) => $q->whereBetween('created_at', [$rangeStart, $rangeEnd])),
            ])
            ->get()
            ->filter(fn ($d) => $d->total_assigned > 0)
            ->map(function (User $d) {
                $d->completion_pct  = round(($d->total_completed / max(1, $d->total_assigned)) * 100);
                $d->avg_turnaround  = $d->avg_turnaround_hours ? round($d->avg_turnaround_hours / 24, 1) : null;
                return $d;
            })
            ->sortByDesc('completion_pct')
            ->values();
    }

    private function getUnassignedTasks(?string $filterStatus, callable $applyFiltersNoStatus): int
    {
        if ($filterStatus && !in_array($filterStatus, ['todo', 'inprogress'])) return 0;

        $q = $filterStatus
            ? Task::whereNull('assigned_to')->where('status', $filterStatus)
            : Task::whereNull('assigned_to')->whereIn('status', ['todo', 'inprogress']);

        return $applyFiltersNoStatus($q)->count();
    }

    // Shared filter applier for task sub-queries (used in withCount closures)
    private function applyTaskFilters($query, $clientId, $status, $priority, $platform, $dateRange, $rangeStart, $rangeEnd)
    {
        if ($clientId)  $query->where('client_id', $clientId);
        if ($status)    $query->where('status', $status);
        if ($priority)  $query->where('priority', $priority);
        if ($platform)  $query->whereJsonContains('platform', $platform);
        if ($dateRange) $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        return $query;
    }

    private function generateChatbotResponse(string $message): string
    {
        $message = strtolower($message);

        if (preg_match('/(task|work|assignment|todo)/i', $message)) {
            $count = Task::whereNotIn('status', ['completed', 'published'])->count();
            return "You have {$count} active tasks. View the Tasks tab to see all of them.";
        }
        if (preg_match('/(client|customer|account)/i', $message)) {
            $count = Client::count();
            return "You have {$count} clients in the system. Check the Clients tab to manage them.";
        }
        if (preg_match('/(report|stats|analytics|metrics|performance)/i', $message)) {
            return "Check the Reports tab for comprehensive analytics and performance metrics.";
        }
        if (preg_match('/(overdue|late|deadline|behind)/i', $message)) {
            $count = Task::where('deadline', '<', now())->whereNotIn('status', ['completed', 'published'])->count();
            return "You have {$count} overdue tasks. Please address them as soon as possible!";
        }
        if (preg_match('/(team|member|staff|employee|designer|strategist)/i', $message)) {
            return "View the Overview tab to see your team's activity and performance metrics.";
        }

        return "I can help you with tasks, clients, reports, and team information. Use the tabs above to explore!";
    }
}
