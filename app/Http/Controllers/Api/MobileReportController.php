<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MobileReportController extends Controller
{
    /**
     * Get executive analytics summary.
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $now = Carbon::now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // 1. Completion & Delivery Rates
        $totalCompletedThisMonth = Task::whereIn('status', ['completed', 'published'])
            ->whereBetween('updated_at', [$thisMonthStart, $now])
            ->count();

        $totalCompletedLastMonth = Task::whereIn('status', ['completed', 'published'])
            ->whereBetween('updated_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $onTimeThisMonth = Task::whereIn('status', ['completed', 'published'])
            ->whereBetween('updated_at', [$thisMonthStart, $now])
            ->whereNotNull('deadline')
            ->whereColumn('updated_at', '<=', 'deadline')
            ->count();

        $onTimeRate = $totalCompletedThisMonth > 0 ? round(($onTimeThisMonth / $totalCompletedThisMonth) * 100) : 100;

        // 2. Team Productivity Ranking
        $teamRanking = User::whereIn('role', ['designer', 'developer', 'strategist', 'editor', 'content_writer'])
            ->withCount([
                'assignedTasks as completed_month' => fn($q) => $q->whereIn('status', ['completed', 'published'])->whereBetween('updated_at', [$thisMonthStart, $now]),
                'assignedTasks as active_count' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES),
            ])
            ->orderBy('completed_month', 'desc')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->role,
                    'avatar_color' => $member->avatar_color,
                    'completed_this_month' => (int)$member->completed_month,
                    'active_tasks' => (int)$member->active_count,
                ];
            });

        // 3. Platform Breakdown
        $platformList = ['instagram', 'facebook', 'linkedin', 'twitter'];
        $platforms = [];
        foreach ($platformList as $p) {
            $platforms[$p] = Task::whereBetween('created_at', [$thisMonthStart, $now])
                ->where(function ($q) use ($p) {
                    $q->where('platform', 'like', "%{$p}%")
                      ->orWhereJsonContains('platform', $p);
                })
                ->count();
        }

        // 4. Type Breakdown
        $types = Task::selectRaw('type, COUNT(*) as count')
            ->whereBetween('created_at', [$thisMonthStart, $now])
            ->whereNotNull('type')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // 5. Client Delivery Scores
        $clientScores = Client::where('is_active', true)
            ->withCount([
                'tasks as completed_month' => fn($q) => $q->whereIn('status', ['completed', 'published'])->whereBetween('updated_at', [$thisMonthStart, $now]),
                'tasks as active_count' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES),
            ])
            ->orderBy('completed_month', 'desc')
            ->take(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'emoji' => $c->emoji,
                    'color' => $c->color,
                    'completed_this_month' => (int)$c->completed_month,
                    'active_tasks' => (int)$c->active_count,
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => [
                'completed_this_month' => $totalCompletedThisMonth,
                'completed_last_month' => $totalCompletedLastMonth,
                'on_time_rate' => $onTimeRate,
                'active_clients' => Client::where('is_active', true)->count(),
            ],
            'team_ranking' => $teamRanking,
            'platforms' => $platforms,
            'types' => $types,
            'client_scores' => $clientScores,
        ]);
    }
}
