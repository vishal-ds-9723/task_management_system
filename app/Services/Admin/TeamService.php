<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeamService
{
    private function normalizeRole(string $role): string
    {
        $normalized = strtolower(trim($role));
        $normalized = preg_replace('/\s+/', '_', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^a-z0-9_]/', '', $normalized) ?? $normalized;

        return $normalized !== '' ? substr($normalized, 0, 50) : 'designer';
    }

    public function getIndexData(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $relations = ['client'];
        if (\Illuminate\Support\Facades\Schema::hasTable('client_user')) {
            $relations[] = 'clients';
        }

        $members = $query->with($relations)->withCount([
            'assignedTasks',
            'assignedTasks as active_tasks_count' => fn ($q) => $q->whereIn('status', ['todo', 'inprogress', 'review']),
            'assignedTasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
        ])->orderBy('name')->get();

        $roles = User::select('role')->distinct()->orderBy('role')->pluck('role');

        $stats = [
            'total'   => User::count(),
            'byRole'  => User::selectRaw('role, COUNT(*) as count')->groupBy('role')->pluck('count', 'role'),
        ];

        return compact('members', 'roles', 'stats');
    }

    public function createMember(array $data)
    {
        if (empty($data['avatar_color'])) {
            $colors = ['#4F6DF0', '#10B981', '#F97316', '#8B5CF6', '#EC4899', '#EF4444', '#14B8A6', '#6366F1'];
            $data['avatar_color'] = $colors[array_rand($colors)];
        }

        $data['can_manage_social_metrics'] = (bool) ($data['can_manage_social_metrics'] ?? false);
        $data['role'] = $this->normalizeRole((string) ($data['role'] ?? 'designer'));
        $clientIds = isset($data['client_ids']) ? array_filter((array) $data['client_ids']) : (!empty($data['client_id']) ? [$data['client_id']] : []);
        $data['client_id'] = !empty($clientIds) ? reset($clientIds) : null;
        $additional = array_map([$this, 'normalizeRole'], (array) ($data['additional_roles'] ?? []));
        $data['additional_roles'] = array_values(array_unique(array_filter($additional, fn($r) => !empty($r) && $r !== $data['role'])));

        $user = User::create($data);

        $hasClientUserTable = \Illuminate\Support\Facades\Schema::hasTable('client_user');
        if (!empty($clientIds) && $hasClientUserTable) {
            $user->clients()->sync($clientIds);
        }

        $relations = ['client'];
        if ($hasClientUserTable) {
            $relations[] = 'clients';
        }

        $user->load($relations)->loadCount([
            'assignedTasks',
            'assignedTasks as active_tasks_count' => fn ($q) => $q->whereIn('status', ['todo', 'inprogress', 'review']),
            'assignedTasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
        ]);

        return $user;
    }

    public function updateMember(User $user, array $data)
    {
        $hasClientUserTable = \Illuminate\Support\Facades\Schema::hasTable('client_user');
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $this->normalizeRole((string) ($data['role'] ?? 'designer'));

        if (isset($data['client_ids'])) {
            $clientIds = array_filter((array) $data['client_ids']);
            if ($hasClientUserTable) {
                $user->clients()->sync($clientIds);
            }
            $user->client_id = !empty($clientIds) ? reset($clientIds) : null;
        } elseif (array_key_exists('client_id', $data)) {
            $clientId = !empty($data['client_id']) ? $data['client_id'] : null;
            $user->client_id = $clientId;
            if ($hasClientUserTable) {
                $user->clients()->sync($clientId ? [$clientId] : []);
            }
        }

        $additional = array_map([$this, 'normalizeRole'], (array) ($data['additional_roles'] ?? []));
        $user->additional_roles = array_values(array_unique(array_filter($additional, fn($r) => !empty($r) && $r !== $user->role)));
        $user->can_manage_social_metrics = (bool) ($data['can_manage_social_metrics'] ?? false);

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        if (!empty($data['avatar_color'])) {
            $user->avatar_color = $data['avatar_color'];
        }

        $user->save();

        $relations = ['client'];
        if ($hasClientUserTable) {
            $relations[] = 'clients';
        }

        $user->load($relations)->loadCount([
            'assignedTasks',
            'assignedTasks as active_tasks_count' => fn ($q) => $q->whereIn('status', ['todo', 'inprogress', 'review']),
            'assignedTasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
        ]);

        return $user;
    }

    public function deleteMember(User $user)
    {
        if ($user->id === Auth::id()) {
            throw new \Exception('Cannot delete self');
        }

        $user->delete();
    }

    public function getMemberDetails(User $user)
    {
        $relations = ['client'];
        if (\Illuminate\Support\Facades\Schema::hasTable('client_user')) {
            $relations[] = 'clients';
        }

        $user->load($relations)->loadCount([
            'assignedTasks',
            'assignedTasks as active_tasks_count' => fn ($q) => $q->whereIn('status', ['todo', 'inprogress', 'review']),
            'assignedTasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
        ]);

        $recentTasks = Task::where('assigned_to', $user->id)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get(['id', 'title', 'status', 'priority', 'deadline', 'updated_at']);

        $monthlyStats = Task::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->where('updated_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $statusBreakdown = Task::where('assigned_to', $user->id)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $avgCompletionDays = Task::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('updated_at')
            ->whereNotNull('created_at')
            ->selectRaw("AVG(DATEDIFF(updated_at, created_at)) as avg_days")
            ->value('avg_days');

        return compact('user', 'recentTasks', 'monthlyStats', 'statusBreakdown', 'avgCompletionDays');
    }

    public function getStats()
    {
        return [
            'total' => User::count(),
            'byRole' => User::selectRaw('role, COUNT(*) as count')->groupBy('role')->pluck('count', 'role'),
        ];
    }

    public function formatMemberForJson(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'client_id' => $user->client_id,
            'client' => $user->client ? [
                'id' => $user->client->id,
                'name' => $user->client->name,
                'emoji' => $user->client->emoji,
                'color' => $user->client->color,
                'logo' => $user->client->logo,
                'category' => $user->client->category,
            ] : null,
            'clients' => $user->clients ? $user->clients->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'emoji' => $c->emoji,
                'color' => $c->color,
                'logo' => $c->logo,
                'category' => $c->category,
            ]) : [],
            'additional_roles' => $user->additional_roles ?? [],
            'can_manage_social_metrics' => $user->can_manage_social_metrics,
            'avatar_color' => $user->avatar_color ?? '#4F6DF0',
            'assigned_tasks_count' => $user->assigned_tasks_count ?? 0,
            'active_tasks_count' => $user->active_tasks_count ?? 0,
            'completed_tasks_count' => $user->completed_tasks_count ?? 0,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}

