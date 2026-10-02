<?php

namespace App\Services;

use App\Models\ActionItem;
use App\Models\ActionItemNote;
use App\Models\Client;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionItemService
{
    /**
     * Get filtered action items for admin dashboard (existing)
     */
    public function getFilteredItems(Request $request)
    {


        $query = ActionItem::with(['assignee', 'client', 'creator', 'parent', 'notes.user']);

        $awayMode = Setting::isStrategistAway();
        $userId = Auth::id();

        if (!$awayMode) {
            $query->where('created_by', $userId);
        }



        return $query->paginate(20);
    }

    /**
     * Get complete dashboard data for admin action items index
     */
    public function getDashboardData(Request $request)
    {
        $filter = $request->input('filter', 'active');
        $assigneeFilter = $request->input('assignee');
        $clientFilter = $request->input('client');
        $search = $request->input('search');

        $userId = Auth::id();
        $awayMode = Setting::isStrategistAway();
        $scopeQuery = function ($q) use ($awayMode, $userId) {
            return $awayMode ? $q : $q->where('created_by', $userId);
        };

        $filter = $request->input('filter', 'active');
        $assigneeFilter = $request->input('assignee');
        $clientFilter = $request->input('client');
        $search = $request->input('search');
        
        $items = $this->getFilteredItems($request);
        // Stats
        $stats = [
            'totalActive' => $scopeQuery(ActionItem::query())->whereIn('status', ['pending', 'in_progress'])->count(),
            'totalOverdue' => $scopeQuery(ActionItem::query())->where('status', '!=', 'done')->where('due_at', '<', now())->count(),
            'completedToday' => $scopeQuery(ActionItem::query())->where('status', 'done')->whereDate('completed_at', today())->count(),
            'totalDone' => $scopeQuery(ActionItem::query())->where('status', 'done')->count(),
            'totalInProgress' => $scopeQuery(ActionItem::query())->where('status', 'in_progress')->count(),
            'totalReminders' => $scopeQuery(ActionItem::query())->where('status', '!=', 'done')->whereNotNull('reminder_at')->count(),
            'remindersDue' => $scopeQuery(ActionItem::query())->where('status', '!=', 'done')->whereNotNull('reminder_at')->where('reminder_at', '<=', now())->count(),
            'totalAll' => $scopeQuery(ActionItem::query())->count(),
            'totalItems' => $scopeQuery(ActionItem::query())->count(),
        ];

        // Recent notes
        $recentNotes = ActionItemNote::whereHas('actionItem', $scopeQuery)->where('created_at', '>=', now()->subDay())->with(['user', 'actionItem'])->latest()->limit(10)->get();

        // Dropdown data
        $strategists = User::where('role', 'strategist')->orderBy('name')->get();
        $clients = Client::where('is_active', true)->orderBy('name')->get();

        $filter = $request->input('filter', 'active');
        $assigneeFilter = $request->input('assignee');
        $clientFilter = $request->input('client');
        $search = $request->input('search');

        return compact(
            'items',
            'stats',
            'recentNotes',
            'strategists',
            'clients',
            'filter',
            'assigneeFilter',
            'clientFilter',
            'search'
        );
    }

    /**
     * Create new action item with notification
     */
    public function createActionItem(array $data)
    {
        $data['status'] = 'pending';
        $data['created_by'] = Auth::id();

        $item = ActionItem::create($data);

        // Notify assignee
        Notification::create([
            'user_id' => $item->assigned_to,
            'icon' => $item->priority === 'urgent' ? '🔴' : '📌',
            'title' => 'New action item assigned',
            'subtitle' => $item->title . ($item->client ? ' · ' . $item->client->name : ''),
            'link' => '/strategist/action-items',
        ]);

        return $item;
    }

    /**
     * Update action item, notify if reassigned
     */
    public function updateActionItem(ActionItem $item, array $data)
    {
        $oldAssignee = $item->assigned_to;
        $item->update($data);

        if ($oldAssignee != $data['assigned_to']) {
            Notification::create([
                'user_id' => $data['assigned_to'],
                'icon' => '🔄',
                'title' => 'Action item reassigned to you',
                'subtitle' => $item->title,
                'link' => '/strategist/action-items',
            ]);
        }

        return $item;
    }

    /**
     * Delete action item (auth check inside)
     */
    public function deleteActionItem(ActionItem $item)
    {
        if ($item->created_by !== Auth::id()) {
            throw new \Exception('Unauthorized');
        }

        $item->delete();

        return true;
    }
}

