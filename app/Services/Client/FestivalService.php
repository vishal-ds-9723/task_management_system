<?php

namespace App\Services\Client;

use App\Models\Festival;
use App\Models\FestivalSelection;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FestivalService
{
    public function getIndexData(Request $request)
    {
        $clientId = auth()->user()->client_id;

        $monthStr = $request->query('month');
        $targetDate = $monthStr ? Carbon::parse($monthStr)->startOfMonth() : now()->addMonth()->startOfMonth();
        
        $start = $targetDate->copy()->startOfMonth();
        $end   = $targetDate->copy()->endOfMonth();

        $festivals = Festival::active()
            ->whereBetween('date', [$start, $end])
            ->with(['selections' => fn($q) => $q->where('client_id', $clientId)])
            ->orderBy('date')
            ->get()
            ->map(function ($festival) use ($clientId) {
                $sel = $festival->selections->where('client_id', $clientId)->first();
                $festival->is_selected = !!$sel;
                $festival->my_notes = $sel ? $sel->notes : '';
                return $festival;
            });

        $monthLabel = $targetDate->format('F Y');
        $monthDate  = $targetDate->toDateString();
        $selectedCount = $festivals->where('is_selected', true)->count();

        $currentMonth = now()->startOfMonth();
        $nextMonth    = now()->addMonth()->startOfMonth();
        $isSelectionAllowed = $targetDate->equalTo($currentMonth) || $targetDate->equalTo($nextMonth);

        return compact('festivals', 'monthLabel', 'monthDate', 'selectedCount', 'isSelectionAllowed');
    }

    public function selectFestival(Festival $festival, Request $request)
    {
        $currentMonth = now()->startOfMonth();
        $nextMonth    = now()->addMonth()->startOfMonth();
        $festivalMonth = $festival->date->copy()->startOfMonth();

        if (!$festivalMonth->equalTo($currentMonth) && !$festivalMonth->equalTo($nextMonth)) {
            return response()->json([
                'success' => false,
                'message' => 'Selection is only allowed for the current and next month.',
            ], 403);
        }

        $request->validate([
            'notes'       => 'nullable|string|max:500',
        ]);

        $clientId = auth()->user()->client_id;
        $user = auth()->user();

        $selection = FestivalSelection::updateOrCreate(
            ['festival_id' => $festival->id, 'client_id' => $clientId],
            [
                'user_id'       => $user->id,
                'content_types' => $request->content_types,
                'platforms'     => $request->platforms,
                'notes'         => $request->notes,
            ]
        );

        $usersToNotify = User::whereIn('role', ['admin', 'strategist'])->get();
        $clientName = $user->client->name ?? 'A client';
        
        foreach ($usersToNotify as $recipient) {
            Notification::create([
                'user_id' => $recipient->id,
                'icon'    => '🎉',
                'title'   => 'New festival selection',
                'subtitle'=> "{$clientName} selected \"{$festival->name}\"",
                'link'    => '/admin/festival-selections',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Festival selected successfully!',
        ]);
    }

    public function deselectFestival(Festival $festival)
    {
        $currentMonth = now()->startOfMonth();
        $nextMonth    = now()->addMonth()->startOfMonth();
        $festivalMonth = $festival->date->copy()->startOfMonth();

        if (!$festivalMonth->equalTo($currentMonth) && !$festivalMonth->equalTo($nextMonth)) {
            return response()->json([
                'success' => false,
                'message' => 'Deselection is only allowed for the current and next month.',
            ], 403);
        }

        $clientId = auth()->user()->client_id;

        FestivalSelection::where('festival_id', $festival->id)
            ->where('client_id', $clientId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Festival deselected.',
        ]);
    }
}

