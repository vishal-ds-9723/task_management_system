<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MobileTeamController extends Controller
{
    /**
     * Get team directory with active workloads and presence.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->isClient()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $today = Carbon::today();
        $query = User::where('role', '!=', 'client');

        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }

        if ($request->has('search') && !empty($request->search)) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        $members = $query->withCount([
            'assignedTasks as active_tasks_count' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES),
            'assignedTasks as completed_today_count' => fn($q) => $q->whereIn('status', ['completed', 'published'])->whereDate('updated_at', $today),
            'assignedTasks as overdue_tasks_count' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES)->whereNotNull('deadline')->where('deadline', '<', $today),
            'assignedTasks as total_tasks_count',
        ])
        ->orderBy('name', 'asc')
        ->get()
        ->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'email' => $m->email,
                'role' => $m->role,
                'avatar_color' => $m->avatar_color,
                'can_manage_social_metrics' => (bool)$m->can_manage_social_metrics,
                'is_online' => $m->isOnline(),
                'last_active_at' => $m->last_active_at ? $m->last_active_at->diffForHumans() : 'Never',
                'active_tasks_count' => (int)$m->active_tasks_count,
                'completed_today_count' => (int)$m->completed_today_count,
                'overdue_tasks_count' => (int)$m->overdue_tasks_count,
                'total_tasks_count' => (int)$m->total_tasks_count,
            ];
        });

        return response()->json([
            'success' => true,
            'members' => $members,
            'roles' => User::ALLOWED_ROLES,
        ]);
    }

    /**
     * Create team member.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:' . implode(',', User::ALLOWED_ROLES),
            'avatar_color' => 'nullable|string',
            'can_manage_social_metrics' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $member = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'avatar_color' => $request->avatar_color ?: '#6366F1',
            'can_manage_social_metrics' => $request->boolean('can_manage_social_metrics', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team member created successfully',
            'member' => $member,
        ], 201);
    }

    /**
     * Get single member details & assigned tasks.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if ($user->isClient()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $member = User::where('role', '!=', 'client')->findOrFail($id);

        $activeTasks = Task::with('client')
            ->where('assigned_to', $member->id)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->latest()
            ->get();

        $completedTasks = Task::with('client')
            ->where('assigned_to', $member->id)
            ->whereIn('status', ['completed', 'published'])
            ->latest('completed_at')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $member->role,
                'avatar_color' => $member->avatar_color,
                'can_manage_social_metrics' => (bool)$member->can_manage_social_metrics,
                'is_online' => $member->isOnline(),
                'last_active_at' => $member->last_active_at ? $member->last_active_at->diffForHumans() : 'Never',
                'created_at' => $member->created_at->format('M d, Y'),
            ],
            'active_tasks' => $activeTasks,
            'completed_tasks' => $completedTasks,
        ]);
    }

    /**
     * Update team member.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $member = User::where('role', '!=', 'client')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $member->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|in:' . implode(',', User::ALLOWED_ROLES),
            'avatar_color' => 'nullable|string',
            'can_manage_social_metrics' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'avatar_color' => $request->avatar_color ?: $member->avatar_color,
            'can_manage_social_metrics' => $request->boolean('can_manage_social_metrics', $member->can_manage_social_metrics),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $member->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Team member updated successfully',
            'member' => $member,
        ]);
    }

    /**
     * Delete team member.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        if ($user->id == $id) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own admin account'], 400);
        }

        $member = User::where('role', '!=', 'client')->findOrFail($id);

        // Unassign active tasks
        Task::where('assigned_to', $member->id)->whereIn('status', Task::ACTIVE_STATUSES)->update(['assigned_to' => null]);

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team member deleted successfully',
        ]);
    }

    /**
     * Get workload distribution matrix.
     */
    public function workload(Request $request)
    {
        $user = $request->user();
        if ($user->isClient()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $today = Carbon::today();

        $workload = User::whereIn('role', ['designer', 'developer', 'strategist', 'editor', 'content_writer'])
            ->withCount([
                'assignedTasks as total_active' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES),
                'assignedTasks as in_progress' => fn($q) => $q->where('status', 'inprogress'),
                'assignedTasks as in_review' => fn($q) => $q->where('status', 'review'),
                'assignedTasks as todo' => fn($q) => $q->where('status', 'todo'),
                'assignedTasks as overdue' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES)->whereNotNull('deadline')->where('deadline', '<', $today),
            ])
            ->get()
            ->map(function ($m) {
                $maxCapacity = 6; // Standard daily target
                $loadPercent = min(100, round(($m->total_active / $maxCapacity) * 100));
                $isOverloaded = $m->total_active >= 6;

                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'role' => $m->role,
                    'avatar_color' => $m->avatar_color,
                    'is_online' => $m->isOnline(),
                    'total_active' => (int)$m->total_active,
                    'in_progress' => (int)$m->in_progress,
                    'in_review' => (int)$m->in_review,
                    'todo' => (int)$m->todo,
                    'overdue' => (int)$m->overdue,
                    'capacity_percent' => $loadPercent,
                    'is_overloaded' => $isOverloaded,
                ];
            });

        return response()->json([
            'success' => true,
            'workload' => $workload,
        ]);
    }

    /**
     * Get realtime active users monitor.
     */
    public function activeUsers(Request $request)
    {
        $user = $request->user();
        if ($user->isClient()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $activeUsers = User::whereNotNull('last_active_at')
            ->orderBy('last_active_at', 'desc')
            ->take(30)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                    'avatar_color' => $u->avatar_color,
                    'is_online' => $u->isOnline(),
                    'current_url' => $u->current_url ?: 'In app',
                    'last_active_at' => $u->last_active_at ? $u->last_active_at->diffForHumans() : 'Never',
                ];
            });

        return response()->json([
            'success' => true,
            'active_users' => $activeUsers,
        ]);
    }
}
