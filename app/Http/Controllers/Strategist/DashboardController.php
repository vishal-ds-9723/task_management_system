<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\SocialMediaPost;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ShootDay;   
use App\Services\Strategist\DashboardService;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->getDashboardData(request());

        return view('strategist.dashboard', $data);
    }

    public function pendingAssignments()
    {
        $today = Carbon::today();

        $unassignedTasks = Task::with(['client', 'assignee', 'creator'])
            ->where(function ($q) {
                $q->whereNull('assigned_to')
                  ->orWhere('status', 'unassigned');
            })
            ->orderBy('deadline')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('strategist.pending-assignments', compact('unassignedTasks'));
    }

    public function allShootDays(Request $request)
    {
        $today = Carbon::today();

        $query = ShootDay::with(['client', 'creator']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $shootDays = $query->orderBy('shoot_date', 'desc')->paginate(25)->withQueryString();

        $shootDaysInProgress = ShootDay::where('status', 'in_progress')->count();
        $shootDaysOverdue = ShootDay::where('status', 'scheduled')->where('shoot_date', '<', $today)->count();
        $shootDaysCompleted = ShootDay::where('status', 'completed')->count();
        $shootDaysScheduled = ShootDay::where('status', 'scheduled')->count();

        $clients = Client::orderBy('name')->get();

        return view('strategist.all-shoot-days', compact(
            'shootDays',
            'shootDaysInProgress',
            'shootDaysOverdue',
            'shootDaysCompleted',
            'shootDaysScheduled',
            'clients'
        ));
    }

    public function priorityHeatmap()
    {
        $base = Task::query()->whereHas('creator', fn($q) => $q->whereIn('role', ['strategist', 'admin']));

        $urgentTasks = (clone $base)->where('status', '!=', 'completed')->where('priority', 'urgent')->count();
        $highTasks = (clone $base)->where('status', '!=', 'completed')->where('priority', 'high')->count();
        $normalTasks = (clone $base)->where('status', '!=', 'completed')->where('priority', 'normal')->count();

        $urgentTasksList = (clone $base)
            ->with(['client', 'assignee', 'creator'])
            ->where('status', '!=', 'completed')
            ->where('priority', 'urgent')
            ->orderByRaw("FIELD(priority, 'urgent')")
            ->oldest()
            ->get();

        $highTasksList = (clone $base)
            ->with(['client', 'assignee', 'creator'])
            ->where('status', '!=', 'completed')
            ->where('priority', 'high')
            ->oldest()
            ->get();

        $normalTasksList = (clone $base)
            ->with(['client', 'assignee', 'creator'])
            ->where('status', '!=', 'completed')
            ->where('priority', 'normal')
            ->oldest()
            ->get();

        return view('strategist.priority-heatmap', compact(
            'urgentTasks', 'highTasks', 'normalTasks',
            'urgentTasksList', 'highTasksList', 'normalTasksList'
        ));
    }

    private function effectiveTaskDate(Task $task, Carbon $today): array
    {
        $deadline = $task->deadline ? Carbon::parse($task->deadline)->startOfDay() : null;
        $postDate = $task->post_date ? Carbon::parse($task->post_date)->startOfDay() : null;

        if ($deadline && $postDate) {
            $futureDates = collect([
                ['date' => $deadline, 'type' => 'deadline'],
                ['date' => $postDate, 'type' => 'post_date'],
            ])->filter(fn ($entry) => $entry['date']->gte($today));

            $pick = $futureDates->sortBy('date')->first();
            if ($pick) {
                return [$pick['date']->toDateString(), $pick['type']];
            }

            return [$deadline->lte($postDate) ? $deadline->toDateString() : $postDate->toDateString(), $deadline->lte($postDate) ? 'deadline' : 'post_date'];
        }

        if ($deadline) {
            return [$deadline->toDateString(), 'deadline'];
        }

        if ($postDate) {
            return [$postDate->toDateString(), 'post_date'];
        }

        return [null, null];
    }
}

