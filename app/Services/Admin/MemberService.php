<?php

namespace App\Services\Admin;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberService
{
    public function getFilteredMembers(Request $request)
    {
        $query = Employee::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        switch ($request->input('sort', 'name')) {
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        $members = $query->withCount([
            'tasks',
            'tasks as active_tasks_count' => fn ($q) => $q->whereIn('status', ['todo', 'inprogress', 'review']),
            'tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
        ])->get();

        $professions = Employee::select('role')->distinct()->orderBy('role')->pluck('role');

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'inactive' => Employee::where('status', 'inactive')->count(),
            'on_leave' => Employee::where('status', 'on_leave')->count(),
            'byRole' => Employee::selectRaw('role, COUNT(*) as count')->groupBy('role')->pluck('count', 'role'),
        ];

        return compact('members', 'professions', 'stats');
    }

    public function createMember(array $validated)
    {
        if (empty($validated['avatar_color'])) {
            $colors = ['#4F6DF0', '#10B981', '#F97316', '#8B5CF6', '#EC4899', '#EF4444', '#14B8A6', '#6366F1'];
            $validated['avatar_color'] = $colors[array_rand($colors)];
        }

        $password = $validated['password'];
        $passwordDisplay = $password;
        unset($validated['password']);

        $employee = Employee::create($validated);

        try {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => $validated['role'],
                'avatar_color' => $validated['avatar_color'],
            ]);
        } catch (\Exception $e) {
            $user = User::where('email', $validated['email'])->first();
            if ($user) {
                $user->update([
                    'role' => $validated['role'],
                    'avatar_color' => $validated['avatar_color'],
                    'password' => Hash::make($password),
                ]);
            }
        }

        return ['employee' => $employee, 'passwordDisplay' => $passwordDisplay];
    }

    public function updateMember(Employee $employee, array $validated)
    {
        $employee->update($validated);

        return $employee;
    }

    public function deleteMember(Employee $employee)
    {
        $employee->delete();

        return true;
    }
}

