<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class AuditService
{
    public function getFilteredLogs(Request $request)
    {
        $search = $request->get('search', '');
        $user_id = $request->get('user_id', '');
        $action_type = $request->get('action_type', '');
        $date_from = $request->get('date_from', '');
        $date_to = $request->get('date_to', '');
        $sort = $request->get('sort', 'oldest');
        $perPage = (int) $request->get('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100]) ? $perPage : 50;

        $query = AuditLog::with(['user', 'task']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'LIKE', "%{$search}%")
                  ->orWhereHas('task', function ($tq) use ($search) {
                      $tq->where('title', 'LIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($user_id)) {
            $query->where('user_id', $user_id);
        }

        if (!empty($action_type)) {
            $query->where('action', 'LIKE', "%{$action_type}%");
        }

        if (!empty($date_from)) {
            $query->whereDate('created_at', '>=', $date_from);
        }
        if (!empty($date_to)) {
            $query->whereDate('created_at', '<=', $date_to);
        }

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            default:
                $query->oldest();
        }

        $logs = $query->paginate($perPage)->appends($request->query());

        $users = User::where('role', 'admin')->get();
        $actionTypes = [
            'Created' => 'Created',
            'Updated' => 'Updated',
            'Deleted' => 'Deleted',
            'Cloned' => 'Cloned',
            'Status' => 'Status Changed',
            'Assigned' => 'Assigned',
        ];

        return compact('logs', 'users', 'actionTypes', 'search', 'user_id', 'action_type', 'date_from', 'date_to', 'sort', 'perPage');
    }

    public function getTaskHistory(Task $task)
    {
        $logs = AuditLog::with(['user'])
            ->where('task_id', $task->id)
            ->oldest()
            ->paginate(20);

        $task->load(['client', 'assignee', 'creator']);

        return compact('task', 'logs');
    }

    public function getAuditLogDetail(AuditLog $auditLog)
    {
        return response()->json([
            'id' => $auditLog->id,
            'action' => $auditLog->action,
            'user_name' => $auditLog->user?->name ?? 'System',
            'user_email' => $auditLog->user?->email ?? 'N/A',
            'ip_address' => $auditLog->ip_address ?? 'Unknown',
            'timestamp' => $auditLog->created_at->format('M d, Y H:i:s'),
            'old_values' => $auditLog->old_values,
            'new_values' => $auditLog->new_values,
        ]);
    }
}

