<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Services\Strategist\FestivalService;
use Illuminate\Http\Request;
use App\Http\Requests\Strategist\StoreFestivalRequest;
use App\Http\Requests\Strategist\UpdateFestivalRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Festival;
use Carbon\Carbon;

class FestivalController extends Controller
{
    protected $service;

    public function __construct(FestivalService $service)
    {
        $this->service = $service;
    }
    /**
     * Show festival calendar for next month.
     * Strategist can manage festivals and see client selections.
     */
    public function index(Request $request)
    {
        $data = $this->service->getIndexData($request);

        return view('strategist.festival-calendar', $data);
    }

    public function store(StoreFestivalRequest $request)
    {
        $festival = $this->service->createFestival($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'festival' => $festival]);
        }

        return back()->with('success', 'Festival added!');
    }

    /**
     * Import festivals from an uploaded Excel/CSV file.
     * Expected header row: name, date, emoji, description, category
     */
    public function import(Request $request)
    {
        $result = $this->service->importFestivals($request);

        return response()->json($result);
    }

    public function update(UpdateFestivalRequest $request, Festival $festival)
    {
        $this->service->updateFestival($festival, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'festival' => $festival]);
        }

        return back()->with('success', 'Festival updated!');
    }

    public function toggle(Festival $festival)
    {
        $festival->update(['is_active' => !$festival->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $festival->is_active,
        ]);
    }

    public function destroy(Festival $festival)
    {
        $festival->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Festival removed.');
    }

    /**
     * API endpoint for festival calendar events (FullCalendar)
     */
    public function calendarEvents(Request $request)
    {
        $events = $this->service->getCalendarEvents($request);
        return response()->json($events);
    }
}
