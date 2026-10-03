<?php

namespace App\Services\Strategist;

use App\Models\Client;
use App\Models\ClientMonthlySchedule;
use App\Models\Task;
use Illuminate\Support\Carbon;

/**
 * Monthly content plan vs. reality for every client: what was promised in the package,
 * how many tasks exist for it, and how many actually went live.
 */
class ContentPlanService
{
    /** Plan columns on client_monthly_schedules, in display order. */
    public const PLAN_TYPES = [
        'posts'       => ['label' => 'Posts',       'icon' => 'fa-image',       'color' => '#3B82F6', 'taskType' => 'post'],
        'reels'       => ['label' => 'Reels',       'icon' => 'fa-film',        'color' => '#F97316', 'taskType' => 'reel'],
        'stories'     => ['label' => 'Stories',     'icon' => 'fa-mobile-screen', 'color' => '#10B981', 'taskType' => 'story'],
        'carousel'    => ['label' => 'Carousels',   'icon' => 'fa-images',      'color' => '#EC4899', 'taskType' => 'carousel'],
        'videos'      => ['label' => 'Videos',      'icon' => 'fa-video',       'color' => '#8B5CF6', 'taskType' => 'video'],
        'guides'      => ['label' => 'Guides',      'icon' => 'fa-compass',     'color' => '#06B6D4', 'taskType' => null],
        'collections' => ['label' => 'Collections', 'icon' => 'fa-folder-open', 'color' => '#F59E0B', 'taskType' => null],
        'other'       => ['label' => 'Other / Print', 'icon' => 'fa-shapes',    'color' => '#64748B', 'taskType' => null],
    ];

    /** Task type → plan column. Anything unlisted (brochure, banner, flyer, others…) counts as "other". */
    private const TYPE_TO_PLAN = [
        'post' => 'posts', 'reel' => 'reels', 'story' => 'stories', 'carousel' => 'carousel',
        'video' => 'videos', 'guide' => 'guides', 'collection' => 'collections',
    ];

    public const STATUSES = [
        'complete'    => ['label' => 'Complete',    'tone' => 'green', 'icon' => 'fa-circle-check'],
        'on_track'    => ['label' => 'On track',    'tone' => 'blue',  'icon' => 'fa-gauge-high'],
        'needs_tasks' => ['label' => 'Needs tasks', 'tone' => 'amber', 'icon' => 'fa-list-check'],
        'behind'      => ['label' => 'Behind',      'tone' => 'red',   'icon' => 'fa-triangle-exclamation'],
        'no_plan'     => ['label' => 'No plan',     'tone' => 'slate', 'icon' => 'fa-calendar-xmark'],
    ];

    public function __construct(private CalendarService $calendar)
    {
    }

    public function build(int $month, int $year): array
    {
        $today = today();
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();
        $isPastMonth = $monthEnd->lt($today);
        $isFutureMonth = $monthStart->gt($today);

        $clients = Client::where('is_active', true)->orderBy('name')->get();

        // Content tasks going live this month (same placement rule as the content calendar)
        $tasksByClient = Task::query()
            ->with(['client:id,name', 'assignee:id,name', 'socialMediaPosts'])
            ->whereNotIn('type', Task::DEV_TYPES)
            ->whereDoesntHave('assignee', fn ($q) => $q->where('role', 'developer'))
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('post_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                  ->orWhere(fn ($q) => $q->whereNull('post_date')->whereBetween('deadline', [$monthStart->toDateString(), $monthEnd->toDateString()]));
            })
            ->get()
            ->map(fn (Task $task) => $this->calendar->describe($task, $today))
            ->groupBy(fn ($item) => $item->task->client_id);


        $rows = $clients->map(function (Client $client) use ($month, $year, $tasksByClient, $isPastMonth, $today) {
            $schedule = ClientMonthlySchedule::getEffectiveSchedule($client->id, $month, $year);
            $isCarried = $schedule && !($schedule->month === $month && $schedule->year === $year);
            $items = $tasksByClient->get($client->id, collect());

            $types = collect(self::PLAN_TYPES)->map(function ($meta, $key) use ($schedule, $items) {
                $typeItems = $items->filter(fn ($item) => (self::TYPE_TO_PLAN[$item->task->type] ?? 'other') === $key);
                $planned = (int) ($schedule?->{$key} ?? 0);
                $created = $typeItems->count();
                $count = fn (array $states) => $typeItems->filter(fn ($i) => in_array($i->state, $states, true))->count();

                return (object) [
                    'key' => $key,
                    'meta' => $meta,
                    'planned' => $planned,
                    'created' => $created,
                    'posted' => $count(['done']),
                    'ready' => $count(['ready']),
                    'inProduction' => $count(['approval', 'review', 'production', 'notstarted', 'hold']),
                    'missed' => $count(['missed']),
                    'toCreate' => max(0, $planned - $created),
                    'extra' => max(0, $created - $planned),
                    // The actual pieces, earliest go-live first, for the detail popup
                    'items' => $typeItems->sortBy(fn ($i) => $i->planDate->timestamp)->values(),
                ];
            })->filter(fn ($t) => $t->planned > 0 || $t->created > 0);

            $planned = $types->sum('planned');
            $toCreate = $types->sum('toCreate');
            $missed = $types->sum('missed');

            $status = match (true) {
                !$schedule || $planned === 0 => 'no_plan',
                $types->sum('posted') >= $planned => 'complete',
                $missed > 0 || ($isPastMonth && $types->sum('posted') < $planned) => 'behind',
                $toCreate > 0 && !$isPastMonth => 'needs_tasks',
                default => 'on_track',
            };

            $next = $items->filter(fn ($i) => $i->state !== 'done' && $i->planDate->gte($today))
                ->sortBy(fn ($i) => $i->planDate->timestamp)->first();

            return (object) [
                'client' => $client,
                'schedule' => $schedule,
                'isCarried' => $isCarried,
                'types' => $types,
                'planned' => $planned,
                'created' => $types->sum('created'),
                'posted' => $types->sum('posted'),
                'ready' => $types->sum('ready'),
                'inProduction' => $types->sum('inProduction'),
                'missed' => $missed,
                'toCreate' => $toCreate,
                'extra' => $types->sum('extra'),
                'pct' => $planned > 0 ? min(100, (int) round($types->sum('posted') / $planned * 100)) : 0,
                'status' => $status,
                'next' => $next,
            ];
        });

        // Clients that need action first, then by size of plan
        $statusOrder = array_flip(['behind', 'needs_tasks', 'on_track', 'no_plan', 'complete']);
        $rows = $rows->sortBy(fn ($r) => [$statusOrder[$r->status], -$r->planned, $r->client->name])->values();

        $planned = $rows->sum('planned');

        return [
            'rows' => $rows,
            'monthStart' => $monthStart,
            'isPastMonth' => $isPastMonth,
            'isFutureMonth' => $isFutureMonth,
            'summary' => [
                'clients' => $rows->count(),
                'withPlan' => $rows->filter(fn ($r) => $r->schedule && !$r->isCarried)->count(),
                'carried' => $rows->where('isCarried', true)->count(),
                'planned' => $planned,
                'created' => $rows->sum('created'),
                'posted' => $rows->sum('posted'),
                'toCreate' => $rows->sum('toCreate'),
                'missed' => $rows->sum('missed'),
                'pct' => $planned > 0 ? min(100, (int) round($rows->sum('posted') / $planned * 100)) : 0,
                'statusCounts' => $rows->countBy('status')->all(),
            ],
        ];
    }
}
