<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\GoogleCalendarService;
use App\Models\FestivalSelection;
use Illuminate\Http\Request;

class GoogleCalendarController extends Controller
{
    protected $service;

    public function __construct(GoogleCalendarService $service)
    {
        $this->service = $service;
    }

    public function getAuthUrl()
    {
        return $this->service->getAuthUrl();
    }

    public function handleCallback(Request $request)
    {
        return $this->service->handleCallback($request);
    }

    public function syncToGoogleCalendar(FestivalSelection $selection)
    {
        return $this->service->syncToGoogleCalendar($selection);
    }

    public function removeFromGoogleCalendar(FestivalSelection $selection)
    {
        return $this->service->removeFromGoogleCalendar($selection);
    }

    public function getEmbedUrl()
    {
        return $this->service->getEmbedUrl();
    }

    public function disconnect()
    {
        return $this->service->disconnect();
    }
}

