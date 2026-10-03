<?php

namespace App\Services\Strategist;

use App\Models\Client;
use App\Models\FestivalSelection;
use App\Models\Task;
use App\Support\SocialRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * Delivery states, ordered from "done" to "most at risk".
     * label/icon/tone are used by the view; the order drives sorting inside a cell.
     */
    public const STATES = [
        'done'       => ['label' => 'Posted',            'icon' => 'fa-circle-check',       'tone' => 'green'],
        'ready'      => ['label' => 'Ready to post',     'icon' => 'fa-paper-plane',        'tone' => 'teal'],
        'approval'   => ['label' => 'Awaiting approval', 'icon' => 'fa-user-check',         'tone' => 'violet'],
        'review'     => ['label' => 'In review',         'icon' => 'fa-eye',                'tone' => 'amber'],
        'production' => ['label' => 'In production',     'icon' => 'fa-pen-ruler',          'tone' => 'blue'],
        'notstarted' => ['label' => 'Not started',       'icon' => 'fa-circle',             'tone' => 'slate'],
        'hold'       => ['label' => 'On hold',           'icon' => 'fa-circle-pause',       'tone' => 'slate'],
        'missed'     => ['label' => 'Missed',            'icon' => 'fa-triangle-exclamation', 'tone' => 'red'],
    ];

    /**
     * Get all active clients with strategist task statistics.
     */
    public function getClientsWithStats(): \Illuminate\Database\Eloquent\Collection
    {
        return Client::where('is_active', true)
            ->withCount([
                'tasks as total_tasks' => fn($q) => $q->whereHas('creator', fn($sq) => $sq->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin'])),
                'tasks as active_tasks' => fn($q) => $q->whereHas('creator', fn($sq) => $sq->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin']))
                    ->where('status', '!=', 'completed'),
                'tasks as overdue_tasks' => fn($q) => $q->whereHas('creator', fn($sq) => $sq->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin']))
                    ->where('status', '!=', 'completed')
                    ->whereNotNull('deadline')
                    ->where('deadline', '<', now())
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get festival selections for a specific client.
     */
    public function getClientFestivalSelections(Client $client)
    {
        return FestivalSelection::where('client_id', $client->id)
            ->with(['festival', 'user', 'tasks'])
            ->latest()
            ->get();
    }

    /**
     * Everything the content planner needs for one week or one month.
     *
     * Each task is placed on its go-live date (post_date, falling back to deadline) and,
     * for the week grid, split into one card per target platform so every channel shows
     * whether it has actually been posted.
     */
    public function buildPlanner(Carbon $anchor, string $view, ?int $clientId, bool $mine, int $userId): array
    {
        $today = today();

        if ($view === 'month') {
            $periodStart = $anchor->copy()->startOfMonth();
            $periodEnd = $anchor->copy()->endOfMonth()->startOfDay();
            $rangeStart = $periodStart->copy()->startOfWeek();
            $rangeEnd = $periodEnd->copy()->endOfWeek()->startOfDay();
        } else {
            $periodStart = $rangeStart = $anchor->copy()->startOfWeek();
            $periodEnd = $rangeEnd = $anchor->copy()->endOfWeek()->startOfDay();
        }

        $tasks = $this->contentTasks($clientId, $mine, $userId)
            ->where(fn ($q) => $this->planDateBetween($q, $rangeStart, $rangeEnd))
            ->get()
            ->map(fn (Task $task) => $this->describe($task, $today))
            ->sortBy(fn ($item) => [$item->planDate->timestamp, $item->task->client->name ?? '', $item->task->title])
            ->values();

        // Cards: one per task per platform (tasks without a platform get one "general" card)
        $cards = $tasks->flatMap(fn ($item) => collect($item->channels ?: [null])->map(fn ($channel) => (object) [
            'item' => $item,
            'platform' => $channel['platform'] ?? 'general',
            'state' => $channel['state'] ?? $item->state,
            'postUrl' => $channel['postUrl'] ?? null,
            'date' => $item->planDate->toDateString(),
        ]));

        $inPeriod = $cards->filter(fn ($card) => $card->date >= $periodStart->toDateString() && $card->date <= $periodEnd->toDateString());

        return [
            'view' => $view,
            'today' => $today,
            'anchor' => $anchor,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'days' => collect(range(0, $rangeStart->diffInDays($rangeEnd)))->map(fn ($i) => $rangeStart->copy()->addDays($i)),
            'tasks' => $tasks,
            'cards' => $cards,
            'rows' => $this->channelRows($cards),
            'summary' => $this->summarize($inPeriod),
            'attention' => $this->attention($clientId, $mine, $userId, $today),
            'nextPlanned' => $tasks->isEmpty() ? $this->nextPlannedDate($clientId, $mine, $userId, $periodEnd) : null,
        ];
    }

    private function contentTasks(?int $clientId, bool $mine, int $userId): Builder
    {
        return Task::query()
            ->with(['client:id,name,logo,color,emoji', 'assignee:id,name', 'creator:id,name', 'socialMediaPosts'])
            ->whereNotIn('type', Task::DEV_TYPES) // dev work lives in the developer panel
            ->whereDoesntHave('assignee', fn ($q) => $q->where('role', 'developer'))
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($mine, fn ($q) => $q->where('created_by', $userId));
    }

    private function planDateBetween(Builder $q, Carbon $from, Carbon $to): void
    {
        $q->whereBetween('post_date', [$from->toDateString(), $to->toDateString()])
          ->orWhere(fn ($q) => $q->whereNull('post_date')->whereBetween('deadline', [$from->toDateString(), $to->toDateString()]));
    }

    /**
     * Work out where a task really stands: per platform, and overall.
     * Also used by the content plan page so both screens agree on what "posted" means.
     */
    public function describe(Task $task, Carbon $today): object
    {
        $planDate = Carbon::parse($task->post_date ?? $task->deadline)->startOfDay();
        $posts = $task->socialMediaPosts->keyBy(fn ($post) => strtolower((string) $post->platform));
        // Target platforms, plus any platform it was actually posted on so no published work is hidden
        $platforms = collect($task->platformList())->map(fn ($p) => strtolower(trim($p)))
            ->merge($posts->keys())->filter()->unique()->values();

        $statusState = match ($task->status) {
            'published' => 'done',
            'on_hold' => 'hold',
            'completed' => 'ready',
            'pending_approval' => 'approval',
            'review' => 'review',
            'inprogress' => 'production',
            default => 'notstarted',
        };
        // Design-only work (no platform) is finished once it is completed
        if ($platforms->isEmpty() && $task->status === 'completed') {
            $statusState = 'done';
        }

        $isPast = $planDate->lt($today);
        $pendingState = ($isPast && $statusState !== 'done' && $statusState !== 'hold') ? 'missed' : $statusState;

        $channels = $platforms->map(function ($platform) use ($posts, $pendingState, $statusState) {
            $post = $posts->get($platform);

            return [
                'platform' => $platform,
                'state' => $post || $statusState === 'done' ? 'done' : $pendingState,
                'postUrl' => $post?->post_url,
                'postedAt' => $post?->posted_at,
            ];
        })->all();

        $postedCount = collect($channels)->where('state', 'done')->count();
        $overall = $platforms->isNotEmpty() && $postedCount === $platforms->count() ? 'done' : $pendingState;

        return (object) [
            'task' => $task,
            'planDate' => $planDate,
            'isToday' => $planDate->isSameDay($today),
            'state' => $overall,
            'channels' => $channels,
            'postedCount' => $postedCount,
            'platformCount' => $platforms->count(),
        ];
    }

    /**
     * Group cards into the week grid's rows: client first, then one row per channel.
     */
    private function channelRows(Collection $cards): Collection
    {
        $platformOrder = array_flip(array_merge(SocialRegistry::PLATFORMS, ['general']));

        return $cards
            ->groupBy(fn ($card) => $card->item->task->client_id ?? 0)
            ->map(function ($clientCards) use ($platformOrder) {
                $client = $clientCards->first()->item->task->client;

                return (object) [
                    'client' => $client,
                    'channels' => $clientCards
                        ->groupBy('platform')
                        ->sortBy(fn ($group, $platform) => $platformOrder[$platform] ?? 999)
                        ->map(fn ($group, $platform) => (object) [
                            'platform' => $platform,
                            'byDate' => $group->groupBy('date'),
                            'total' => $group->count(),
                            'done' => $group->where('state', 'done')->count(),
                        ]),
                    'total' => $clientCards->count(),
                    'done' => $clientCards->where('state', 'done')->count(),
                ];
            })
            ->sortBy(fn ($row) => $row->client->name ?? 'zzz')
            ->values();
    }

    private function summarize(Collection $cards): array
    {
        $counts = collect(array_keys(self::STATES))->mapWithKeys(fn ($state) => [$state => $cards->where('state', $state)->count()]);
        $total = $cards->count();

        return [
            'total' => $total,
            'counts' => $counts->all(),
            'completion' => $total > 0 ? (int) round($counts['done'] / $total * 100) : 0,
            'tasks' => $cards->pluck('item.task.id')->unique()->count(),
        ];
    }

    /**
     * Action list for the strategist, independent of the period being viewed.
     */
    private function attention(?int $clientId, bool $mine, int $userId, Carbon $today): array
    {
        $items = $this->contentTasks($clientId, $mine, $userId)
            ->where(function ($q) use ($today) {
                $q->where(fn ($q) => $this->planDateBetween($q, $today->copy()->subDays(30), $today->copy()->addDays(2)))
                  ->orWhere('status', 'review');
            })
            ->get()
            ->map(fn (Task $task) => $this->describe($task, $today));

        return [
            'missed' => $items->where('state', 'missed')->sortByDesc(fn ($i) => $i->planDate->timestamp)->values(),
            'atRisk' => $items->filter(fn ($i) => $i->planDate->betweenIncluded($today, $today->copy()->addDays(2))
                    && in_array($i->state, ['notstarted', 'production', 'review', 'approval'], true))
                ->sortBy(fn ($i) => $i->planDate->timestamp)->values(),
            'review' => $items->filter(fn ($i) => $i->task->status === 'review')
                ->sortBy(fn ($i) => $i->planDate->timestamp)->values(),
        ];
    }

    private function nextPlannedDate(?int $clientId, bool $mine, int $userId, Carbon $after): ?Carbon
    {
        $task = $this->contentTasks($clientId, $mine, $userId)
            ->where(function ($q) use ($after) {
                $q->where('post_date', '>', $after->toDateString())
                  ->orWhere(fn ($q) => $q->whereNull('post_date')->where('deadline', '>', $after->toDateString()));
            })
            ->orderByRaw('COALESCE(post_date, deadline) ASC')
            ->first();

        return $task ? Carbon::parse($task->post_date ?? $task->deadline) : null;
    }
}
