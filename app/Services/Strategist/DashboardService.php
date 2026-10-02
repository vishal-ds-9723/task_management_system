<?php

namespace App\Services\Strategist;

use App\Models\Client;
use App\Models\ClientMonthlySchedule;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\ShootDay;
use App\Models\SocialMediaPost;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get comprehensive dashboard data for strategist (with fast caching).
     */
    public function getDashboardData(?Request $request = null): array
    {
        $strategistId = Auth::id() ?? 0;
        $cacheKey = 'strategist_dashboard_' . $strategistId . '_' . md5(json_encode($request?->all() ?? []));

        return Cache::remember($cacheKey, 30, function () use ($request, $strategistId) {
            return $this->buildDashboardData($request, $strategistId);
        });
    }

    /**
     * Build the full dashboard data payload using highly optimized SQL queries.
     */
    protected function buildDashboardData(?Request $request, int $strategistId): array
    {
        $user = Auth::user();
        $strategistName = $user?->name ?? 'Strategist';
        $now = now();
        $today = $now->copy()->startOfDay();
        $tomorrow = $today->copy()->addDay();
        $in48Hours = $now->copy()->addHours(48);
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $lastWeekStart = $weekStart->copy()->subWeek();
        $lastWeekEnd = $weekEnd->copy()->subWeek();

        $designers = User::where('role', 'designer')->select('id', 'name', 'avatar_color')->orderBy('name')->get();
        $clients = Client::orderBy('name')->get();

        // Greeting
        $hour = (int) $now->format('H');
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        // Pre-fetch creator IDs for indexed foreign key filtering
        $creatorRoles = ['strategist', 'admin', 'manager', 'editor', 'content_writer'];
        $creatorIds = User::whereIn('role', $creatorRoles)->pluck('id');
        $strategistIds = User::where('role', 'strategist')->pluck('id');

        // Base query for strategist & management tasks
        $base = Task::query()->whereIn('created_by', $creatorIds);

        // Apply filters
        $status = $request?->input('status');
        $clientId = $request?->input('client');
        $creatorId = $request?->input('creator_id');
        $date = $request?->input('date');
        if ($status) $base->where('status', $status);
        if ($clientId) $base->where('client_id', $clientId);
        if ($creatorId) $base->where('created_by', $creatorId);
        if ($date) $base->whereDate('deadline', $date);

        // Core metrics — single conditional aggregation replaces multiple count() calls
        $coreStats = (clone $base)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as content_to_plan,
            SUM(CASE WHEN assigned_to IS NULL AND status != 'completed' THEN 1 ELSE 0 END) as pending_assign,
            SUM(CASE WHEN post_date BETWEEN ? AND ? THEN 1 ELSE 0 END) as posts_week,
            SUM(CASE WHEN status = 'completed' AND DATE(updated_at) = ? THEN 1 ELSE 0 END) as approved_today,
            SUM(CASE WHEN status != 'completed' AND DATE(deadline) < ? THEN 1 ELSE 0 END) as overdue,
            SUM(CASE WHEN status != 'completed' AND ((deadline BETWEEN ? AND ?) OR (post_date BETWEEN ? AND ?)) THEN 1 ELSE 0 END) as due_soon,
            SUM(CASE WHEN status = 'inprogress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'review' THEN 1 ELSE 0 END) as in_review,
            SUM(CASE WHEN assigned_to IS NULL AND status != 'completed' AND ((deadline BETWEEN ? AND ?) OR (post_date BETWEEN ? AND ?)) THEN 1 ELSE 0 END) as unassigned_due_soon
        ", [
            $weekStart, $weekEnd,
            $today,
            $today,
            $now, $in48Hours, $now, $in48Hours,
            $today, $in48Hours, $today, $in48Hours,
        ])->first();

        $totalTasks        = (int) ($coreStats->total ?? 0);
        $completedTasks    = (int) ($coreStats->completed ?? 0);
        $completionRate    = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        $contentToPlan     = (int) ($coreStats->content_to_plan ?? 0);
        $pendingAssign     = (int) ($coreStats->pending_assign ?? 0);
        $postsThisWeek     = (int) ($coreStats->posts_week ?? 0);
        $approvedToday     = (int) ($coreStats->approved_today ?? 0);
        $overdueCount      = (int) ($coreStats->overdue ?? 0);
        $dueSoonCount      = (int) ($coreStats->due_soon ?? 0);
        $inProgressCount   = (int) ($coreStats->in_progress ?? 0);
        $pendingApprovals  = (int) ($coreStats->in_review ?? 0);
        $unassignedDueSoon = (int) ($coreStats->unassigned_due_soon ?? 0);

        // Last week comparisons
        $contentToPlanLastWeek = Task::whereIn('created_by', $strategistIds)->where('status', 'todo')->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();
        $postsThisWeekLast = Task::whereIn('created_by', $strategistIds)->whereBetween('post_date', [$lastWeekStart, $lastWeekEnd])->count();
        $approvalPressure = (clone $base)->where('status', 'review')->where('updated_at', '<=', $now->copy()->subHours(24))->count();

        // Task collections
        $pendingAssignmentTasks = (clone $base)->with(['client'])->whereNull('assigned_to')->where('status', '!=', 'completed')->orderBy('deadline')->take(5)->get();
        $overdueTasks = (clone $base)->with(['client', 'assignee'])->where('status', '!=', 'completed')->whereDate('deadline', '<', $today)->orderBy('deadline')->take(10)->get();
        $dueSoonTasks = (clone $base)->with(['client', 'assignee'])->where('status', '!=', 'completed')->where(function ($q) use ($now, $in48Hours) {
            $q->whereBetween('deadline', [$now, $in48Hours])->orWhereBetween('post_date', [$now, $in48Hours]);
        })->orderByRaw('COALESCE(deadline, post_date) asc')->take(10)->get();
        $inReviewTasks = (clone $base)->with(['client', 'assignee'])->where('status', 'review')->orderBy('updated_at')->take(10)->get();
        $inProgressTasks = (clone $base)->with(['client', 'assignee'])->where('status', 'inprogress')->orderBy('updated_at', 'desc')->take(10)->get();
        $completedTasksList = (clone $base)->with(['client', 'assignee'])->where('status', 'completed')->orderBy('updated_at', 'desc')->take(10)->get();
        $pendingAssignAll = (clone $base)->with(['client'])->whereNull('assigned_to')->where('status', '!=', 'completed')->orderBy('deadline')->take(10)->get();

        // Today tasks
        $todayBase = (clone $base)->with(['client', 'assignee'])->where(function ($q) use ($today) {
            $q->whereDate('deadline', $today)->orWhereDate('post_date', $today);
        })->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal')")->oldest();
        $todayNeedsAssignment = (clone $todayBase)->whereNull('assigned_to')->take(5)->get();
        $todayInProgress = (clone $todayBase)->where('status', 'inprogress')->take(5)->get();
        $todayNeedsReview = (clone $todayBase)->where('status', 'review')->take(5)->get();

        // Upcoming tasks
        $upcomingRaw = (clone $base)->with(['client', 'assignee'])->where('status', '!=', 'completed')->where(function ($q) use ($today) {
            $q->whereDate('deadline', '>=', $today)->orWhereDate('post_date', '>=', $today);
        })->orderByRaw('COALESCE(deadline, post_date) asc')->take(30)->get();

        $upcoming = $upcomingRaw->map(function (Task $task) use ($today) {
            [$effectiveDate, $dateType] = $this->effectiveTaskDate($task, $today);
            $task->effective_date = $effectiveDate;
            $task->effective_date_type = $dateType;
            return $task;
        })->filter(fn (Task $task) => !empty($task->effective_date));

        $upcomingTomorrow = $upcoming->filter(fn (Task $task) => Carbon::parse($task->effective_date)->isSameDay($tomorrow))->take(5)->values();
        $upcomingNextThree = $upcoming->filter(function (Task $task) use ($tomorrow, $today) {
            $d = Carbon::parse($task->effective_date);
            return $d->gt($tomorrow) && $d->lte($today->copy()->addDays(3));
        })->take(5)->values();
        $upcomingLaterWeek = $upcoming->filter(function (Task $task) use ($today, $weekEnd) {
            $d = Carbon::parse($task->effective_date);
            return $d->gt($today->copy()->addDays(3)) && $d->lte($weekEnd);
        })->take(5)->values();

        // Client health — single fast SQL query with subquery aggregations
        $clientHealth = Client::where('is_active', true)
            ->select('id', 'name', 'emoji', 'updated_at')
            ->withCount([
                'tasks as open_count' => fn($q) => $q->whereIn('created_by', $creatorIds)->where('status', '!=', 'completed'),
                'tasks as due_this_week' => fn($q) => $q->whereIn('created_by', $creatorIds)->where('status', '!=', 'completed')->whereBetween('deadline', [$today, $weekEnd]),
                'tasks as overdue' => fn($q) => $q->whereIn('created_by', $creatorIds)->where('status', '!=', 'completed')->whereDate('deadline', '<', $today),
            ])
            ->having('open_count', '>', 0)
            ->orderByDesc('open_count')
            ->take(6)
            ->get()
            ->map(function ($c) {
                return [
                    'client_id' => $c->id,
                    'name' => $c->name ?? 'Unknown Client',
                    'emoji' => $c->emoji ?? '🏢',
                    'open_count' => (int) $c->open_count,
                    'due_this_week' => (int) $c->due_this_week,
                    'overdue' => (int) $c->overdue,
                    'last_activity' => optional($c->updated_at)->diffForHumans() ?? 'Recently',
                ];
            });

        // Platform distribution
        $platformDistribution = [];
        $platformTasks = (clone $base)->where('status', '!=', 'completed')->whereNotNull('platform')->limit(200)->pluck('platform');
        foreach ($platformTasks as $platforms) {
            $platArray = is_array($platforms) ? $platforms : (json_decode($platforms, true) ?: [$platforms]);
            foreach ($platArray as $plat) {
                $key = strtolower(trim($plat));
                if ($key) $platformDistribution[$key] = ($platformDistribution[$key] ?? 0) + 1;
            }
        }
        arsort($platformDistribution);

        // Productivity score
        $productivityScore = min(100, max(0, round((($completedTasks * 3) + ($pendingApprovals * 2) + ($inProgressCount * 1) - ($overdueCount * 2)) / max(1, $totalTasks) * 25)));

        // Week comparisons
        $completedThisWeek = (clone $base)->where('status', 'completed')->whereBetween('updated_at', [$weekStart, $weekEnd])->count();
        $completedLastWeek = (clone $base)->where('status', 'completed')->whereBetween('updated_at', [$lastWeekStart, $lastWeekEnd])->count();
        $avgTasksPerDay = round($completedThisWeek / max(1, $now->diffInDays($weekStart) + 1), 1);
        $createdToday = (clone $base)->whereDate('created_at', $today)->count();

        // Design deadlines
        $designDeadlineOverdue = (clone $base)->where('status', '!=', 'completed')->whereNotNull('design_deadline')->whereDate('design_deadline', '<', $today)->count();
        $designDeadlineSoon = (clone $base)->where('status', '!=', 'completed')->whereNotNull('design_deadline')->whereBetween('design_deadline', [$today, $in48Hours])->count();
        $designDeadlineTasks = (clone $base)->with(['client', 'assignee'])->where('status', '!=', 'completed')->whereNotNull('design_deadline')->whereDate('design_deadline', '<=', $today->copy()->addDays(5))->orderBy('design_deadline')->take(6)->get();

        // Stuck tasks
        $stuckTasks = (clone $base)->with(['client', 'assignee'])->whereIn('status', ['inprogress', 'review', 'todo'])->where('updated_at', '<=', $now->copy()->subDays(3))->orderBy('updated_at')->take(6)->get()->map(function (Task $task) {
            $task->stuck_days = (int) Carbon::parse($task->updated_at)->diffInDays(now());
            return $task;
        });

        // Comments (direct queries via relationship instead of plucking all task IDs)
        $totalComments = Comment::whereHas('task', fn($q) => $q->whereIn('created_by', $creatorIds))->count();
        $commentsThisWeek = Comment::whereHas('task', fn($q) => $q->whereIn('created_by', $creatorIds))->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $mostCommentedTasks = (clone $base)->with('client')->withCount('comments')->having('comments_count', '>', 0)->orderByDesc('comments_count')->take(5)->get();
        $recentComments = Comment::with(['user', 'task.client'])->whereHas('task', fn($q) => $q->whereIn('created_by', $creatorIds))->latest()->take(5)->get();

        // Notifications
        $unreadNotifications = Notification::where('user_id', $strategistId)->whereNull('read_at')->count();

        // Client categories
        $clientCategoryBreakdown = (clone $base)->where('status', '!=', 'completed')->join('clients', 'tasks.client_id', '=', 'clients.id')->selectRaw("COALESCE(clients.category, 'Uncategorized') as category, COUNT(*) as cnt")->groupBy('category')->pluck('cnt', 'category')->toArray();

        // Task completeness — direct SQL queries
        $activeForCompleteness = (clone $base)->where('status', '!=', 'completed');
        $totalActive = (clone $activeForCompleteness)->count();
        $missingCaption = (clone $activeForCompleteness)->where(function ($q) { $q->whereNull('caption')->orWhere('caption', ''); })->count();
        $missingMediaCount = (clone $activeForCompleteness)->doesntHave('media')->count();
        $missingDeadline = (clone $activeForCompleteness)->whereNull('deadline')->count();
        $contentReadiness = $totalActive > 0 ? round((($totalActive - $missingCaption) / $totalActive) * 100) : 100;

        // Week stats
        $createdThisWeek = (clone $base)->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $createdLastWeek = Task::whereIn('created_by', $strategistIds)->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();
        $overdueThisWeek = $overdueCount;
        $overdueLastWeek = Task::whereIn('created_by', $strategistIds)->where('status', '!=', 'completed')->whereDate('deadline', '<', $lastWeekEnd)->whereDate('deadline', '>=', $lastWeekStart)->count();

        // Scheduling pressure
        $schedulingPressure = (clone $base)->with(['client', 'assignee'])->where('status', '!=', 'completed')->whereNotNull('deadline')->whereNotNull('post_date')->whereRaw('DATEDIFF(post_date, deadline) <= 1')->whereDate('post_date', '>=', $today)->orderBy('post_date')->take(5)->get()->map(function (Task $task) {
            $task->gap_days = Carbon::parse($task->deadline)->diffInDays(Carbon::parse($task->post_date), false);
            return $task;
        });

        // Caption fill rate
        $captionFilled = (clone $base)->where('status', '!=', 'completed')->whereNotNull('caption')->where('caption', '!=', '')->count();
        $captionFillRate = $totalActive > 0 ? round(($captionFilled / $totalActive) * 100) : 100;

        // Shoot days
        $shootDays = ShootDay::with(['client', 'creator'])->where('created_by', $strategistId)->where('shoot_date', '>=', $today->copy()->subDays(1))->whereIn('status', ['scheduled', 'in_progress'])->orderBy('shoot_date')->take(10)->get();
        $shootDaysInProgress = ShootDay::where('created_by', $strategistId)->where('status', 'in_progress')->count();
        $shootDaysOverdue = ShootDay::where('created_by', $strategistId)->where('status', 'scheduled')->where('shoot_date', '<', $today)->count();
        $shootDaysCompleted = ShootDay::where('created_by', $strategistId)->where('status', 'completed')->where('shoot_date', '>=', $today->copy()->subDays(7))->count();

        // Publishing
        $myPublishingAwaiting = (clone $base)->where('is_urgent_task', false)->where('status', 'completed')->whereDoesntHave('socialMediaPosts')->count();
        $myPublishingPartial = (clone $base)->where('is_urgent_task', false)->where('status', 'completed')->whereHas('socialMediaPosts')->count();
        $myPublishingDone = (clone $base)->where('is_urgent_task', false)->where('status', 'published')->count();
        $myPublishingTotal = $myPublishingAwaiting + $myPublishingPartial + $myPublishingDone;
        $myRecentPublishing = SocialMediaPost::with(['task.client', 'poster'])
            ->whereHas('task', fn($q) => $q->where('created_by', $strategistId)->where('is_urgent_task', false))
            ->latest()
            ->take(5)
            ->get();
        $myPublishingByPlatform = SocialMediaPost::whereHas('task', fn($q) => $q->where('created_by', $strategistId)->where('is_urgent_task', false))
            ->selectRaw('platform, COUNT(*) as total')
            ->groupBy('platform')
            ->pluck('total', 'platform');

        // Urgent tasks while away
        $urgentTasksWhileAway = Task::with(['client', 'assignee'])->where('is_urgent_task', true)->whereHas('creator', fn($q) => $q->where('role', 'designer'))->where('created_at', '>=', now()->subDays(2))->latest()->take(10)->get();

        // Type distribution
        $typeDistribution = (clone $base)->where('status', '!=', 'completed')->selectRaw("type, count(*) as cnt")->groupBy('type')->pluck('cnt', 'type')->toArray();

        // Dev projects
        $devTypes = ['website', 'software', 'maintenance'];
        $devTasksActive = (clone $base)->whereIn('type', $devTypes)->whereNotIn('status', ['completed', 'published'])->count();
        $devTasksList = (clone $base)
            ->with(['client', 'assignee'])
            ->whereIn('type', $devTypes)
            ->whereNotIn('status', ['completed', 'published'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal')")
            ->orderByRaw('COALESCE(dev_deadline, launch_date, deadline) IS NULL, COALESCE(dev_deadline, launch_date, deadline) ASC')
            ->take(8)
            ->get();

        // Monthly schedule overview (optimized batch query)
        $scheduleOverview = $this->getScheduleOverview();

        return compact(
            'greeting', 'designers', 'strategistName', 'totalTasks', 'completedTasks', 'completionRate', 'contentToPlan', 'pendingAssign', 'postsThisWeek', 'approvedToday',
            'overdueCount', 'dueSoonCount', 'inProgressCount', 'pendingApprovals', 'unassignedDueSoon', 'approvalPressure', 'pendingAssignmentTasks', 'todayNeedsAssignment',
            'todayInProgress', 'todayNeedsReview', 'upcomingTomorrow', 'upcomingNextThree', 'upcomingLaterWeek', 'clientHealth', 'contentToPlanLastWeek', 'postsThisWeekLast',
            'typeDistribution', 'platformDistribution', 'productivityScore', 'completedThisWeek', 'completedLastWeek', 'avgTasksPerDay', 'createdToday', 'designDeadlineOverdue',
            'designDeadlineSoon', 'designDeadlineTasks', 'stuckTasks', 'totalComments', 'commentsThisWeek', 'mostCommentedTasks', 'recentComments', 'unreadNotifications',
            'clientCategoryBreakdown', 'totalActive', 'missingCaption', 'missingMediaCount', 'missingDeadline', 'contentReadiness', 'createdThisWeek', 'createdLastWeek',
            'overdueThisWeek', 'overdueLastWeek', 'schedulingPressure', 'captionFillRate', 'shootDays', 'shootDaysInProgress', 'shootDaysOverdue', 'shootDaysCompleted', 'clients',
            'myPublishingAwaiting', 'myPublishingPartial', 'myPublishingDone', 'myPublishingTotal', 'myRecentPublishing', 'myPublishingByPlatform', 'overdueTasks', 'dueSoonTasks',
            'inReviewTasks', 'inProgressTasks', 'completedTasksList', 'pendingAssignAll', 'urgentTasksWhileAway', 'scheduleOverview',
            'devTasksActive', 'devTasksList'
        );
    }

    private function effectiveTaskDate(Task $task, Carbon $today): array
    {
        $deadline = $task->deadline ? Carbon::parse($task->deadline)->startOfDay() : null;
        $postDate = $task->post_date ? Carbon::parse($task->post_date)->startOfDay() : null;

        if ($deadline && $postDate) {
            $futureDates = collect([
                ['date' => $deadline, 'type' => 'deadline'],
                ['date' => $postDate, 'type' => 'post_date'],
            ])->filter(fn ($entry) => $entry['date']->gte($today));

            $pick = $futureDates->sortBy('date')->first();
            if ($pick) return [$pick['date']->toDateString(), $pick['type']];

            return [$deadline->lte($postDate) ? $deadline->toDateString() : $postDate->toDateString(), $deadline->lte($postDate) ? 'deadline' : 'post_date'];
        }

        if ($deadline) return [$deadline->toDateString(), 'deadline'];
        if ($postDate) return [$postDate->toDateString(), 'post_date'];

        return [null, null];
    }

    private function getScheduleOverview(): array
    {
        $month = now()->month;
        $year = now()->year;
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $activeClients = Client::where('is_active', true)->select('id', 'name', 'emoji')->get();

        if ($activeClients->isEmpty()) {
            return [
                'totalClients' => 0,
                'clientsWithSchedule' => 0,
                'totalPlanned' => 0,
                'totalDone' => 0,
                'totalActive' => 0,
                'overallPct' => 0,
                'topClients' => [],
                'monthLabel' => Carbon::create($year, $month, 1)->format('F Y'),
            ];
        }

        $clientIds = $activeClients->pluck('id');

        // Single query to fetch all relevant schedules
        $allSchedules = ClientMonthlySchedule::whereIn('client_id', $clientIds)
            ->where(function ($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function ($q2) use ($month, $year) {
                      $q2->where('year', $year)->where('month', '<=', $month);
                  });
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->groupBy('client_id');

        // Single query for all task counts in the month
        $taskData = Task::whereIn('client_id', $clientIds)
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('deadline', [$monthStart, $monthEnd])
                  ->orWhereBetween('post_date', [$monthStart, $monthEnd])
                  ->orWhereBetween('created_at', [$monthStart, $monthEnd]);
            })
            ->selectRaw('client_id, type, status, COUNT(*) as cnt')
            ->groupBy('client_id', 'type', 'status')
            ->get();

        $typeMap = [
            'post' => 'posts', 'reel' => 'reels', 'story' => 'stories',
            'carousel' => 'carousel', 'video' => 'videos',
            'guide' => 'guides', 'collection' => 'collections',
        ];

        $totalPlanned = 0;
        $totalDone = 0;
        $totalActive = 0;
        $clientsWithSchedule = 0;
        $topClients = [];

        foreach ($activeClients as $client) {
            $clientSchedules = $allSchedules->get($client->id);
            if (!$clientSchedules || $clientSchedules->isEmpty()) {
                continue;
            }

            // Exact match, else fallback to latest prior
            $schedule = $clientSchedules->first(fn($s) => $s->month == $month && $s->year == $year) ?? $clientSchedules->first();
            if (!$schedule) continue;

            $clientsWithSchedule++;
            $planned = $schedule->getTotalContent();
            $totalPlanned += $planned;

            $clientTasks = $taskData->where('client_id', $client->id);
            $done = 0;
            $active = 0;
            foreach ($typeMap as $taskType => $scheduleKey) {
                $done += $clientTasks->where('type', $taskType)->whereIn('status', ['completed', 'published'])->sum('cnt');
                $active += $clientTasks->where('type', $taskType)->whereNotIn('status', ['completed', 'published'])->sum('cnt');
            }
            $totalDone += $done;
            $totalActive += $active;

            $remaining = max(0, $planned - $done - $active);
            $pct = $planned > 0 ? min(100, round(($done / $planned) * 100)) : 0;

            $topClients[] = [
                'name' => $client->name,
                'emoji' => $client->emoji ?? '🏢',
                'planned' => $planned,
                'done' => (int) $done,
                'active' => (int) $active,
                'remaining' => $remaining,
                'pct' => $pct,
            ];
        }

        // Sort by remaining (most work left first)
        usort($topClients, fn($a, $b) => $b['remaining'] <=> $a['remaining']);
        $topClients = array_slice($topClients, 0, 5);

        $overallPct = $totalPlanned > 0 ? min(100, round(($totalDone / $totalPlanned) * 100)) : 0;

        return [
            'totalClients' => $activeClients->count(),
            'clientsWithSchedule' => $clientsWithSchedule,
            'totalPlanned' => $totalPlanned,
            'totalDone' => $totalDone,
            'totalActive' => $totalActive,
            'overallPct' => $overallPct,
            'topClients' => $topClients,
            'monthLabel' => Carbon::create($year, $month, 1)->format('F Y'),
        ];
    }
}
