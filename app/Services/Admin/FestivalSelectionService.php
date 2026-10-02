<?php

namespace App\Services\Admin;

use App\Models\Client;
use App\Models\Festival;
use App\Models\FestivalSelection;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FestivalSelectionService
{
    public function getFestivalData(Request $request)
    {
        $nextMonth = now()->addMonth()->startOfMonth();
        $nextMonthEnd = $nextMonth->copy()->endOfMonth();

        $clientId = $request->get('client_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 25);
        $sortBy = $request->get('sort', 'name');
        $sortDir = $request->get('dir', 'asc');

        $festivals = Festival::active()
            ->whereBetween('date', [$nextMonth, $nextMonthEnd])
            ->orderBy('date')
            ->get();

        $allSelections = FestivalSelection::with(['festival', 'client', 'user'])
            ->whereHas('festival', fn($q) => $q->whereBetween('date', [$nextMonth, $nextMonthEnd]))
            ->get();

        $totalSelections = $allSelections->count();
        $totalClients = $allSelections->pluck('client_id')->unique()->count();

        $typeCounts = [];
        foreach ($allSelections as $sel) {
            foreach ($sel->content_types ?? [] as $type) {
                $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
            }
        }
        arsort($typeCounts);

        $clientQuery = Client::where('is_active', true)
            ->whereHas('festivalSelections', fn($q) => $q->whereHas('festival', fn($fq) => $fq->whereBetween('date', [$nextMonth, $nextMonthEnd])))
            ->with(['festivalSelections' => fn($q) => $q->with(['festival', 'user'])->whereHas('festival', fn($fq) => $fq->whereBetween('date', [$nextMonth, $nextMonthEnd]))])
            ->when($clientId, fn($q) => $q->where('id', $clientId))
            ->when($search, fn($q) => $q->where('name', 'like', '%' . $search . '%'));

        if ($sortBy === 'count') {
            $clientQuery->withCount(['festivalSelections' => fn($q) => $q->whereHas('festival', fn($fq) => $fq->whereBetween('date', [$nextMonth, $nextMonthEnd]))])
                ->orderBy('festival_selections_count', $sortDir);
        } else {
            $clientQuery->orderBy('name', $sortDir);
        }

        $paginatedClients = $clientQuery->paginate($perPage)->appends($request->query());

        $clients = Client::where('is_active', true)->orderBy('name')->get();

        $monthLabel = $nextMonth->format('F Y');

        return compact(
            'festivals', 'clients', 'paginatedClients', 'clientId', 'search',
            'totalSelections', 'totalClients', 'typeCounts', 'monthLabel',
            'perPage', 'sortBy', 'sortDir'
        );
    }
}

