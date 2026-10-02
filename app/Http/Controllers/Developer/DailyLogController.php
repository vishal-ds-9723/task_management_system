<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\DailyWorkLog;
use App\Models\DailyWorkLogEntry;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DailyLogController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $date = $this->resolveDate($request->input('date'));

        $log = DailyWorkLog::where('user_id', $userId)
            ->whereDate('log_date', $date)
            ->with('entries.task.client')
            ->first();

        $canEdit = $date->betweenIncluded(today()->subDays(DailyWorkLog::EDIT_WINDOW_DAYS), today());

        // Tasks the developer could have worked on: anything still open, recently finished work,
        // and whatever this day's log already references
        $loggedTaskIds = $log ? $log->entries->pluck('task_id')->filter()->all() : [];

        $tasks = Task::where('assigned_to', $userId)
            ->where(function ($q) use ($loggedTaskIds) {
                $q->whereIn('status', ['todo', 'inprogress', 'review', 'pending_approval', 'on_hold'])
                  ->orWhere('updated_at', '>=', today()->subDays(DailyWorkLog::EDIT_WINDOW_DAYS))
                  ->orWhereIn('id', $loggedTaskIds);
            })
            ->with('client')
            ->orderByRaw("CASE WHEN status = 'inprogress' THEN 0 WHEN status = 'todo' THEN 1 ELSE 2 END")
            ->orderBy('title')
            ->get();

        $weekStart = today()->startOfWeek();
        $monthStart = today()->startOfMonth();

        $hoursThisWeek = (float) DailyWorkLogEntry::whereHas('log', fn ($q) => $q
            ->where('user_id', $userId)
            ->whereBetween('log_date', [$weekStart->toDateString(), today()->toDateString()])
        )->sum('hours');

        $monthLogs = DailyWorkLog::where('user_id', $userId)
            ->whereBetween('log_date', [$monthStart->toDateString(), today()->toDateString()])
            ->withSum('entries', 'hours')
            ->get();

        $daysLoggedThisMonth = $monthLogs->count();
        $avgHoursThisMonth = $daysLoggedThisMonth > 0
            ? round($monthLogs->sum('entries_sum_hours') / $daysLoggedThisMonth, 1)
            : 0;

        $history = DailyWorkLog::where('user_id', $userId)
            ->with('entries.task.client')
            ->orderByDesc('log_date')
            ->paginate(10)
            ->withQueryString();

        return view('developer.daily-log', [
            'date' => $date,
            'log' => $log,
            'canEdit' => $canEdit,
            'tasks' => $tasks,
            'workTypes' => DailyWorkLogEntry::WORK_TYPES,
            'hoursThisWeek' => $hoursThisWeek,
            'daysLoggedThisMonth' => $daysLoggedThisMonth,
            'avgHoursThisMonth' => $avgHoursThisMonth,
            'history' => $history,
        ]);
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'log_date' => [
                'required', 'date',
                'after_or_equal:' . today()->subDays(DailyWorkLog::EDIT_WINDOW_DAYS)->toDateString(),
                'before_or_equal:' . today()->toDateString(),
            ],
            'entries' => ['required', 'array', 'min:1', 'max:30'],
            'entries.*.task_id' => [
                'nullable', 'integer',
                Rule::exists('tasks', 'id')->where('assigned_to', $userId),
            ],
            'entries.*.work_type' => ['required', Rule::in(array_keys(DailyWorkLogEntry::WORK_TYPES))],
            'entries.*.description' => ['required', 'string', 'max:2000'],
            'entries.*.hours' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'entries.*.progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'blockers' => ['nullable', 'string', 'max:2000'],
            'plan_next' => ['nullable', 'string', 'max:2000'],
        ], [
            'log_date.after_or_equal' => 'Logs older than ' . DailyWorkLog::EDIT_WINDOW_DAYS . ' days can no longer be edited.',
            'log_date.before_or_equal' => 'You cannot log work for a future date.',
            'entries.required' => 'Add at least one work entry.',
            'entries.*.task_id.exists' => 'One of the selected tasks is not assigned to you.',
            'entries.*.description.required' => 'Describe what you worked on for every entry.',
            'entries.*.hours.min' => 'Each entry must be at least 0.25 hours (15 minutes).',
        ]);

        $entries = collect($validated['entries']);

        if ($entries->sum('hours') > 24) {
            throw ValidationException::withMessages([
                'entries' => 'Total hours for one day cannot exceed 24.',
            ]);
        }

        $date = Carbon::parse($validated['log_date'])->startOfDay();

        DB::transaction(function () use ($userId, $date, $validated, $entries) {
            $log = DailyWorkLog::where('user_id', $userId)->whereDate('log_date', $date)->first()
                ?? new DailyWorkLog(['user_id' => $userId, 'log_date' => $date->toDateString()]);

            $log->fill([
                'blockers' => $validated['blockers'] ?? null,
                'plan_next' => $validated['plan_next'] ?? null,
            ])->save();

            // The form always submits the full day, so replace rather than diff the entries
            $log->entries()->delete();
            $log->entries()->createMany($entries->map(fn ($entry) => [
                'task_id' => $entry['task_id'] ?? null,
                'work_type' => $entry['work_type'],
                'description' => $entry['description'],
                'hours' => $entry['hours'],
                'progress' => $entry['progress'] ?? null,
            ])->all());
        });

        return redirect()
            ->route('developer.daily-log', ['date' => $date->toDateString()])
            ->with('success', 'Daily log saved for ' . $date->format('M d, Y') . '.');
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
