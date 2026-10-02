<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class MobileAuditController extends Controller
{
    /**
     * Get system audit logs.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $query = AuditLog::with(['user', 'task.client']);

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action') && !empty($request->action)) {
            $query->where('action', $request->action);
        }

        if ($request->has('search') && !empty($request->search)) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('action', 'like', $term)
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', $term))
                  ->orWhereHas('task', fn($t) => $t->where('title', 'like', $term));
            });
        }

        $logs = $query->latest()->paginate(25);

        return response()->json([
            'success' => true,
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Get audit log detail.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $log = AuditLog::with(['user', 'task.client'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'log' => $log,
        ]);
    }

    /**
     * Get audit history for a specific task.
     */
    public function taskHistory(Request $request, $taskId)
    {
        $logs = AuditLog::with('user')
            ->where('task_id', $taskId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'history' => $logs,
        ]);
    }
}
