<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamMemberRequest;
use App\Http\Requests\Admin\UpdateTeamMemberRequest;
use App\Services\Admin\TeamService;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TeamController extends Controller
{
    protected $service;

    public function __construct(TeamService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getIndexData($request);
        $data['clients'] = Client::orderBy('name')->get();

        if ($request->ajax()) {
            return view('admin.team', $data);
        }

        return view('admin.team', $data);
    }

    public function storeAjax(StoreTeamMemberRequest $request)
    {
        try {
            $user = $this->service->createMember($request->validated());

            return response()->json([
                'success' => true,
                'message' => "Team member '{$user->name}' added successfully!",
                'member'  => $this->service->formatMemberForJson($user),
                'stats'   => $this->service->getStats(),
            ]);
        } catch (Throwable $e) {
            Log::error('Team member create failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add team member. Please verify details and try again.',
            ], 500);
        }
    }

    public function store(StoreTeamMemberRequest $request)
    {
        $this->service->createMember($request->validated());

        return back()->with('success', "Team member added successfully!");
    }

    public function updateAjax(UpdateTeamMemberRequest $request, User $user)
    {
        try {
            $user = $this->service->updateMember($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => "'{$user->name}' updated successfully!",
                'member'  => $this->service->formatMemberForJson($user),
                'stats'   => $this->service->getStats(),
            ]);
        } catch (Throwable $e) {
            Log::error('Team member update failed.', [
                'member_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update team member. Please try again.',
            ], 500);
        }
    }

    public function edit(User $user)
    {
        $roles = User::select('role')->distinct()->orderBy('role')->pluck('role');
        $allRoles = User::ALLOWED_ROLES;
        $existingRoles = $roles->toArray();
        $mergedRoles = array_values(array_unique(array_merge($allRoles, $existingRoles)));

        $relations = ['client'];
        if (\Illuminate\Support\Facades\Schema::hasTable('client_user')) {
            $relations[] = 'clients';
        }

        $user->load($relations)->loadCount([
            'assignedTasks',
            'assignedTasks as active_tasks_count' => fn ($q) => $q->whereIn('status', ['todo', 'inprogress', 'review']),
            'assignedTasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
        ]);

        $clients = Client::orderBy('name')->get();

        return view('admin.team-edit', compact('user', 'roles', 'mergedRoles', 'clients'));
    }

    public function update(UpdateTeamMemberRequest $request, User $user)
    {
        $this->service->updateMember($user, $request->validated());

        return redirect()->route('admin.team')->with('success', "'{$user->name}' updated successfully!");
    }

    public function destroyAjax(User $user)
    {
        $this->service->deleteMember($user);

        return response()->json([
            'success' => true,
            'message' => 'Team member removed.',
            'stats'   => $this->service->getStats(),
        ]);
    }

    public function destroy(User $user)
    {
        $this->service->deleteMember($user);

        return back()->with('success', 'Team member removed.');
    }

    public function show(User $user)
    {
        $data = $this->service->getMemberDetails($user);

        return response()->json([
            'success' => true,
            'member' => $data['user'],
            'recentTasks' => $data['recentTasks'],
            'monthlyStats' => $data['monthlyStats'],
            'statusBreakdown' => $data['statusBreakdown'],
            'avgCompletionDays' => $data['avgCompletionDays'],
        ]);
    }
}

