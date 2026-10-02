<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActionItem;
use App\Models\ActionItemNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobileActionItemController extends Controller
{
    /**
     * Get action items.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ActionItem::with(['client', 'assignee', 'creator', 'notes.user']);

        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        $items = $query->orderByRaw("status = 'done' asc")
                       ->orderByRaw("FIELD(priority, 'urgent', 'normal')")
                       ->orderBy('due_at', 'asc')
                       ->get()
                       ->map(function ($item) {
                           $item->is_completed = $item->status === 'done';
                           return $item;
                       });

        return response()->json([
            'success' => true,
            'action_items' => $items,
        ]);
    }

    /**
     * Create action item.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'client_id' => 'nullable|exists:clients,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:normal,urgent',
            'due_at' => 'nullable|date',
            'reminder_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $item = ActionItem::create([
            'title' => $request->title,
            'description' => $request->description,
            'client_id' => $request->client_id,
            'assigned_to' => $request->assigned_to ?? $request->user()->id,
            'created_by' => $request->user()->id,
            'priority' => $request->priority ?? 'normal',
            'due_at' => $request->due_at ?? now()->addDay(),
            'reminder_at' => $request->reminder_at,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Action item created',
            'action_item' => $item->load(['client', 'assignee']),
        ]);
    }

    /**
     * Toggle completed status.
     */
    public function toggleDone(Request $request, $id)
    {
        $item = ActionItem::findOrFail($id);
        $nowDone = $item->status !== 'done';
        $item->status = $nowDone ? 'done' : 'pending';
        $item->completed_at = $nowDone ? now() : null;
        $item->save();

        $fresh = $item->fresh(['client', 'assignee']);
        $fresh->is_completed = $fresh->status === 'done';

        return response()->json([
            'success' => true,
            'is_completed' => $fresh->is_completed,
            'action_item' => $fresh,
        ]);
    }

    /**
     * Add note to action item.
     */
    public function addNote(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error'], 422);
        }

        $note = ActionItemNote::create([
            'action_item_id' => $id,
            'user_id' => $request->user()->id,
            'note' => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'note' => $note->load('user'),
        ]);
    }

    /**
     * Snooze action item.
     */
    public function snooze(Request $request, $id)
    {
        $baseDate = $item->due_at ? $item->due_at->copy() : now();
        $item->due_at = $baseDate->addHours($hours);
        $item->save();

        return response()->json([
            'success' => true,
            'message' => "Snoozed by {$hours} hour(s).",
            'due_at' => $item->due_at->toIso8601String(),
        ]);
    }
}
