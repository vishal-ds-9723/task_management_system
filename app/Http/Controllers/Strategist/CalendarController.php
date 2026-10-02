<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Strategist\CalendarService;

class CalendarController extends Controller
{
    protected $service;

    public function __construct(CalendarService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $clients = $this->service->getClientsWithStats();

        return view('strategist.calendar', compact('clients'));
    }

    public function clientCalendar(Client $client)
    {
        $festivalSelections = $this->service->getClientFestivalSelections($client);

        return view('strategist.client-calendar', compact('client', 'festivalSelections'));
    }
}
