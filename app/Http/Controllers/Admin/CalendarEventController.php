<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CalendarEventRequest;
use App\Models\Task;
use App\Services\Admin\CalendarEventService;

class CalendarEventController extends Controller
{
    protected $service;

    public function __construct(CalendarEventService $service)
    {
        $this->service = $service;
    }

    public function getEvents(CalendarEventRequest $request)
    {
        $events = $this->service->getEvents($request);

        return $events;
    }

    public function getClients()
    {
        $clients = $this->service->getClients();

        return $clients;
    }

    public function askForReview(Task $task)
    {
        $result = $this->service->askForReview($task);

        return $result;
    }

    public function reschedule(CalendarEventRequest $request, Task $task)
    {
        $result = $this->service->reschedule($request, $task);

        return $result;
    }
}

