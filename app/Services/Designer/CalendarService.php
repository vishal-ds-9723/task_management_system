<?php

namespace App\Services\Designer;

use App\Models\Client;

class CalendarService
{
    public function getClients()
    {
        return Client::where('is_active', true)
            ->select('id', 'name', 'emoji', 'color')
            ->orderBy('name')
            ->get();
    }
}

