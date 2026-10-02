<?php

namespace App\Services\Admin;

use App\Models\Client;
use App\Models\FestivalSelection;
use App\Models\User;

class CalendarService
{
    public function getDashboardClients()
    {
        return Client::where('is_active', true)
            ->select('id', 'name', 'emoji', 'color', 'category', 'logo')
            ->withCount(['tasks as total_tasks'])
            ->withCount(['tasks as active_tasks' => fn($q) => $q->where('status', '!=', 'completed')])
            ->withCount(['tasks as overdue_tasks' => fn($q) => $q->where('status', '!=', 'completed')->whereNotNull('deadline')->where('deadline', '<', now())])
            ->orderBy('name')
            ->get();
    }

    public function getClientCalendarData(Client $client)
    {
        $festivalSelections = FestivalSelection::where('client_id', $client->id)
            ->with(['festival', 'user', 'tasks'])
            ->latest()
            ->get();

        $strategists = User::where('role', 'strategist')->get();

        return compact('festivalSelections', 'strategists');
    }
}

