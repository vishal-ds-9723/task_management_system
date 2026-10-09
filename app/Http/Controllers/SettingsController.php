<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the settings page with dropdown option management.
     */
    public function index(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access. Settings are restricted to administrators.');
        }

        $category = $request->query('tab', 'urgent_requested_by');

        $optionsByCategory = DropdownOption::ordered()->get()->groupBy('category');

        return view('settings.index', [
            'activeTab'          => $category,
            'optionsByCategory'  => $optionsByCategory,
            'requesters'         => $optionsByCategory->get('urgent_requested_by', collect()),
            'taskTypesGrouped'   => $optionsByCategory->get('task_type', collect())->groupBy(fn($i) => $i->group_name ?: 'General'),
            'platforms'          => $optionsByCategory->get('platform', collect()),
            'priorities'         => $optionsByCategory->get('priority', collect()),
            'statuses'           => $optionsByCategory->get('status', collect()),
        ]);
    }
}
