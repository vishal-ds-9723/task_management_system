<?php

namespace App\Services\Client;

use App\Models\Task;
use App\Models\FestivalSelection;
use App\Models\Festival;
use App\Models\User;
use App\Models\SocialMediaPostMetric;

use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function getIndexData()
    {
        $user = auth()->user();
        $clientId = $user->client_id;

        // Single conditional aggregation replaces 4 count() calls
        $taskStats = Task::where('client_id', $clientId)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status NOT IN ('completed', 'published') THEN 1 ELSE 0 END) as in_progress
        ")->first();
        $publishedCount  = (int) ($taskStats->published ?? 0);
        $inProgressCount = (int) ($taskStats->in_progress ?? 0);
        $completedCount  = (int) ($taskStats->completed ?? 0);
        $totalTasks      = (int) ($taskStats->total ?? 0);

        $recentPublished = Task::with(['media', 'socialMediaPosts', 'assignee'])
            ->where('client_id', $clientId)
            ->where('status', 'published')
            ->latest('completed_at')
            ->take(6)
            ->get();

        $upcomingFestivals = Festival::active()
            ->upcoming()
            ->take(5)
            ->get();

        $selectedFestivalsCount = FestivalSelection::where('client_id', $clientId)->count();
        $client = $user->client;

        // ── Monthly Content Schedule with carry-forward ──
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $monthlySchedule = \App\Models\ClientMonthlySchedule::getEffectiveSchedule($clientId, $currentMonth, $currentYear);
        $isCarriedForward = $monthlySchedule && !($monthlySchedule->month === $currentMonth && $monthlySchedule->year === $currentYear);

        // Task progress by type for current month
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $monthTasks = Task::where('client_id', $clientId)
            ->where(function($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('deadline', [$monthStart, $monthEnd])
                  ->orWhereBetween('post_date', [$monthStart, $monthEnd])
                  ->orWhereBetween('created_at', [$monthStart, $monthEnd]);
            })
            ->selectRaw("type, status, COUNT(*) as cnt")
            ->groupBy('type', 'status')
            ->get();

        $typeMap = [
            'post' => 'posts', 'reel' => 'reels', 'story' => 'stories',
            'carousel' => 'carousel', 'video' => 'videos',
            'guide' => 'guides', 'collection' => 'collections',
        ];
        $scheduleProgress = [];
        foreach ($typeMap as $taskType => $scheduleKey) {
            $done = $monthTasks->where('type', $taskType)->whereIn('status', ['completed', 'published'])->sum('cnt');
            $active = $monthTasks->where('type', $taskType)->whereNotIn('status', ['completed', 'published'])->sum('cnt');
            $scheduleProgress[$scheduleKey] = ['done' => (int) $done, 'active' => (int) $active];
        }
        $mappedTypes = array_keys($typeMap);
        $otherDone = $monthTasks->whereNotIn('type', $mappedTypes)->whereIn('status', ['completed', 'published'])->sum('cnt');
        $otherActive = $monthTasks->whereNotIn('type', $mappedTypes)->whereNotIn('status', ['completed', 'published'])->sum('cnt');
        $scheduleProgress['other'] = ['done' => (int) $otherDone, 'active' => (int) $otherActive];

        // ── Social Media Analysis ──
        $metrics = SocialMediaPostMetric::whereHas('socialMediaPost.task', function($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->with(['socialMediaPost.socialMediaLink', 'socialMediaPost.task.socialMediaLink'])->orderBy('snapshot_date')->get();

        $totalAdSpend = $metrics->sum('ad_spend_inr');
        $organicCount = $metrics->where('paid_promotion', false)->count();
        $inorganicCount = $metrics->where('paid_promotion', true)->count();

        $locations = $metrics->whereNotNull('target_area')
            ->groupBy('target_area')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5);

        // Raw metrics for JS-side filtering
        $rawMetrics = $metrics->map(function($m) {
            $post = $m->socialMediaPost;
            $accountLabel = $post->socialMediaLink?->label
                ?? $post->task->socialMediaLink?->label
                ?? 'General';

            return [
                'date' => $m->snapshot_date->format('Y-m-d'),
                'display_date' => $m->snapshot_date->format('M d'),
                'views' => (int) $m->views,
                'reach' => (int) $m->reach,
                'likes' => (int) $m->likes,
                'impressions' => (int) $m->impressions,
                'ad_spend_inr' => (float) ($m->ad_spend_inr ?? 0),
                'target_area' => $m->target_area,
                'platform' => $post->platform,
                'post_type' => $post->post_type,
                'post_title' => $post->task->title ?: 'Untitled Post',
                'paid' => (bool) $m->paid_promotion,
                'account' => $accountLabel,
            ];
        });

        // Get linked platforms (New Table)
        $links = \App\Models\ClientSocialMediaLink::where('client_id', $clientId)->get();
        $availablePlatforms = $links->pluck('platform')->unique()->values();
        $availableAccounts = $links->map(fn($l) => ['platform' => $l->platform, 'label' => $l->label ?: 'Main Account']);

        // Merge legacy fields from client table
        $legacyMap = [
            'instagram' => $client->instagram,
            'facebook' => $client->facebook,
            'twitter' => $client->twitter,
            'linkedin' => $client->linkedin,
            'youtube' => $client->youtube,
            'tiktok' => $client->tiktok,
            'whatsapp' => $client->whatsapp ?? null,
        ];

        foreach ($legacyMap as $plat => $val) {
            if (!empty($val)) {
                if (!$availablePlatforms->contains($plat)) {
                    $availablePlatforms->push($plat);
                }
                // Check if already in accounts
                if (!$availableAccounts->contains(fn($acc) => $acc['platform'] === $plat)) {
                    $availableAccounts->push(['platform' => $plat, 'label' => 'Legacy Link']);
                }
            }
        }

        $availablePlatforms = $availablePlatforms->unique()->values();
        $availableAccounts = $availableAccounts->unique()->values();

        $availableTypes = $rawMetrics->pluck('post_type')->unique()->values();
        $availableTitles = $rawMetrics->pluck('post_title')->unique()->values();

        // Initial timeline data (aggregated)
        $timelineData = $metrics->groupBy(function($m) {
            return $m->snapshot_date->format('Y-m-d');
        })->map(function($group) {
            return [
                'date' => $group->first()->snapshot_date->format('M d'),
                'views' => $group->sum('views'),
                'reach' => $group->sum('reach'),
                'likes' => $group->sum('likes'),
                'impressions' => $group->sum('impressions'),
            ];
        })->values();

        return compact(
            'publishedCount', 'inProgressCount', 'completedCount', 'totalTasks',
            'recentPublished', 'upcomingFestivals', 'selectedFestivalsCount', 'client',
            'monthlySchedule', 'isCarriedForward', 'scheduleProgress', 'currentMonth', 'currentYear',
            'totalAdSpend', 'organicCount', 'inorganicCount', 'locations', 'timelineData',
            'rawMetrics', 'availablePlatforms', 'availableAccounts', 'availableTypes', 'availableTitles'
        );

    }

    public function getStatDetails($type)
    {
        $user = auth()->user();
        $clientId = $user->client_id;

        $data = [];
        $title = '';

        switch ($type) {
            case 'published':
                $title = 'Published Content';
                $data = Task::where('client_id', $clientId)
                    ->where('status', 'published')
                    ->latest('completed_at')
                    ->get()
                    ->map(fn($t) => [
                        'title' => $t->title ?: 'Untitled Post',
                        'type' => ucfirst($t->type),
                        'date' => $t->completed_at ? $t->completed_at->format('M d, Y') : 'N/A',
                        'meta' => is_array($t->platform) ? implode(', ', $t->platform) : '',
                        'url' => route('client.tasks.show', $t->id)
                    ]);
                break;
            case 'in_progress':
                $title = 'In Progress Content';
                $data = Task::where('client_id', $clientId)
                    ->whereNotIn('status', ['completed', 'published'])
                    ->latest('updated_at')
                    ->get()
                    ->map(fn($t) => [
                        'title' => $t->title ?: 'Untitled Post',
                        'type' => ucfirst($t->type),
                        'date' => $t->deadline ? $t->deadline->format('M d, Y') : 'N/A',
                        'meta' => ucfirst($t->status),
                        'url' => route('client.tasks.show', $t->id)
                    ]);
                break;
            case 'completed':
                $title = 'Completed Content';
                $data = Task::where('client_id', $clientId)
                    ->where('status', 'completed')
                    ->latest('completed_at')
                    ->get()
                    ->map(fn($t) => [
                        'title' => $t->title ?: 'Untitled Post',
                        'type' => ucfirst($t->type),
                        'date' => $t->completed_at ? $t->completed_at->format('M d, Y') : 'N/A',
                        'meta' => 'Awaiting Review',
                        'url' => route('client.tasks.show', $t->id)
                    ]);
                break;
            case 'festivals':
                $title = 'Selected Festivals';
                $data = FestivalSelection::with('festival')
                    ->where('client_id', $clientId)
                    ->get()
                    ->map(fn($s) => [
                        'title' => $s->festival->name,
                        'type' => 'Festival',
                        'date' => $s->festival->date->format('M d, Y'),
                        'meta' => is_array($s->platforms) ? implode(', ', $s->platforms) : 'No platform selected',
                        'url' => route('client.festivals', ['month' => $s->festival->date->format('Y-m')])
                    ]);
                break;
        }

        return compact('title', 'data');
    }

    public function getTaskData(Task $task)
    {
        if ($task->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $task->load(['media', 'socialMediaPosts', 'assignee', 'creator', 'comments.user']);
        $client = auth()->user()->client;

        return compact('task', 'client');
    }

    public function getContentScheduleData()
    {
        $user = auth()->user();
        $clientId = $user->client_id;
        $client = $user->client;

        $currentMonth = now()->month;
        $currentYear = now()->year;

        $monthlySchedule = \App\Models\ClientMonthlySchedule::getEffectiveSchedule($clientId, $currentMonth, $currentYear);
        $isCarriedForward = $monthlySchedule && !($monthlySchedule->month === $currentMonth && $monthlySchedule->year === $currentYear);

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // Get all tasks for this month with details
        $monthTasks = Task::with(['assignee'])
            ->where('client_id', $clientId)
            ->where(function($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('deadline', [$monthStart, $monthEnd])
                  ->orWhereBetween('post_date', [$monthStart, $monthEnd])
                  ->orWhereBetween('created_at', [$monthStart, $monthEnd]);
            })
            ->latest('created_at')
            ->get();

        // Task counts by type and status
        $typeMap = [
            'post' => 'posts', 'reel' => 'reels', 'story' => 'stories',
            'carousel' => 'carousel', 'video' => 'videos',
            'guide' => 'guides', 'collection' => 'collections',
        ];

        $scheduleProgress = [];
        foreach ($typeMap as $taskType => $scheduleKey) {
            $typeTasks = $monthTasks->where('type', $taskType);
            $done = $typeTasks->whereIn('status', ['completed', 'published'])->count();
            $active = $typeTasks->whereNotIn('status', ['completed', 'published'])->count();
            $scheduleProgress[$scheduleKey] = ['done' => $done, 'active' => $active];
        }
        $mappedTypes = array_keys($typeMap);
        $otherDone = $monthTasks->whereNotIn('type', $mappedTypes)->whereIn('status', ['completed', 'published'])->count();
        $otherActive = $monthTasks->whereNotIn('type', $mappedTypes)->whereNotIn('status', ['completed', 'published'])->count();
        $scheduleProgress['other'] = ['done' => $otherDone, 'active' => $otherActive];

        // Group tasks by type for the task list
        $tasksByType = [];
        foreach ($monthTasks as $task) {
            $key = $typeMap[$task->type] ?? 'other';
            $tasksByType[$key][] = $task;
        }

        return compact(
            'client', 'monthlySchedule', 'isCarriedForward', 'scheduleProgress',
            'currentMonth', 'currentYear', 'monthTasks', 'tasksByType'
        );
    }
}
