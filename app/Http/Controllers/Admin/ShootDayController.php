<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ShootDay;
use App\Services\Strategist\ShootDayService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShootDayController extends Controller
{
    protected ShootDayService $service;

    public function __construct(ShootDayService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $today = Carbon::today();

        $query = ShootDay::with(['client', 'creator']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $shootDays = $query->orderBy('shoot_date', 'desc')->paginate(25)->withQueryString();

        $shootDaysInProgress = ShootDay::where('status', 'in_progress')->count();
        $shootDaysOverdue = ShootDay::where('status', 'scheduled')->where('shoot_date', '<', $today)->count();
        $shootDaysCompleted = ShootDay::where('status', 'completed')->count();
        $shootDaysScheduled = ShootDay::where('status', 'scheduled')->count();

        $clients = Client::orderBy('name')->get();

        return view('strategist.all-shoot-days', compact(
            'shootDays',
            'shootDaysInProgress',
            'shootDaysOverdue',
            'shootDaysCompleted',
            'shootDaysScheduled',
            'clients'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'type'           => 'required|in:video,photo,both',
            'shoot_date'     => 'required|date',
            'number_of_days' => 'nullable',
            'start_date'     => 'nullable|date_format:H:i',
            'location'       => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:1000',
            'client_id'      => 'nullable|exists:clients,id',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->service->store($validated);
        }

        $this->service->store($validated);
        return back()->with('success', 'Photoshoot scheduled successfully!');
    }

    public function update(Request $request, ShootDay $shootDay)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->service->update($shootDay, $validated);
        }

        $this->service->update($shootDay, $validated);
        return back()->with('success', 'Photoshoot updated!');
    }

    public function destroy(Request $request, ShootDay $shootDay)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $this->service->destroy($shootDay);
        }

        $this->service->destroy($shootDay);
        return back()->with('success', 'Photoshoot removed.');
    }
}
