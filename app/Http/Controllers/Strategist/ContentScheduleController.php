<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Services\Strategist\ContentPlanService;
use Illuminate\Http\Request;

/**
 * Read-only view of each client's monthly content plan against delivery.
 * Plans themselves are set by admins on the client page.
 */
class ContentScheduleController extends Controller
{
    public function index(Request $request, ContentPlanService $plans)
    {
        $month = min(max((int) $request->input('month', now()->month), 1), 12);
        $year = min(max((int) $request->input('year', now()->year), 2020), 2100);

        return view('strategist.content-schedules', $plans->build($month, $year) + [
            'month' => $month,
            'year' => $year,
            'planTypes' => ContentPlanService::PLAN_TYPES,
            'statuses' => ContentPlanService::STATUSES,
            // Per-piece states, shared with the content calendar
            'itemStates' => \App\Services\Strategist\CalendarService::STATES,
        ]);
    }
}
