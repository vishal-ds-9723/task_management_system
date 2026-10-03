<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Strategist\CalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    protected $service;

    public function __construct(CalendarService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $view = $request->input('view') === 'month' ? 'month' : 'week';

        try {
            $anchor = $request->filled('date') ? Carbon::parse($request->input('date'))->startOfDay() : today();
        } catch (\Throwable $e) {
            $anchor = today();
        }

        $clientId = $request->integer('client') ?: null;
        $mine = $request->boolean('mine');

        $planner = $this->service->buildPlanner($anchor, $view, $clientId, $mine, Auth::id());
        $clients = Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'logo', 'emoji', 'color']);

        return view('calendar.planner', $planner + [
            'calendarRoute' => 'strategist.calendar',
            'clients' => $clients,
            'clientId' => $clientId,
            'mine' => $mine,
            'states' => CalendarService::STATES,
        ]);
    }

    public function clientCalendar(Client $client)
    {
        $festivalSelections = $this->service->getClientFestivalSelections($client);

        return view('strategist.client-calendar', compact('client', 'festivalSelections'));
    }
}
