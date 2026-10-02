<?php

namespace App\Services\Admin;

use App\Models\Task;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportService
{
    public function getReportData(Request $request)
    {
        $range     = $request->input('range', '30');
        $clientId  = $request->input('client_id');
        $designer  = $request->input('designer_id');

        [$rangeStart, $rangeEnd, $prevStart, $prevEnd] = $this->parseRange($range, $request);

        // ── Base query builder helper ─────────────────────────────────
        $base = function (bool $withStatus = true) use ($clientId, $designer) {
            $q = Task::query();
            if ($clientId) $q->where('client_id', $clientId);
            if ($designer)  $q->where('assigned_to', $designer);
            return $q;
        };

        $inRange = fn ($q) => $q->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        $completedInRange = fn ($q) => $q->whereIn('status', ['completed', 'published'])
            ->whereBetween('completed_at', [$rangeStart, $rangeEnd]);

        // ── KPI Cards — single conditional aggregation replaces 12 count() calls ──
        $kpi = $base()->selectRaw("
            COUNT(*) as all_count,
            SUM(CASE WHEN status IN ('completed','published') AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as total_delivered,
            SUM(CASE WHEN status IN ('completed','published') AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as prev_delivered,
            SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as total_created,
            SUM(CASE WHEN status NOT IN ('completed','published') THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN deadline < NOW() AND status NOT IN ('completed','published') THEN 1 ELSE 0 END) as overdue,
            SUM(CASE WHEN type = 'reel' AND status IN ('completed','published') THEN 1 ELSE 0 END) as total_reels,
            SUM(CASE WHEN type = 'post' AND status IN ('completed','published') THEN 1 ELSE 0 END) as total_posts,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status = 'inprogress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status IN ('review','pending_approval') THEN 1 ELSE 0 END) as in_review,
            SUM(CASE WHEN status IN ('completed','published') THEN 1 ELSE 0 END) as completed_overall
        ", [
            $rangeStart, $rangeEnd,
            $prevStart, $prevEnd,
            $rangeStart, $rangeEnd,
        ])->first();

        $totalDelivered  = (int) ($kpi->total_delivered ?? 0);
        $prevDelivered   = (int) ($kpi->prev_delivered ?? 0);
        $totalCreated    = (int) ($kpi->total_created ?? 0);
        $pending         = (int) ($kpi->pending ?? 0);
        $overdue         = (int) ($kpi->overdue ?? 0);
        $totalReels      = (int) ($kpi->total_reels ?? 0);
        $totalPosts      = (int) ($kpi->total_posts ?? 0);
        $publishedCount  = (int) ($kpi->published ?? 0);
        $inProgressCount = (int) ($kpi->in_progress ?? 0);
        $reviewCount     = (int) ($kpi->in_review ?? 0);
        $allActive       = (int) ($kpi->all_count ?? 0);
        $completionRate  = $allActive > 0 ? round(((int) ($kpi->completed_overall ?? 0) / $allActive) * 100) : 0;

        // On-time delivery = completed before or on deadline / total completed with deadline
        $completedWithDeadline = Task::whereIn('status', ['completed', 'published'])
            ->whereNotNull('deadline')->whereNotNull('completed_at');
        if ($clientId) $completedWithDeadline->where('client_id', $clientId);
        if ($designer)  $completedWithDeadline->where('assigned_to', $designer);
        $totalWithDeadline  = $completedWithDeadline->count();
        $onTime             = (clone $completedWithDeadline)->whereColumn('completed_at', '<=', 'deadline')->count();
        $onTimeRate         = $totalWithDeadline > 0 ? round(($onTime / $totalWithDeadline) * 100) : 0;

        // Average revisions
        $avgRevisions       = round(Task::avg('revision_count') ?? 0, 1);

        // Month-over-month delivered trend
        $deliveredTrend     = $prevDelivered > 0
            ? round((($totalDelivered - $prevDelivered) / $prevDelivered) * 100)
            : ($totalDelivered > 0 ? 100 : 0);

        // ── Task Trend Chart (daily) ─────────────────────────────────
        $days = (int) min($rangeEnd->diffInDays($rangeStart) + 1, 90);
        $taskTrends = $this->buildDailyTrend($rangeStart, $days, $clientId, $designer);

        // ── Status Breakdown ─────────────────────────────────────────
        $statusCounts = Task::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusBreakdown = [
            'labels' => ['To Do', 'In Progress', 'Review', 'Pending Approval', 'Completed', 'Published'],
            'series' => [
                (int) ($statusCounts['todo'] ?? 0),
                (int) ($statusCounts['inprogress'] ?? 0),
                (int) ($statusCounts['review'] ?? 0),
                (int) ($statusCounts['pending_approval'] ?? 0),
                (int) ($statusCounts['completed'] ?? 0),
                (int) ($statusCounts['published'] ?? 0),
            ],
        ];

        // ── Platform Breakdown ───────────────────────────────────────
        $platformBreakdown = $this->buildPlatformBreakdown($clientId, $designer);

        // ── Content Types ────────────────────────────────────────────
        $typeCounts = Task::selectRaw('type, COUNT(*) as count')
            ->whereNotNull('type')->where('type', '!=', '')
            ->groupBy('type')->pluck('count', 'type');

        $contentTypes = [
            'labels' => ['Post', 'Reel', 'Story', 'Carousel', 'Video'],
            'series' => [
                (int) ($typeCounts['post'] ?? 0),
                (int) ($typeCounts['reel'] ?? 0),
                (int) ($typeCounts['story'] ?? 0),
                (int) ($typeCounts['carousel'] ?? 0),
                (int) ($typeCounts['video'] ?? 0),
            ],
            'colors' => ['#3B82F6', '#F97316', '#14B8A6', '#EC4899', '#8B5CF6'],
        ];

        // ── Workload by Client (Top 8) ───────────────────────────────
        $topClients = Client::where('is_active', true)
            ->withCount([
                'tasks',
                'tasks as active_tasks_count'    => fn ($q) => $q->whereNotIn('status', ['completed', 'published']),
                'tasks as completed_tasks_count'  => fn ($q) => $q->whereIn('status', ['completed', 'published']),
                'tasks as overdue_tasks_count'    => fn ($q) => $q->where('deadline', '<', now())->whereNotIn('status', ['completed', 'published']),
            ])
            ->orderByDesc('tasks_count')
            ->take(8)->get();

        $workloadData = [
            'labels'    => $topClients->pluck('name')->toArray(),
            'active'    => $topClients->pluck('active_tasks_count')->toArray(),
            'completed' => $topClients->pluck('completed_tasks_count')->toArray(),
        ];

        // ── Designer Performance Table ────────────────────────────────
        $designerPerformance = User::whereIn('role', ['designer', 'editor', 'content_writer'])
            ->withCount([
                'assignedTasks as total_assigned',
                'assignedTasks as completed_count'   => fn ($q) => $q->whereIn('status', ['completed', 'published']),
                'assignedTasks as inprogress_count'  => fn ($q) => $q->where('status', 'inprogress'),
                'assignedTasks as overdue_count'     => fn ($q) => $q->where('deadline', '<', now())->whereNotIn('status', ['completed', 'published']),
                'assignedTasks as review_count'      => fn ($q) => $q->whereIn('status', ['review', 'pending_approval']),
            ])
            ->get()
            ->map(function ($u) {
                $rate = $u->total_assigned > 0 ? round(($u->completed_count / $u->total_assigned) * 100) : 0;
                $avgRev = Task::where('assigned_to', $u->id)->avg('revision_count') ?? 0;
                return [
                    'id'             => $u->id,
                    'name'           => $u->name,
                    'role'           => $u->role,
                    'avatar_color'   => $u->avatar_color,
                    'initial'        => strtoupper(substr($u->name, 0, 1)),
                    'total'          => $u->total_assigned,
                    'completed'      => $u->completed_count,
                    'inprogress'     => $u->inprogress_count,
                    'review'         => $u->review_count,
                    'overdue'        => $u->overdue_count,
                    'completion_rate'=> $rate,
                    'avg_revisions'  => round($avgRev, 1),
                ];
            })
            ->sortByDesc('completion_rate')
            ->values();

        // ── Client Performance Table ──────────────────────────────────
        $clientPerformance = Client::where('is_active', true)
            ->withCount([
                'tasks',
                'tasks as completed_count'  => fn ($q) => $q->whereIn('status', ['completed', 'published']),
                'tasks as overdue_count'    => fn ($q) => $q->where('deadline', '<', now())->whereNotIn('status', ['completed', 'published']),
                'tasks as review_count'     => fn ($q) => $q->whereIn('status', ['review', 'pending_approval']),
            ])
            ->get()
            ->filter(fn ($c) => $c->tasks_count > 0)
            ->map(function ($c) {
                $rate = $c->tasks_count > 0 ? round(($c->completed_count / $c->tasks_count) * 100) : 0;
                return [
                    'name'            => $c->name,
                    'emoji'           => $c->emoji,
                    'color'           => $c->color,
                    'total'           => $c->tasks_count,
                    'completed'       => $c->completed_count,
                    'overdue'         => $c->overdue_count,
                    'review'          => $c->review_count,
                    'completion_rate' => $rate,
                ];
            })
            ->sortByDesc('completion_rate')
            ->values();

        // ── Filters metadata for view ────────────────────────────────
        $clients   = Client::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $designers = User::whereIn('role', ['designer', 'editor'])->orderBy('name')->get(['id', 'name', 'role']);

        return compact(
            'totalDelivered', 'prevDelivered', 'deliveredTrend',
            'totalCreated', 'pending', 'overdue',
            'totalReels', 'totalPosts', 'publishedCount',
            'inProgressCount', 'reviewCount',
            'completionRate', 'onTimeRate', 'avgRevisions',
            'taskTrends', 'statusBreakdown', 'platformBreakdown',
            'contentTypes', 'workloadData',
            'designerPerformance', 'clientPerformance',
            'clients', 'designers',
            'range', 'clientId', 'designer',
            'rangeStart', 'rangeEnd',
        );
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function parseRange(string $range, Request $request): array
    {
        $today = Carbon::today();

        switch ($range) {
            case '7':
                $start = $today->copy()->subDays(6)->startOfDay();
                $end   = $today->copy()->endOfDay();
                $pStart = $today->copy()->subDays(13)->startOfDay();
                $pEnd   = $today->copy()->subDays(7)->endOfDay();
                break;
            case '14':
                $start = $today->copy()->subDays(13)->startOfDay();
                $end   = $today->copy()->endOfDay();
                $pStart = $today->copy()->subDays(27)->startOfDay();
                $pEnd   = $today->copy()->subDays(14)->endOfDay();
                break;
            case 'month':
                $start = $today->copy()->startOfMonth();
                $end   = $today->copy()->endOfMonth();
                $pStart = $today->copy()->subMonth()->startOfMonth();
                $pEnd   = $today->copy()->subMonth()->endOfMonth();
                break;
            case 'lastmonth':
                $start = $today->copy()->subMonth()->startOfMonth();
                $end   = $today->copy()->subMonth()->endOfMonth();
                $pStart = $today->copy()->subMonths(2)->startOfMonth();
                $pEnd   = $today->copy()->subMonths(2)->endOfMonth();
                break;
            case '90':
                $start = $today->copy()->subDays(89)->startOfDay();
                $end   = $today->copy()->endOfDay();
                $pStart = $today->copy()->subDays(179)->startOfDay();
                $pEnd   = $today->copy()->subDays(90)->endOfDay();
                break;
            case 'custom':
                $start = Carbon::parse($request->input('date_from', $today->copy()->subDays(29)))->startOfDay();
                $end   = Carbon::parse($request->input('date_to', $today))->endOfDay();
                $diff  = $start->diffInDays($end);
                $pStart = $start->copy()->subDays($diff + 1)->startOfDay();
                $pEnd   = $start->copy()->subDay()->endOfDay();
                break;
            default: // 30
                $start = $today->copy()->subDays(29)->startOfDay();
                $end   = $today->copy()->endOfDay();
                $pStart = $today->copy()->subDays(59)->startOfDay();
                $pEnd   = $today->copy()->subDays(30)->endOfDay();
        }

        return [$start, $end, $pStart, $pEnd];
    }

    private function buildDailyTrend(Carbon $rangeStart, int $days, $clientId, $designer): array
    {
        $categories = [];
        $completed  = [];
        $created    = [];

        // Clamp to max 60 points for readability
        $step = $days > 60 ? (int) ceil($days / 60) : 1;

        $rangeEnd = $rangeStart->copy()->addDays($days - 1)->endOfDay();

        // Single query for completed-per-day across the whole range — replaces N day-loops.
        $completedByDay = Task::selectRaw('DATE(completed_at) as d, COUNT(*) as c')
            ->whereIn('status', ['completed', 'published'])
            ->whereBetween('completed_at', [$rangeStart->copy()->startOfDay(), $rangeEnd])
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($designer, fn ($q) => $q->where('assigned_to', $designer))
            ->groupBy('d')
            ->pluck('c', 'd');

        // Single query for created-per-day.
        $createdByDay = Task::selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->whereBetween('created_at', [$rangeStart->copy()->startOfDay(), $rangeEnd])
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($designer, fn ($q) => $q->where('assigned_to', $designer))
            ->groupBy('d')
            ->pluck('c', 'd');

        for ($i = 0; $i < $days; $i += $step) {
            $day = $rangeStart->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $categories[] = $day->format('M d');
            $completed[]  = (int) ($completedByDay[$key] ?? 0);
            $created[]    = (int) ($createdByDay[$key] ?? 0);
        }

        return compact('categories', 'completed', 'created');
    }

    private function buildPlatformBreakdown($clientId, $designer): array
    {
        $platforms = ['instagram', 'facebook', 'linkedin', 'twitter'];
        $counts    = [];

        foreach ($platforms as $p) {
            $q = Task::where(function ($q) use ($p) {
                $q->where('platform', 'like', "%{$p}%")
                  ->orWhereJsonContains('platform', $p);
            });
            if ($clientId) $q->where('client_id', $clientId);
            if ($designer)  $q->where('assigned_to', $designer);
            $counts[] = $q->count();
        }

        return [
            'labels' => ['Instagram', 'Facebook', 'LinkedIn', 'Twitter / X'],
            'series' => $counts,
            'colors' => ['#E1306C', '#1877F2', '#0A66C2', '#1DA1F2'],
        ];
    }
}
