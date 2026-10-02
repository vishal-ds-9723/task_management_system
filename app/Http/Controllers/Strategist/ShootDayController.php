<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Services\Strategist\ShootDayService;
use App\Models\ShootDay;
use Illuminate\Http\Request;

class ShootDayController extends Controller
{
    protected $service;

    public function __construct(ShootDayService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'type'       => 'required|in:video,photo,both',
            'shoot_date' => 'required|date',
            'number_of_days' => 'nullable',
            'start_date' => 'nullable|date_format:H:i',
            'location'   => 'nullable|string|max:255',
            'notes'      => 'nullable|string|max:1000',
            'client_id'  => 'nullable|exists:clients,id',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->service->store($validated->all());
        }

        return back()->with('success', 'Shoot day scheduled successfully!');
    }

    public function update(Request $request, ShootDay $shootDay)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->service->update($shootDay, $validated->all());
        }

        return back()->with('success', 'Shoot day updated!');
    }

    public function destroy(Request $request, ShootDay $shootDay)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $this->service->destroy($shootDay);
        }

        return back()->with('success', 'Shoot day removed.');
    }
}

