<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Services\Strategist\ActionItemService;
use App\Models\ActionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActionItemController extends Controller
{
    protected $service;

    public function __construct(ActionItemService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getIndexData($request);

        return view('strategist.action-items', $data);
    }

    public function store(Request $request)
    {
        $data = $this->service->store($request);

        return $data;
    }

    public function update(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id() || $actionItem->created_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $this->service->update($request, $actionItem);

        return $data;
    }

    public function markDone(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $data = $this->service->markDone($request, $actionItem);

        return $data;
    }

    public function startProgress(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $data = $this->service->startProgress($request, $actionItem);

        return $data;
    }

    public function reopen(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $data = $this->service->reopen($request, $actionItem);

        return $data;
    }

    public function addNote(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $data = $this->service->addNote($request, $actionItem);

        return $data;
    }

    public function snooze(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $data = $this->service->snooze($request, $actionItem);

        return $data;
    }

    public function setReminder(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $data = $this->service->setReminder($request, $actionItem);

        return $data;
    }

    public function clearReminder(Request $request, ActionItem $actionItem)
    {
        if ($actionItem->assigned_to !== Auth::id()) {
            abort(403);
        }

        $data = $this->service->clearReminder($request, $actionItem);

        return $data;
    }

    public function getActiveReminders(Request $request)
    {
        $data = $this->service->getActiveReminders($request);

        return $data;
    }
}

