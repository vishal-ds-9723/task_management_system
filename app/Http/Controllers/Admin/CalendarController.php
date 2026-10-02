<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Admin\CalendarService;

class CalendarController extends Controller
{
    protected $service;

    public function __construct(CalendarService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $clients = $this->service->getDashboardClients();

        return view('admin.calendar', compact('clients'));
    }

    public function clientCalendar(Client $client)
    {
        $data = $this->service->getClientCalendarData($client);

        return view('admin.client-calendar', array_merge($data, compact('client')));
    }
}

