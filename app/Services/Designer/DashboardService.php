<?php

namespace App\Services\Designer;

use App\Models\Task;
use App\Models\Comment;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function getIndexData()
    {
        $user = Auth::user();
        $userId = $user->id;

        // ── Core Stats — single conditional aggregation replaces 4 count() calls ──
        $today      = today();
        $weekStart  = now()->startOfWeek();
        $weekEnd    = now()->endOfWeek();
        $thisMonth  = now()->month;
        $stats = Task::assignedTo($userId)->selectRaw("
            SUM(CASE WHEN DATE(deadline) = ? THEN 1 ELSE 0 END) as today_count,
            SUM(CASE WHEN deadline BETWEEN ? AND ? THEN 1 ELSE 0 END) as week_count,
            SUM(CASE WHEN status = 'completed' AND MONTH(updated_at) = ? THEN 1 ELSE 0 END) as completed_month,
            SUM(CASE WHEN MONTH(created_at) = ? THEN 1 ELSE 0 END) as total_month
        ", [$today, $weekStart, $weekEnd, $thisMonth, $thisMonth])->first();

        $todayTasks     = (int) ($stats->today_count ?? 0);
        $dueThisWeek    = (int) ($stats->week_count ?? 0);
        $completedMonth = (int) ($stats->completed_month ?? 0);
        $totalMonth     = (int) ($stats->total_month ?? 0);
        $efficiency     = $totalMonth > 0 ? round(($completedMonth / $totalMonth) * 100) : 0;

        // ── Active / In-Progress task (spotlight) ──
        $activeTask = Task::with('client')
            ->assignedTo($userId)
            ->where('status', 'inprogress')
            ->orderBy('deadline')
            ->first();

        // ── Overdue tasks ──
        $overdueTasks = Task::with('client')
            ->assignedTo($userId)
            ->whereNotIn('status', ['completed', 'published'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->orderBy('deadline')
            ->get();

        // ── Today's task list (prioritized: urgent/high first, then earliest deadline) ──
        $todayTasksList = Task::with('client')
            ->assignedTo($userId)
            ->where('status', '!=', 'completed')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal')")
            ->orderBy('deadline')
            ->take(5)->get();

        // ── Revision inbox (tasks sent back with latest revision comment) ──
        $revisionInbox = Task::with(['client', 'comments' => fn($q) => $q->latest()->limit(1)])
            ->assignedTo($userId)
            ->where('status', 'inprogress')
            ->where('revision_count', '>', 0)
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        // ── Upcoming deadlines ──
        $upcoming = Task::with('client')
            ->assignedTo($userId)
            ->where('deadline', '>=', today())
            ->where('status', '!=', 'completed')
            ->orderBy('deadline')
            ->take(5)->get();

        // ── In review tasks ──
        $inReviewCount = Task::assignedTo($userId)->where('status', 'review')->count();

        // ── Revision count (tasks sent back) ──
        $revisionTasks = Task::assignedTo($userId)
            ->where('status', 'inprogress')
            ->where('revision_count', '>', 0)
            ->count();

        // ── Recent comments on my tasks ──
        $recentComments = Comment::with(['user', 'task.client'])
            ->whereHas('task', fn($q) => $q->where('assigned_to', $userId))
            ->where('user_id', '!=', $userId)
            ->latest()
            ->take(5)
            ->get();

        // ── Weekly completions (last 7 days) ──
        $weeklyCompleted = Task::assignedTo($userId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(7))
            ->count();

        // ── Streak: consecutive on-time completions ──
        $streak = 0;
        $recentCompleted = Task::assignedTo($userId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereNotNull('deadline')
            ->latest('completed_at')
            ->take(20)
            ->get();
        foreach ($recentCompleted as $t) {
            if ($t->completed_at->lte($t->deadline->endOfDay())) {
                $streak++;
            } else {
                break;
            }
        }

        
        // ── Paused tasks ──
        $pausedTasks = Task::with('client')
            ->assignedTo($userId)
            ->where('is_paused', true)
            ->get();

        // ── Clients list for urgent task form ──
        $clients = Client::where('is_active', true)->orderBy('name')->get();

        // ── Active in-progress (non-paused) task for auto-pause checkbox ──
        $currentActiveTask = Task::assignedTo($userId)
            ->where('status', 'inprogress')
            ->where('is_paused', false)
            ->first();

        return compact(
            'todayTasks', 'dueThisWeek', 'completedMonth', 'efficiency',
            'todayTasksList', 'upcoming', 'activeTask', 'overdueTasks',
            'inReviewCount', 'revisionTasks', 'revisionInbox', 'recentComments',
            'weeklyCompleted', 'streak',
            'pausedTasks', 'clients', 'currentActiveTask'
        );
    }
}

