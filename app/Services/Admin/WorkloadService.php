<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WorkloadService
{
    public function getIndexData(\Illuminate\Http\Request $request)
    {
        $search     = trim((string) $request->get('search', ''));
        $perPageRaw = $request->get('per_page', 25);
        $perPage    = (int) $perPageRaw;
        $roleValue  = $request->filled('role') ? trim((string) $request->input('role')) : null;

        if ($perPageRaw === 'custom') {
            $perPage = (int) $request->get('per_page_custom', 25);
        }
        if ($perPage < 1) $perPage = 25;

        $now = now();

        // Aggregate task counts once and join to users. This avoids multiple correlated
        // withCount subqueries for every row and improves pagination responsiveness.
        $taskStatsSubquery = DB::table('tasks')
            ->selectRaw('assigned_to as user_id')
            ->selectRaw('COUNT(*) as total_tasks')
            ->selectRaw("SUM(CASE WHEN status = 'inprogress' THEN 1 ELSE 0 END) as inprogress_tasks")
            ->selectRaw("SUM(CASE WHEN deadline < ? AND status != 'completed' THEN 1 ELSE 0 END) as delayed_tasks", [$now])
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks")
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to');

        $query = User::query()
            ->where('users.role', '!=', 'client')
            ->leftJoinSub($taskStatsSubquery, 'task_stats', function ($join) {
                $join->on('task_stats.user_id', '=', 'users.id');
            })
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                'users.avatar_color',
            ])
            ->selectRaw('COALESCE(task_stats.total_tasks, 0) as total_tasks')
            ->selectRaw('COALESCE(task_stats.inprogress_tasks, 0) as inprogress_tasks')
            ->selectRaw('COALESCE(task_stats.delayed_tasks, 0) as delayed_tasks')
            ->selectRaw('COALESCE(task_stats.completed_tasks, 0) as completed_tasks')
            ->orderBy('users.name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'LIKE', "%{$search}%")
                  ->orWhere('users.email', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($roleValue)) {
            $query->where('users.role', $roleValue);
        }

        $employees = $query->paginate($perPage)->appends($request->query());

        $employees->getCollection()->transform(function ($emp) {
            $emp->total_tasks = (int) $emp->total_tasks;
            $emp->inprogress_tasks = (int) $emp->inprogress_tasks;
            $emp->delayed_tasks = (int) $emp->delayed_tasks;
            $emp->completed_tasks = (int) $emp->completed_tasks;
            $emp->efficiency = $emp->total_tasks > 0
                ? round(($emp->completed_tasks / $emp->total_tasks) * 100)
                : 0;
            return $emp;
        });

        $statsKey = 'workload_stats_v2_' . md5($search . '|' . ($roleValue ?? 'all'));

        $stats = Cache::remember($statsKey, 60, function () use ($search, $roleValue, $taskStatsSubquery) {
            $aggregate = User::query()
                ->where('users.role', '!=', 'client')
                ->leftJoinSub($taskStatsSubquery, 'task_stats', function ($join) {
                    $join->on('task_stats.user_id', '=', 'users.id');
                })
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->where('users.name', 'LIKE', "%{$search}%")
                           ->orWhere('users.email', 'LIKE', "%{$search}%");
                    });
                })
                ->when(!empty($roleValue), function ($q) use ($roleValue) {
                    $q->where('users.role', $roleValue);
                })
                ->selectRaw('COUNT(users.id) as total_members')
                ->selectRaw('COALESCE(AVG(CASE WHEN COALESCE(task_stats.total_tasks, 0) > 0 THEN (task_stats.completed_tasks * 100.0 / task_stats.total_tasks) ELSE 0 END), 0) as avg_efficiency')
                ->selectRaw('SUM(CASE WHEN COALESCE(task_stats.delayed_tasks, 0) > 0 THEN 1 ELSE 0 END) as overloaded_count')
                ->first();

            return [
                'totalMembers' => (int) ($aggregate->total_members ?? 0),
                'avgEfficiency' => (int) round((float) ($aggregate->avg_efficiency ?? 0)),
                'overloadedCount' => (int) ($aggregate->overloaded_count ?? 0),
            ];
        });

        $totalMembers = $stats['totalMembers'];
        $avgEfficiency = $stats['avgEfficiency'];
        $overloadedCount = $stats['overloadedCount'];

        return compact('employees', 'search', 'perPage', 'perPageRaw', 'avgEfficiency', 'overloadedCount', 'totalMembers');
    }
}

