<?php

namespace App\Services\Strategist;

use App\Models\DailyWorkLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * "Who's doing what": one lane per team member with what they are on now,
 * what they should pick up next, and how loaded they are.
 */
class TeamBoardService
{
    /** Roles that do the work strategists hand out. Designers and developers are always listed. */
    private const CORE_ROLES = ['designer', 'developer'];
    private const OTHER_ROLES = ['editor', 'content_writer', 'strategist', 'manager'];

    /** Active (todo + in progress) tasks at which someone counts as fully loaded. */
    public const CAPACITY = 8;

    private const PRIORITY_RANK = ['urgent' => 0, 'high' => 1, 'normal' => 2, 'medium' => 2, 'low' => 3];

    public function build(?int $clientId, bool $mine, int $userId): array
    {
        $today = today();

        $open = Task::query()
            ->with(['client:id,name,logo,color', 'creator:id,name'])
            ->whereNotNull('assigned_to')
            ->whereIn('status', ['todo', 'inprogress', 'review', 'pending_approval', 'on_hold'])
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($mine, fn ($q) => $q->where('created_by', $userId))
            ->get()
            ->map(fn (Task $task) => $this->describe($task, $today))
            ->groupBy(fn ($item) => $item->task->assigned_to);

        $doneThisWeek = Task::query()
            ->whereNotNull('assigned_to')
            ->whereIn('status', ['completed', 'published'])
            ->where('updated_at', '>=', $today->copy()->startOfWeek())
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($mine, fn ($q) => $q->where('created_by', $userId))
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $hoursToday = DailyWorkLog::query()
            ->whereDate('log_date', $today)
            ->withSum('entries', 'hours')
            ->get()
            ->pluck('entries_sum_hours', 'user_id');

        $people = User::query()
            ->whereIn('role', array_merge(self::CORE_ROLES, self::OTHER_ROLES))
            ->orderBy('name')
            ->get()
            // Strategists/editors only appear when they actually hold tasks
            ->filter(fn (User $user) => in_array($user->role, self::CORE_ROLES, true) || $open->has($user->id))
            ->map(fn (User $user) => $this->lane($user, $open->get($user->id, collect()), (int) ($doneThisWeek[$user->id] ?? 0), $hoursToday[$user->id] ?? null))
            ->values();

        $unassigned = Task::query()
            ->with(['client:id,name,logo,color', 'creator:id,name'])
            ->whereNull('assigned_to')
            ->whereIn('status', ['todo', 'inprogress'])
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($mine, fn ($q) => $q->where('created_by', $userId))
            ->get()
            ->map(fn (Task $task) => $this->describe($task, $today))
            ->sortBy(fn ($item) => $this->queueKey($item))
            ->values();

        return [
            'people' => $people,
            'unassigned' => $unassigned,
            'summary' => [
                'people' => $people->count(),
                'workingNow' => $people->filter(fn ($p) => $p->now->isNotEmpty())->count(),
                'idle' => $people->filter(fn ($p) => $p->active === 0)->count(),
                'overloaded' => $people->where('status', 'overloaded')->count(),
                'overdue' => $people->sum('overdue'),
                'waiting' => $people->sum(fn ($p) => $p->waiting->count()),
                'unassigned' => $unassigned->count(),
            ],
            'capacity' => self::CAPACITY,
        ];
    }

    private function lane(User $user, Collection $items, int $doneThisWeek, $hoursToday): object
    {
        $now = $items->filter(fn ($i) => $i->task->status === 'inprogress')
            ->sortByDesc(fn ($i) => $i->task->started_at?->timestamp ?? 0)->values();
        $next = $items->filter(fn ($i) => $i->task->status === 'todo')
            ->sortBy(fn ($i) => $this->queueKey($i))->values();
        $waiting = $items->filter(fn ($i) => in_array($i->task->status, ['review', 'pending_approval'], true))->values();
        $onHold = $items->filter(fn ($i) => $i->task->status === 'on_hold')->values();

        $active = $now->count() + $next->count();
        $overdue = $now->concat($next)->where('isOverdue', true)->count();

        $status = match (true) {
            $active === 0 => 'available',
            $active >= self::CAPACITY || $overdue >= 3 => 'overloaded',
            $overdue > 0 => 'behind',
            $now->isEmpty() => 'queued',
            default => 'working',
        };

        return (object) [
            'user' => $user,
            'kind' => $user->role,
            'online' => $user->isOnline(),
            'now' => $now,
            'next' => $next,
            'waiting' => $waiting,
            'onHold' => $onHold,
            'active' => $active,
            'overdue' => $overdue,
            'doneThisWeek' => $doneThisWeek,
            'hoursToday' => $hoursToday !== null ? round((float) $hoursToday, 1) : null,
            'load' => min(100, (int) round($active / self::CAPACITY * 100)),
            'status' => $status,
            'freeOn' => $this->freeOn($now->concat($next)),
        ];
    }

    private function describe(Task $task, Carbon $today): object
    {
        $isDev = Task::isDevType($task->type);
        $dueRaw = $isDev ? ($task->dev_deadline ?? $task->deadline) : ($task->deadline ?? $task->post_date);
        $due = $dueRaw ? Carbon::parse($dueRaw)->startOfDay() : null;
        $daysLeft = $due ? (int) $today->diffInDays($due) : null;

        return (object) [
            'task' => $task,
            'due' => $due,
            'daysLeft' => $daysLeft,
            'isOverdue' => $daysLeft !== null && $daysLeft < 0 && in_array($task->status, ['todo', 'inprogress'], true),
            'priorityRank' => self::PRIORITY_RANK[strtolower((string) $task->priority)] ?? 2,
        ];
    }

    /**
     * Order a person should pick work up in: overdue first, then earliest due date,
     * then priority; undated work goes last.
     */
    private function queueKey(object $item): array
    {
        return [
            $item->isOverdue ? 0 : 1,
            $item->due?->timestamp ?? PHP_INT_MAX,
            $item->priorityRank,
            $item->task->created_at?->timestamp ?? 0,
        ];
    }

    /** Latest due date in someone's queue: a rough "free from" date for planning. */
    private function freeOn(Collection $items): ?Carbon
    {
        return $items->pluck('due')->filter()->max();
    }
}
