<?php

namespace App\Services\Strategist;

use App\Models\Client;
use App\Models\Festival;
use App\Models\FestivalSelection;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FestivalService
{
    /**
     * Get festivals data for index view.
     */
    public function getIndexData(Request $request): array
    {
        $currentMonth = now()->startOfMonth();
        $nextMonthEnd = now()->addMonth()->endOfMonth();

        $festivals = Festival::whereBetween('date', [$currentMonth, $nextMonthEnd])
            ->withCount('selections')
            ->with(['selections.client'])
            ->orderBy('date')
            ->get();

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $categories = ['religious', 'national', 'international', 'awareness', 'cultural'];
        $monthLabel = $currentMonth->format('F Y');
        $totalSelections = $festivals->sum('selections_count');

        return compact('festivals', 'clients', 'categories', 'monthLabel', 'totalSelections');
    }

    /**
     * Validate and create festival.
     */
    public function createFestival(array $data): Festival
    {
        return Festival::create(array_merge($data, ['is_active' => true]));
    }

    /**
     * Import festivals from Excel/CSV.
     */
    public function importFestivals(Request $request): array
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $sheets = Excel::toArray(null, $request->file('file'));
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('Failed to read Excel/CSV file: ' . $e->getMessage());
        }

        $rows = $sheets[0] ?? [];
        if (empty($rows)) {
            throw new \InvalidArgumentException('No rows found in file.');
        }

        $header = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
        $map = array_flip($header);
        $created = 0;
        $skipped = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $r = $rows[$i];
            $name = trim($r[$map['name']] ?? '');
            $dateCell = $r[$map['date']] ?? null;

            if (!$name || !$dateCell) {
                $skipped++;
                continue;
            }

            try {
                $date = Carbon::parse($dateCell);
            } catch (\Throwable $e) {
                $skipped++;
                continue;
            }

            $emoji = trim($r[$map['emoji']] ?? '');
            $description = trim($r[$map['description']] ?? '');
            $category = trim($r[$map['category']] ?? '');

            $festival = Festival::firstOrCreate(
                ['name' => $name, 'date' => $date->toDateString()],
                [
                    'emoji' => $emoji,
                    'description' => $description,
                    'category' => $category,
                    'is_active' => true
                ]
            );

            if ($festival->wasRecentlyCreated) $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'message' => "Imported {$created} festivals (skipped {$skipped})."
        ];
    }

    /**
     * Validate and update festival.
     */
    public function updateFestival(Festival $festival, array $data): bool
    {
        return $festival->update($data);
    }

    /**
     * Get calendar events for FullCalendar API.
     */
    public function getCalendarEvents(Request $request): Collection
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $query = Festival::with(['selections.client']);

        if ($start && $end) {
            $query->whereBetween('date', [$start, $end]);
        } else {
            $currentMonth = now()->startOfMonth();
            $nextMonthEnd = now()->addMonth()->endOfMonth();
            $query->whereBetween('date', [$currentMonth, $nextMonthEnd]);
        }

        return $query->orderBy('date')->get()->map(function (Festival $festival) {
            return [
                'id' => $festival->id,
                'title' => ($festival->emoji ?? '🎉') . ' ' . $festival->name,
                'start' => $festival->date,
                'classNames' => ['festival-event', $festival->category ?? 'other', $festival->is_active ? '' : 'inactive'],
                'extendedProps' => [
                    'name' => $festival->name,
                    'date' => $festival->date,
                    'emoji' => $festival->emoji ?? '🎉',
                    'category' => $festival->category ?? 'other',
                    'description' => $festival->description,
                    'is_active' => $festival->is_active,
                    'selections_count' => $festival->selections_count ?? 0,
                    'selections' => $festival->selections->map(fn (FestivalSelection $selection) => [
                        'client_name' => $selection->client->name ?? 'Unknown',
                        'content_types' => $selection->content_types ?? [],
                        'platforms' => $selection->platforms ?? [],
                        'notes' => $selection->notes ?? ''
                    ])
                ]
            ];
        });
    }
}

