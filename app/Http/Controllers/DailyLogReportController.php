<?php

namespace App\Http\Controllers;

use App\Models\DailyWorkLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Team view of developers' daily work logs, shared by admins, strategists and managers.
 */
class DailyLogReportController extends Controller
{
    public function index(Request $request)
    {
        $date = $this->resolveDate($request->input('date'));

        $developers = User::where(function ($q) {
                $q->whereIn('role', ['developer', 'designer'])
                  ->orWhereJsonContains('additional_roles', 'developer')
                  ->orWhereJsonContains('additional_roles', 'designer')
                  ->orWhereHas('dailyWorkLogs');
            })
            ->when($request->filled('user_id'), fn ($q) => $q->where('id', $request->input('user_id')))
            ->orderBy('name')
            ->get();

        $logs = DailyWorkLog::whereIn('user_id', $developers->pluck('id'))
            ->whereDate('log_date', $date)
            ->with('entries.task.client')
            ->get()
            ->keyBy('user_id');

        // Hours per developer for the 7 days ending on the selected date
        $weekDays = collect(range(6, 0))->map(fn ($i) => $date->copy()->subDays($i));

        $weekHours = DailyWorkLog::whereIn('user_id', $developers->pluck('id'))
            ->whereBetween('log_date', [$weekDays->first()->toDateString(), $date->toDateString()])
            ->withSum('entries', 'hours')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($userLogs) => $userLogs->mapWithKeys(fn ($log) => [
                $log->log_date->toDateString() => (float) $log->entries_sum_hours,
            ]));

        $summary = [
            'developers' => $developers->count(),
            'submitted' => $logs->count(),
            'hours' => round($logs->sum(fn ($log) => $log->totalHours()), 2),
            'blockers' => $logs->filter(fn ($log) => filled($log->blockers))->count(),
        ];

        return view('daily-logs.report', [
            'date' => $date,
            'developers' => $developers,
            'allDevelopers' => User::where(function ($q) {
                $q->whereIn('role', ['developer', 'designer'])
                  ->orWhereJsonContains('additional_roles', 'developer')
                  ->orWhereJsonContains('additional_roles', 'designer')
                  ->orWhereHas('dailyWorkLogs');
            })->orderBy('name')->get(['id', 'name']),
            'logs' => $logs,
            'weekDays' => $weekDays,
            'weekHours' => $weekHours,
            'summary' => $summary,
        ]);
    }

    private function resolveDate(?string $input): Carbon
    {
        if (!$input) {
            return today();
        }

        try {
            $date = Carbon::parse($input)->startOfDay();
        } catch (\Throwable $e) {
            return today();
        }

        return $date->isFuture() ? today() : $date;
    }
}
