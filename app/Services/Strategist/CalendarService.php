<?php

namespace App\Services\Strategist;

use App\Models\Client;
use App\Models\FestivalSelection;

class CalendarService
{
    /**
     * Get all active clients with strategist task statistics.
     */
    public function getClientsWithStats(): \Illuminate\Database\Eloquent\Collection
    {
        return Client::where('is_active', true)
            ->withCount([
                'tasks as total_tasks' => fn($q) => $q->whereHas('creator', fn($sq) => $sq->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin'])),
                'tasks as active_tasks' => fn($q) => $q->whereHas('creator', fn($sq) => $sq->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin']))
                    ->where('status', '!=', 'completed'),
                'tasks as overdue_tasks' => fn($q) => $q->whereHas('creator', fn($sq) => $sq->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin']))
                    ->where('status', '!=', 'completed')
                    ->whereNotNull('deadline')
                    ->where('deadline', '<', now())
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get festival selections for a specific client.
     */
    public function getClientFestivalSelections(Client $client)
    {
        return FestivalSelection::where('client_id', $client->id)
            ->with(['festival', 'user', 'tasks'])
            ->latest()
            ->get();
    }
}
