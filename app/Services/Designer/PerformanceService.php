<?php

namespace App\Services\Designer;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class PerformanceService
{
    public function getIndexData()
    {
        $user = Auth::user();
        $uid = $user->id;

        // ── Current month ──
        $tasksAssigned = Task::assignedTo($uid)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $tasksCompleted = Task::assignedTo($uid)
            ->byStatus('completed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        $efficiency = $tasksAssigned > 0 ? round(($tasksCompleted / $tasksAssigned) * 100) : 0;

        // ── Last month (for deltas) ──
        $lastMonth = now()->subMonth();
        $lastAssigned = Task::assignedTo($uid)
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();
        $lastCompleted = Task::assignedTo($uid)
            ->byStatus('completed')
            ->whereMonth('updated_at', $lastMonth->month)
            ->whereYear('updated_at', $lastMonth->year)
            ->count();
        $lastEfficiency = $lastAssigned > 0 ? round(($lastCompleted / $lastAssigned) * 100) : 0;

        // Deltas
        $deltaAssigned = $tasksAssigned - $lastAssigned;
        $deltaCompleted = $tasksCompleted - $lastCompleted;
        $deltaEfficiency = $efficiency - $lastEfficiency;

        // ── Type breakdown (this month) ──
        $typeBreakdown = Task::assignedTo($uid)
            ->whereMonth('created_at', now()->month)
            ->selectRaw("type, count(*) as count")
            ->groupBy('type')
            ->pluck('count', 'type');

        // Last 7 days completions (for mini sparkline) — single GROUP BY query
        $sevenDaysAgo = now()->subDays(6)->startOfDay();
        $completionsByDay = Task::assignedTo($uid)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $sevenDaysAgo)
            ->selectRaw('DATE(completed_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');
        $weeklyTrend = collect(range(6, 0))->map(function ($offset) use ($completionsByDay) {
            $date = now()->subDays($offset);
            return [
                'label' => $date->format('D'),
                'date'  => $date->format('M j'),
                'count' => (int) ($completionsByDay[$date->toDateString()] ?? 0),
            ];
        });
        $weeklyMax = max($weeklyTrend->max('count'), 1);
        $weeklyCompleted = $weeklyTrend->sum('count');
        $avgDailyCompleted = round($weeklyCompleted / 7, 1);

        // Revision rate this month (reuses $tasksAssigned as denominator)
        $revisedThisMonth = Task::assignedTo($uid)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('revision_count', '>', 0)
            ->count();
        $revisionRate = $tasksAssigned > 0 ? round(($revisedThisMonth / $tasksAssigned) * 100) : 0;

        return compact(
            'tasksAssigned', 'tasksCompleted', 'efficiency',
            'deltaAssigned', 'deltaCompleted', 'deltaEfficiency',
            'typeBreakdown', 'weeklyTrend', 'weeklyMax', 'weeklyCompleted', 'avgDailyCompleted',
            'revisedThisMonth', 'revisionRate'
        );
    }
}

