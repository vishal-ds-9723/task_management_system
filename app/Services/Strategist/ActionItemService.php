<?php

namespace App\Services\Strategist;

use App\Models\ActionItem;
use App\Models\ActionItemNote;
use App\Models\Client;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActionItemService
{
    public function getIndexData(Request $request)
    {
        $filter = $request->input('filter', 'active');
        $search = $request->input('search');
        $clientFilter = $request->input('client');
        $sort = $request->input('sort', 'due_date');

        $query = ActionItem::with(['creator', 'client', 'parent', 'notes.user'])
            ->where('assigned_to', Auth::id());

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($clientFilter) {
            $query->where('client_id', $clientFilter);
        }

        switch ($filter) {
            case 'done':
                $query->where('status', 'done');
                $sort === 'priority' ? $query->orderByRaw("FIELD(priority, 'urgent', 'normal')")->latest('completed_at') : ($sort === 'newest' ? $query->latest() : $query->latest('completed_at'));
                break;
            case 'overdue':
                $query->where('status', '!=', 'done')->where('due_at', '<', now());
                $sort === 'priority' ? $query->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('due_at') : ($sort === 'newest' ? $query->latest() : $query->orderBy('due_at'));
                break;
            case 'in_progress':
                $query->where('status', 'in_progress');
                $sort === 'priority' ? $query->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('due_at') : ($sort === 'newest' ? $query->latest() : $query->orderBy('due_at'));
                break;
            case 'reminders':
                $query->where('status', '!=', 'done')->whereNotNull('reminder_at');
                $sort === 'priority' ? $query->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('reminder_at') : ($sort === 'newest' ? $query->latest() : $query->orderBy('reminder_at'));
                break;
            case 'all':
                $sort === 'priority' ? $query->orderByRaw("FIELD(priority, 'urgent', 'normal')")->latest() : ($sort === 'newest' ? $query->latest() : $query->latest());
                break;
            default: // active
                $query->whereIn('status', ['pending', 'in_progress']);
                $sort === 'priority' ? $query->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('due_at') : ($sort === 'newest' ? $query->latest() : $query->orderBy('due_at'));
                break;
        }

        $items = $query->paginate(20);

        $userId = Auth::id();
        $totalActive = ActionItem::where('assigned_to', $userId)->whereIn('status', ['pending', 'in_progress'])->count();
        $totalOverdue = ActionItem::where('assigned_to', $userId)->where('status', '!=', 'done')->where('due_at', '<', now())->count();
        $completedToday = ActionItem::where('assigned_to', $userId)->where('status', 'done')->whereDate('completed_at', today())->count();
        $totalInProgress = ActionItem::where('assigned_to', $userId)->where('status', 'in_progress')->count();
        $totalDone = ActionItem::where('assigned_to', $userId)->where('status', 'done')->count();
        $totalAll = ActionItem::where('assigned_to', $userId)->count();
        $totalItems = $totalAll;
        $totalReminders = ActionItem::where('assigned_to', $userId)->where('status', '!=', 'done')->whereNotNull('reminder_at')->count();
        $remindersDue = ActionItem::where('assigned_to', $userId)->where('status', '!=', 'done')->whereNotNull('reminder_at')->where('reminder_at', '<=', now())->count();

        $clients = Client::whereHas('actionItems', function ($q) use ($userId) {
            $q->where('assigned_to', $userId);
        })->orderBy('name')->get();

        $allClients = Client::where('is_active', true)->orderBy('name')->get();

        return compact(
            'items', 'filter', 'search', 'clientFilter', 'sort',
            'totalActive', 'totalOverdue', 'completedToday',
            'totalInProgress', 'totalDone', 'totalAll', 'totalItems',
            'totalReminders', 'remindersDue',
            'clients', 'allClients'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'client_id' => 'nullable|exists:clients,id',
            'due_at' => 'required|date|after_or_equal:now',
            'priority' => 'required|in:normal,urgent',
            'recurring' => 'nullable|in:daily,weekly,monthly',
            'reminder_at' => 'nullable|date|after_or_equal:now',
            'reminder_note' => 'nullable|string|max:500',
        ]);

        $item = ActionItem::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'client_id' => $request->input('client_id') ?: null,
            'assigned_to' => Auth::id(),
            'created_by' => Auth::id(),
            'due_at' => $request->input('due_at'),
            'priority' => $request->input('priority'),
            'status' => 'pending',
            'recurring' => $request->input('recurring'),
            'reminder_at' => $request->input('reminder_at'),
            'reminder_note' => $request->input('reminder_note'),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Action item created!', 'id' => $item->id]);
        }

        return back()->with('success', 'Action item created!');
    }

    public function update(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id() || $actionItem->created_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'client_id' => 'nullable|exists:clients,id',
            'due_at' => 'required|date',
            'priority' => 'required|in:normal,urgent',
            'recurring' => 'nullable|in:daily,weekly,monthly',
        ]);

        $actionItem->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'client_id' => $request->input('client_id') ?: null,
            'due_at' => $request->input('due_at'),
            'priority' => $request->input('priority'),
            'recurring' => $request->input('recurring'),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Action item updated!']);
        }

        return back()->with('success', 'Action item updated!');
    }

    public function markDone(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'completion_note' => 'required|string|max:500',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
        ]);

        $actionItem->update([
            'status' => 'done',
            'completed_at' => now(),
            'completion_note' => $request->input('completion_note'),
            'follow_up_date' => $request->input('follow_up_date'),
        ]);

        Notification::create([
            'user_id' => $actionItem->created_by,
            'icon' => '✅',
            'title' => 'Action item completed',
            'subtitle' => $actionItem->title . ' — "' . Str::limit($request->input('completion_note'), 50) . '"',
            'link' => '/admin/action-items',
        ]);

        if ($request->input('follow_up_date')) {
            ActionItem::create([
                'title' => 'Follow-up: ' . $actionItem->title,
                'description' => 'Follow-up from completed item. Note: ' . $request->input('completion_note'),
                'client_id' => $actionItem->client_id,
                'assigned_to' => $actionItem->assigned_to,
                'created_by' => $actionItem->created_by,
                'due_at' => $request->input('follow_up_date') . ' 10:00:00',
                'priority' => $actionItem->priority,
                'status' => 'pending',
                'parent_id' => $actionItem->id,
            ]);
        }

        if ($actionItem->recurring) {
            $nextDue = match ($actionItem->recurring) {
                'daily' => $actionItem->due_at->addDay(),
                'weekly' => $actionItem->due_at->addWeek(),
                'monthly' => $actionItem->due_at->addMonth(),
            };

            ActionItem::create([
                'title' => $actionItem->title,
                'description' => $actionItem->description,
                'client_id' => $actionItem->client_id,
                'assigned_to' => $actionItem->assigned_to,
                'created_by' => $actionItem->created_by,
                'due_at' => $nextDue,
                'priority' => $actionItem->priority,
                'status' => 'pending',
                'recurring' => $actionItem->recurring,
                'parent_id' => $actionItem->id,
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Marked as done!']);
        }

        return back()->with('success', 'Marked as done!');
    }

    public function startProgress(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $actionItem->update(['status' => 'in_progress']);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Started!']);
        }

        return back()->with('success', 'Started!');
    }

    public function reopen(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $actionItem->update([
            'status' => 'pending',
            'completed_at' => null,
            'completion_note' => null,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Reopened!']);
        }

        return back()->with('success', 'Reopened!');
    }

    public function addNote(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'note' => 'required|string|max:500',
        ]);

        $note = ActionItemNote::create([
            'action_item_id' => $actionItem->id,
            'user_id' => Auth::id(),
            'note' => $request->input('note'),
        ]);

        Notification::create([
            'user_id' => $actionItem->created_by,
            'icon' => '💬',
            'title' => 'Note added on action item',
            'subtitle' => $actionItem->title . ' — "' . Str::limit($request->input('note'), 50) . '"',
            'link' => '/admin/action-items',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'note' => [
                    'id' => $note->id,
                    'note' => $note->note,
                    'user_name' => Auth::user()->name,
                    'created_at' => $note->created_at->diffForHumans(),
                ],
            ]);
        }

        return back()->with('success', 'Note added!');
    }

    public function snooze(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $hours = (int) $request->input('hours', 1);
        $hours = min($hours, 24);

        $actionItem->update([
            'due_at' => $actionItem->due_at->addHours($hours),
        ]);

        return back()->with('success', "Snoozed by {$hours} hour(s).");
    }

    public function setReminder(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'reminder_at' => 'required|date|after:now',
            'reminder_note' => 'required|string|max:500',
        ]);

        $actionItem->update([
            'reminder_at' => $request->input('reminder_at'),
            'reminder_note' => $request->input('reminder_note'),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Reminder set for ' . $actionItem->reminder_at->format('d M, h:i A'),
            ]);
        }

        return back()->with('success', 'Reminder set!');
    }

    public function clearReminder(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $actionItem->update([
            'reminder_at' => null,
            'reminder_note' => null,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Reminder cleared']);
        }

        return back()->with('success', 'Reminder cleared!');
    }

    public function getActiveReminders(Request $request)
    {
        $reminders = ActionItem::where('assigned_to', Auth::id())
            ->where('status', '!=', 'done')
            ->where('reminder_at', '!=', null)
            ->with(['client', 'creator'])
            ->orderBy('reminder_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'reminder_at' => $item->reminder_at->toIso8601String(),
                    'reminder_note' => $item->reminder_note,
                    'is_due' => $item->isReminderDue(),
                    'client' => $item->client ? ['name' => $item->client->name, 'emoji' => $item->client->emoji] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'reminders' => $reminders,
            'total' => $reminders->count(),
            'due' => $reminders->where('is_due', true)->count(),
        ]);
    }
}

