<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Admin\CalendarService;
use App\Services\Strategist\CalendarService as PlannerService;
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

    /**
     * Content calendar: the same channel × day planner the strategists use, across every client.
     */
    public function index(Request $request, PlannerService $planner)
    {
        $view = $request->input('view') === 'month' ? 'month' : 'week';

        try {
            $anchor = $request->filled('date') ? Carbon::parse($request->input('date'))->startOfDay() : today();
        } catch (\Throwable $e) {
            $anchor = today();
        }

        $clientId = $request->integer('client') ?: null;
        $mine = $request->boolean('mine');

        return view('calendar.planner', $planner->buildPlanner($anchor, $view, $clientId, $mine, Auth::id()) + [
            'calendarRoute' => 'admin.calendar',
            'clients' => Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'logo', 'emoji', 'color']),
            'clientId' => $clientId,
            'mine' => $mine,
            'states' => PlannerService::STATES,
        ]);
    }

    public function clientCalendar(Client $client)
    {
        $data = $this->service->getClientCalendarData($client);

        return view('admin.client-calendar', array_merge($data, compact('client')));
    }
}
