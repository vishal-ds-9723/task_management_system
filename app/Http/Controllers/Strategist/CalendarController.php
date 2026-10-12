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
        $view = in_array($request->input('view'), ['month', 'feed'], true) ? $request->input('view') : 'week';

        try {
            $anchor = $request->filled('date') ? Carbon::parse($request->input('date'))->startOfDay() : today();
        } catch (\Throwable $e) {
            $anchor = today();
        }

        $clientId = $request->integer('client') ?: null;
        $mine = $request->boolean('mine');
        $filters = [
            'platform' => $request->input('platform'),
            'type'     => $request->input('type'),
            'assignee' => $request->integer('assignee') ?: null,
        ];

        $planner = $this->service->buildPlanner($anchor, $view, $clientId, $mine, Auth::id(), $filters);
        $clients = Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'logo', 'emoji', 'color']);
        $designers = \App\Models\User::whereIn('role', ['designer', 'content_writer', 'editor', 'strategist', 'admin'])->orderBy('name')->get(['id', 'name', 'avatar_color', 'role']);

        return view('calendar.planner', $planner + [
            'calendarRoute' => 'strategist.calendar',
            'clients' => $clients,
            'clientId' => $clientId,
            'mine' => $mine,
            'filters' => $filters,
            'designers' => $designers,
            'states' => CalendarService::STATES,
        ]);
    }

    public function clientCalendar(Client $client)
    {
        $festivalSelections = $this->service->getClientFestivalSelections($client);

        return view('strategist.client-calendar', compact('client', 'festivalSelections'));
    }
}
