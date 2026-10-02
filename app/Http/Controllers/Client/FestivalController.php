<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\FestivalService;
use App\Models\Festival;
use Illuminate\Http\Request;

class FestivalController extends Controller
{
    protected $service;

    public function __construct(FestivalService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getIndexData($request);

        return view('client.festivals', $data);
    }

    public function select(Request $request, Festival $festival)
    {
        return $this->service->selectFestival($festival, $request);
    }

    public function deselect(Festival $festival)
    {
        return $this->service->deselectFestival($festival);
    }
}

