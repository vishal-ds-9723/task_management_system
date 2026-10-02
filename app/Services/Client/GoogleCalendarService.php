<?php

namespace App\Services\Client;

use App\Models\FestivalSelection;
use App\Models\User;
use App\Models\Notification;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleCalendarService
{
    protected $googleClient;
    protected $calendarService;

    public function __construct()
    {
        $this->initializeGoogleClient();
    }

    protected function initializeGoogleClient()
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setApplicationName('TMS');
        $this->googleClient->setScopes([
            Google_Service_Calendar::CALENDAR,
            Google_Service_Calendar::CALENDAR_EVENTS
        ]);
        $this->googleClient->setAuthConfig(config_path('google-calendar-credentials.json'));
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setPrompt('consent');

        $this->calendarService = new Google_Service_Calendar($this->googleClient);
    }

    public function getAuthUrl()
    {
        $authUrl = $this->googleClient->createAuthUrl();
        return response()->json(['auth_url' => $authUrl]);
    }

    public function handleCallback(Request $request)
    {
        $code = $request->get('code');

        if ($code) {
            $accessToken = $this->googleClient->fetchAccessTokenWithAuthCode($code);
            $this->googleClient->setAccessToken($accessToken);

            session(['google_calendar_token' => $accessToken]);

            return redirect()->route('client.festivals')->with('success', 'Google Calendar connected successfully!');
        }

        return redirect()->route('client.festivals')->with('error', 'Failed to connect Google Calendar');
    }

    public function syncToGoogleCalendar(FestivalSelection $selection)
    {
        $token = session('google_calendar_token') ?? auth()->user()->google_calendar_token;

        if (!$token) {
            return response()->json(['error' => 'Google Calendar not connected'], 401);
        }

        $this->googleClient->setAccessToken($token);

        if ($this->googleClient->isAccessTokenExpired()) {
            $refreshToken = $token['refresh_token'] ?? null;
            if ($refreshToken) {
                $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);
                $token = $this->googleClient->getAccessToken();
                session(['google_calendar_token' => $token]);
            }
        }

        $event = $this->createCalendarEvent($selection);
        $event = $this->calendarService->events->insert('primary', $event);

        $selection->update(['google_event_id' => $event->getId()]);

        return response()->json([
            'success' => true,
            'message' => 'Festival synced to Google Calendar',
            'event_id' => $event->getId()
        ]);
    }

    protected function createCalendarEvent(FestivalSelection $selection)
    {
        $festival = $selection->festival;
        $client = $selection->client;

        $contentTypes = implode(', ', $selection->content_types ?? []);
        $description = "Festival Selection\n";
        $description .= "Client: {$client->name}\n";
        $description .= "Content Types: {$contentTypes}\n";
        if ($selection->notes) {
            $description .= "Notes: {$selection->notes}";
        }

        $event = new Google_Service_Calendar_Event([
            'summary' => "{$festival->name} - {$client->name}",
            'description' => $description,
            'start' => new Google_Service_Calendar_EventDateTime([
                'date' => $festival->date->format('Y-m-d'),
                'timeZone' => config('app.timezone', 'UTC'),
            ]),
            'end' => new Google_Service_Calendar_EventDateTime([
                'date' => $festival->date->addDay()->format('Y-m-d'),
                'timeZone' => config('app.timezone', 'UTC'),
            ]),
            'colorId' => '8',
            'transparency' => 'transparent',
            'visibility' => 'private',
        ]);

        return $event;
    }

    public function removeFromGoogleCalendar(FestivalSelection $selection)
    {
        $token = session('google_calendar_token') ?? auth()->user()->google_calendar_token;

        if (!$token || !$selection->google_event_id) {
            return response()->json(['error' => 'Event not synced to Google Calendar'], 400);
        }

        $this->googleClient->setAccessToken($token);

        if ($this->googleClient->isAccessTokenExpired()) {
            $refreshToken = $token['refresh_token'] ?? null;
            if ($refreshToken) {
                $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);
            }
        }

        $this->calendarService->events->delete('primary', $selection->google_event_id);

        $selection->update(['google_event_id' => null]);

        return response()->json(['success' => true, 'message' => 'Event removed from Google Calendar']);
    }

    public function getEmbedUrl()
    {
        $calendarId = auth()->user()->google_calendar_id ?? 'primary';
        $embedUrl = "https://calendar.google.com/calendar/embed?src={$calendarId}&ctz=" . urlencode(config('app.timezone', 'UTC'));

        return response()->json(['embed_url' => $embedUrl]);
    }

    public function disconnect()
    {
        $token = session('google_calendar_token');

        if ($token && isset($token['access_token'])) {
            $this->googleClient->revokeToken($token['access_token']);
        }

        session()->forget('google_calendar_token');
        auth()->user()->update([
            'google_calendar_token' => null,
            'google_calendar_id' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Google Calendar disconnected']);
    }
}

