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

        $clients = Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'logo', 'emoji', 'color']);
        $designers = \App\Models\User::whereIn('role', ['designer', 'content_writer', 'editor', 'strategist', 'admin'])->orderBy('name')->get(['id', 'name', 'avatar_color', 'role']);

        return view('calendar.planner', $planner->buildPlanner($anchor, $view, $clientId, $mine, Auth::id(), $filters) + [
            'calendarRoute' => 'admin.calendar',
            'clients'       => $clients,
            'clientId'      => $clientId,
            'mine'          => $mine,
            'filters'       => $filters,
            'designers'     => $designers,
            'states'        => PlannerService::STATES,
        ]);
    }

    public function clientCalendar(Client $client)
    {
        $data = $this->service->getClientCalendarData($client);

        return view('admin.client-calendar', array_merge($data, compact('client')));
    }
}
