<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Strategist\CalendarService;
use App\Services\Strategist\ContentPlanService;
use Illuminate\Http\Request;

class ContentScheduleController extends Controller
{
    /**
     * Display monthly content plans against delivery across all clients.
     */
    public function index(Request $request, ContentPlanService $plans)
    {
        $month = min(max((int) $request->input('month', now()->month), 1), 12);
        $year = min(max((int) $request->input('year', now()->year), 2020), 2100);

        return view('strategist.content-schedules', $plans->build($month, $year) + [
            'month'      => $month,
            'year'       => $year,
            'planTypes'  => ContentPlanService::PLAN_TYPES,
            'statuses'   => ContentPlanService::STATUSES,
            'itemStates' => CalendarService::STATES,
        ]);
    }
}
