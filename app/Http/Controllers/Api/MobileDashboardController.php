<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActionItem;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\FestivalSelection;
use App\Models\ShootDay;
use App\Models\SocialMediaPost;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MobileDashboardController extends Controller
{
    /**
     * Get role-tailored dashboard metrics and feeds.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;

        switch ($role) {
            case 'admin':
                return $this->adminDashboard($user);
            case 'strategist':
            case 'manager':
            case 'editor':
            case 'content_writer':
                return $this->strategistDashboard($user);
            case 'designer':
                return $this->designerDashboard($user);
            case 'developer':
                return $this->developerDashboard($user);
            case 'client':
                return $this->clientDashboard($user);
            default:
                return $this->genericDashboard($user);
        }
    }

    protected function adminDashboard(User $user)
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        // 1. Core Task Counts
        $totalTasks = Task::count();
        $inProgress = Task::where('status', 'inprogress')->count();
        $inReview = Task::where('status', 'review')->count();
        $pendingApproval = Task::where('status', 'pending_approval')->count();
        $completed = Task::whereIn('status', ['completed', 'published'])->count();
        $urgentCount = Task::where('is_urgent_task', true)->whereIn('status', Task::ACTIVE_STATUSES)->count();
        $clientsCount = Client::where('is_active', true)->count();
        $teamCount = User::where('role', '!=', 'client')->count();

        $completedToday = Task::whereIn('status', ['completed', 'published'])
            ->whereDate('updated_at', $today)
            ->count();

        $completedThisWeek = Task::whereIn('status', ['completed', 'published'])
            ->whereBetween('updated_at', [$weekStart, $weekEnd->copy()->endOfDay()])
            ->count();

        $createdToday = Task::whereDate('created_at', $today)->count();

        $overdueCount = Task::whereIn('status', Task::ACTIVE_STATUSES)
            ->whereNotNull('deadline')
            ->where('deadline', '<', $today)
            ->count();

        $unassignedCount = Task::whereIn('status', ['todo', 'inprogress'])
            ->whereNull('assigned_to')
            ->count();

        $awaitingPublishing = Task::where('is_urgent_task', false)
            ->where('status', 'completed')
            ->whereDoesntHave('socialMediaPosts')
            ->count();

        // 2. Weekly Trend (last 7 days)
        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayDate = $today->copy()->subDays($i);
            $weeklyTrend[] = [
                'day' => $dayDate->format('D'),
                'date' => $dayDate->format('M d'),
                'created' => Task::whereDate('created_at', $dayDate)->count(),
                'completed' => Task::whereIn('status', ['completed', 'published'])->whereDate('updated_at', $dayDate)->count(),
            ];
        }

        // 3. Urgent & Overdue Task Feeds
        $urgentTasks = Task::with(['client', 'assignee'])
            ->where('is_urgent_task', true)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->latest()
            ->take(6)
            ->get();

        $overdueTasks = Task::with(['client', 'assignee'])
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->whereNotNull('deadline')
            ->where('deadline', '<', $today)
            ->orderBy('deadline', 'asc')
            ->take(6)
            ->get();

        $recentPendingApprovals = Task::with(['client', 'assignee'])
            ->whereIn('status', ['review', 'pending_approval'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        // 4. Team Workload Roster
        $teamWorkload = User::where('role', '!=', 'client')
            ->withCount([
                'assignedTasks as active_tasks_count' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES),
                'assignedTasks as completed_today_count' => fn($q) => $q->whereIn('status', ['completed', 'published'])->whereDate('updated_at', $today),
                'assignedTasks as overdue_tasks_count' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES)->whereNotNull('deadline')->where('deadline', '<', $today),
            ])
            ->orderBy('active_tasks_count', 'desc')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->role,
                    'email' => $member->email,
                    'avatar_color' => $member->avatar_color,
                    'is_online' => $member->isOnline(),
                    'active_tasks_count' => (int)$member->active_tasks_count,
                    'completed_today_count' => (int)$member->completed_today_count,
                    'overdue_tasks_count' => (int)$member->overdue_tasks_count,
                ];
            });

        // 5. Clients Roster
        $clientsRoster = Client::where('is_active', true)
            ->withCount([
                'tasks as active_tasks_count' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES),
                'tasks as completed_tasks_count' => fn($q) => $q->whereIn('status', ['completed', 'published']),
            ])
            ->orderBy('active_tasks_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'category' => $client->category,
                    'emoji' => $client->emoji,
                    'color' => $client->color,
                    'logo' => $client->logo,
                    'active_tasks_count' => (int)$client->active_tasks_count,
                    'completed_tasks_count' => (int)$client->completed_tasks_count,
                ];
            });

        // 6. Recent Studio Activity Stream
        $recentActivity = AuditLog::with(['user', 'task'])
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'user_name' => $log->user ? $log->user->name : 'System',
                    'user_avatar_color' => $log->user ? $log->user->avatar_color : '#6366F1',
                    'task_title' => $log->task ? $log->task->title : null,
                    'task_id' => $log->task_id,
                    'created_at' => $log->created_at ? $log->created_at->diffForHumans() : 'just now',
                ];
            });

        return response()->json([
            'success' => true,
            'role' => 'admin',
            'stats' => [
                'total_tasks' => $totalTasks,
                'in_progress' => $inProgress,
                'in_review' => $inReview,
                'pending_approval' => $pendingApproval,
                'completed' => $completed,
                'completed_today' => $completedToday,
                'completed_this_week' => $completedThisWeek,
                'created_today' => $createdToday,
                'overdue_tasks' => $overdueCount,
                'unassigned_tasks' => $unassignedCount,
                'urgent_tasks' => $urgentCount,
                'clients_count' => $clientsCount,
                'team_count' => $teamCount,
                'awaiting_publishing' => $awaitingPublishing,
            ],
            'weekly_trend' => $weeklyTrend,
            'urgent_tasks' => $urgentTasks,
            'overdue_tasks' => $overdueTasks,
            'pending_approvals' => $recentPendingApprovals,
            'team_workload' => $teamWorkload,
            'clients_roster' => $clientsRoster,
            'recent_activity' => $recentActivity,
        ]);
    }

    protected function strategistDashboard(User $user)
    {
        $myActiveTasks = Task::whereIn('status', Task::ACTIVE_STATUSES)->count();
        $inReview = Task::where('status', 'review')->count();
        $dueToday = Task::whereDate('deadline', Carbon::today())->whereIn('status', Task::ACTIVE_STATUSES)->count();
        $shootDaysUpcoming = ShootDay::whereDate('shoot_date', '>=', Carbon::today())->count();
        $actionItemsCount = ActionItem::where('status', '!=', 'done')->count();

        $pendingApprovals = Task::with(['client', 'assignee', 'creator'])
            ->where('status', 'review')
            ->latest()
            ->take(5)
            ->get();

        $urgentTasks = Task::with(['client', 'assignee', 'creator'])
            ->where('is_urgent_task', true)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'role' => 'strategist',
            'stats' => [
                'active_tasks' => $myActiveTasks,
                'in_review' => $inReview,
                'due_today' => $dueToday,
                'shoot_days' => $shootDaysUpcoming,
                'action_items' => $actionItemsCount,
            ],
            'pending_reviews' => $pendingApprovals,
            'urgent_tasks' => $urgentTasks,
        ]);
    }

    protected function designerDashboard(User $user)
    {
        $assignedTasks = Task::where('assigned_to', $user->id);
        $totalAssigned = (clone $assignedTasks)->count();
        $inProgress = (clone $assignedTasks)->where('status', 'inprogress')->count();
        $inReview = (clone $assignedTasks)->where('status', 'review')->count();
        $completed = (clone $assignedTasks)->where('status', 'completed')->count();
        $urgentAssigned = (clone $assignedTasks)->where('is_urgent_task', true)->whereIn('status', Task::ACTIVE_STATUSES)->count();

        $activeTask = Task::with(['client'])
            ->where('assigned_to', $user->id)
            ->where('status', 'inprogress')
            ->first();

        $todayTasks = Task::with(['client'])
            ->where('assigned_to', $user->id)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->orderBy('priority', 'desc')
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'role' => 'designer',
            'stats' => [
                'total_assigned' => $totalAssigned,
                'in_progress' => $inProgress,
                'in_review' => $inReview,
                'completed' => $completed,
                'urgent_tasks' => $urgentAssigned,
            ],
            'active_task' => $activeTask,
            'today_tasks' => $todayTasks,
        ]);
    }

    protected function developerDashboard(User $user)
    {
        $assignedTasks = Task::where('assigned_to', $user->id);
        $inProgress = (clone $assignedTasks)->where('status', 'inprogress')->count();
        $todo = (clone $assignedTasks)->where('status', 'todo')->count();
        $completed = (clone $assignedTasks)->where('status', 'completed')->count();

        $activeProjects = Task::with(['client'])
            ->where('assigned_to', $user->id)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->latest()
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'role' => 'developer',
            'stats' => [
                'todo' => $todo,
                'in_progress' => $inProgress,
                'completed' => $completed,
            ],
            'active_projects' => $activeProjects,
        ]);
    }

    protected function clientDashboard(User $user)
    {
        $clientId = $user->client_id;
        $client = $user->client;

        $totalTasks = Task::where('client_id', $clientId)->count();
        $publishedCount = Task::where('client_id', $clientId)->where('status', 'published')->count();
        $inReviewCount = Task::where('client_id', $clientId)->whereIn('status', ['review', 'pending_approval'])->count();
        $festivalsSelected = FestivalSelection::where('client_id', $clientId)->count();

        $recentPublished = SocialMediaPost::whereHas('task', fn ($q) => $q->where('client_id', $clientId))
            ->latest('posted_at')
            ->take(6)
            ->get();

        $upcomingSchedules = Task::where('client_id', $clientId)
            ->whereDate('post_date', '>=', Carbon::today())
            ->orderBy('post_date')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'role' => 'client',
            'client' => $client ? [
                'id' => $client->id,
                'name' => $client->name,
                'logo' => $client->logo,
                'category' => $client->category,
            ] : null,
            'stats' => [
                'total_content' => $totalTasks,
                'published' => $publishedCount,
                'in_review' => $inReviewCount,
                'festivals_opted' => $festivalsSelected,
            ],
            'recent_published' => $recentPublished,
            'upcoming_schedules' => $upcomingSchedules,
        ]);
    }

    protected function genericDashboard(User $user)
    {
        return response()->json([
            'success' => true,
            'role' => $user->role,
            'stats' => [
                'assigned' => Task::where('assigned_to', $user->id)->count(),
            ],
        ]);
    }
}
