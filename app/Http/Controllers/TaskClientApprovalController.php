<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskClientApprovalController extends Controller
{
    /**
     * Toggle client approval between Yes and No.
     */
    public function toggle(Request $request, Task $task)
    {
        $wasApproved = $task->isClientApproved();
        $user = Auth::user();

        if ($wasApproved) {
            $task->update([
                'client_approved_at' => null,
                'client_approved_by' => null,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'action'  => 'Client approval changed to No',
                'details' => "Marked as Client Approved: No by {$user->name}",
            ]);

            $isApproved = false;
            $message = 'Client approval status marked as NO.';
        } else {
            $task->update([
                'client_approved_at' => now(),
                'client_approved_by' => $user->id,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'action'  => 'Client approval confirmed (Yes)',
                'details' => "Marked as Client Approved: Yes by {$user->name}",
            ]);

            $isApproved = true;
            $message = 'Client approval status marked as YES.';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'is_approved' => $isApproved,
                'label'       => $isApproved ? 'Yes' : 'No',
                'approved_at' => $isApproved ? $task->client_approved_at->format('d M, h:i A') : null,
                'approved_by' => $isApproved ? $user->name : null,
                'message'     => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
