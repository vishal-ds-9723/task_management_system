<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Services\Strategist\FestivalSelectionService;
use Illuminate\Http\Request;

class FestivalSelectionController extends Controller
{
    protected $service;

    public function __construct(FestivalSelectionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getIndexData($request);

        return view('strategist.festival-selections', $data);
    }
}

