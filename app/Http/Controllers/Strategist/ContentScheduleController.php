<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientMonthlySchedule;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContentScheduleController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $clients = Client::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Content type mapping: task type => schedule key
        $typeMap = [
            'post' => 'posts', 'reel' => 'reels', 'story' => 'stories',
            'carousel' => 'carousel', 'video' => 'videos',
            'guide' => 'guides', 'collection' => 'collections',
        ];

        $contentTypes = [
            'posts' => ['icon' => 'fa-pen-to-square', 'label' => 'Posts', 'color' => '#3B82F6'],
            'reels' => ['icon' => 'fa-film', 'label' => 'Reels', 'color' => '#F97316'],
            'stories' => ['icon' => 'fa-book-open', 'label' => 'Stories', 'color' => '#10B981'],
            'carousel' => ['icon' => 'fa-images', 'label' => 'Carousels', 'color' => '#EC4899'],
            'videos' => ['icon' => 'fa-video', 'label' => 'Videos', 'color' => '#8B5CF6'],
            'guides' => ['icon' => 'fa-compass', 'label' => 'Guides', 'color' => '#06B6D4'],
            'collections' => ['icon' => 'fa-folder-open', 'label' => 'Collections', 'color' => '#F59E0B'],
            'other' => ['icon' => 'fa-ellipsis', 'label' => 'Other', 'color' => '#6B7280'],
        ];

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Get task counts by client, type, status for this month
        $taskData = Task::where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('deadline', [$monthStart, $monthEnd])
                  ->orWhereBetween('post_date', [$monthStart, $monthEnd])
                  ->orWhereBetween('created_at', [$monthStart, $monthEnd]);
            })
            ->selectRaw('client_id, type, status, COUNT(*) as cnt')
            ->groupBy('client_id', 'type', 'status')
            ->get();

        $mappedTypes = array_keys($typeMap);

        $scheduleData = $clients->map(function (Client $client) use ($month, $year, $typeMap, $mappedTypes, $taskData) {
            $schedule = ClientMonthlySchedule::getEffectiveSchedule($client->id, $month, $year);
            $isCarried = $schedule && !($schedule->month === $month && $schedule->year === $year);

            $clientTasks = $taskData->where('client_id', $client->id);

            $progress = [];
            foreach ($typeMap as $taskType => $scheduleKey) {
                $done = $clientTasks->where('type', $taskType)->whereIn('status', ['completed', 'published'])->sum('cnt');
                $active = $clientTasks->where('type', $taskType)->whereNotIn('status', ['completed', 'published'])->sum('cnt');
                $progress[$scheduleKey] = ['done' => (int) $done, 'active' => (int) $active];
            }
            $otherDone = $clientTasks->whereNotIn('type', $mappedTypes)->whereIn('status', ['completed', 'published'])->sum('cnt');
            $otherActive = $clientTasks->whereNotIn('type', $mappedTypes)->whereNotIn('status', ['completed', 'published'])->sum('cnt');
            $progress['other'] = ['done' => (int) $otherDone, 'active' => (int) $otherActive];

            $totalPlanned = $schedule ? $schedule->getTotalContent() : 0;
            $totalDone = collect($progress)->sum('done');
            $totalActive = collect($progress)->sum('active');
            $pct = $totalPlanned > 0 ? min(100, round(($totalDone / $totalPlanned) * 100)) : 0;

            return (object) [
                'client' => $client,
                'schedule' => $schedule,
                'isCarried' => $isCarried,
                'progress' => $progress,
                'totalPlanned' => $totalPlanned,
                'totalDone' => $totalDone,
                'totalActive' => $totalActive,
                'pct' => $pct,
            ];
        })->sortByDesc('totalPlanned')->values();

        // Stats
        $totalClients = $scheduleData->count();
        $clientsWithSchedule = $scheduleData->filter(fn ($d) => $d->schedule !== null)->count();
        $overallPlanned = $scheduleData->sum('totalPlanned');
        $overallDone = $scheduleData->sum('totalDone');
        $overallActive = $scheduleData->sum('totalActive');
        $overallPct = $overallPlanned > 0 ? min(100, round(($overallDone / $overallPlanned) * 100)) : 0;

        $monthLabel = Carbon::create($year, $month, 1)->format('F Y');

        return view('strategist.content-schedules', compact(
            'scheduleData', 'contentTypes', 'month', 'year', 'monthLabel',
            'totalClients', 'clientsWithSchedule',
            'overallPlanned', 'overallDone', 'overallActive', 'overallPct'
        ));
    }
}
