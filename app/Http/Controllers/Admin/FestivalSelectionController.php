<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\FestivalSelectionService;
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
        $data = $this->service->getFestivalData($request);

        return view('admin.festival-selections', $data);
    }

    public function show(\App\Models\FestivalSelection $selection)
    {
        if (request()->wantsJson() || request()->ajax()) {
            $selection->load(['festival', 'client', 'user', 'tasks']);
            return response()->json(['success' => true, 'selection' => $selection]);
        }

        return redirect()->route('admin.festival-selections', ['client_id' => $selection->client_id]);
    }
}

