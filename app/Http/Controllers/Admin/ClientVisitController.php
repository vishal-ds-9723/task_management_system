<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientVisitRequest;
use App\Http\Requests\Admin\UpdateClientVisitRequest;
use App\Http\Requests\Admin\StoreClientVisitUpdateRequest;
use App\Models\ClientVisit;
use App\Services\Admin\ClientVisitService;
use Illuminate\Http\Request;

class ClientVisitController extends Controller
{
    protected ClientVisitService $service;

    public function __construct(ClientVisitService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getFilteredVisits($request);

        return view('admin.visits', $data);
    }

    public function store(StoreClientVisitRequest $request)
    {
        $visit = $this->service->createVisit($request->validated(), auth()->id());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'visit' => $visit]);
        }

        return redirect()->route('admin.visits', ['tab' => $request->visit_type])
            ->with('success', 'Visit logged successfully.');
    }

    public function show(ClientVisit $visit)
    {
        $visit->load(['client', 'visitor', 'creator', 'updates.user']);

        return response()->json([
            'visit' => $visit,
            'status_details' => $visit->status_details,
            'meeting_mode_details' => $visit->meeting_mode_details,
            'display_name' => $visit->display_name,
            'display_contact' => $visit->display_contact,
        ]);
    }

    public function update(UpdateClientVisitRequest $request, ClientVisit $visit)
    {
        $this->service->updateVisit($visit, $request->validated(), auth()->id());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'visit' => $visit->fresh()]);
        }

        return redirect()->back()->with('success', 'Visit details updated successfully.');
    }

    public function destroy(ClientVisit $visit)
    {
        $this->service->deleteVisit($visit);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.visits')->with('success', 'Visit record deleted successfully.');
    }

    public function addUpdate(StoreClientVisitUpdateRequest $request, ClientVisit $visit)
    {
        $update = $this->service->addVisitUpdate($visit, $request->validated(), auth()->id());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'update' => $update->load('user'), 'visit' => $visit->fresh()]);
        }

        return redirect()->back()->with('success', 'Visit update added successfully.');
    }

    public function convert(Request $request, ClientVisit $visit)
    {
        $clientData = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'nullable|string|max:255',
            'color'          => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'emoji'          => 'nullable|string|max:10',
            'contact_person' => 'nullable|string|max:255',
            'contact_email'  => 'nullable|email|max:255',
            'contact_phone'  => 'nullable|string|max:50',
        ]);

        $client = $this->service->convertLeadToClient($visit, $clientData, auth()->id());

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "Lead successfully converted to Client: {$client->name}!");
    }
}
