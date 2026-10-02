<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreActionItemRequest;
use App\Http\Requests\Admin\UpdateActionItemRequest;
use App\Models\ActionItem;
use App\Services\ActionItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionItemController extends Controller
{
    protected $service;

    public function __construct(ActionItemService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getDashboardData($request);

        if (isset($data['stats']) && is_array($data['stats'])) {
            $data = array_merge($data, $data['stats']);
        }

        return view('admin.action-items', $data);
    }

    public function store(StoreActionItemRequest $request)
    {
        $this->service->createActionItem($request->validated());

        return back()->with('success', 'Action item created!');
    }

    public function update(UpdateActionItemRequest $request, ActionItem $actionItem)
    {
        if ($actionItem->created_by !== Auth::id()) {
            abort(403);
        }

        $this->service->updateActionItem($actionItem, $request->validated());

        return back()->with('success', 'Action item updated!');
    }

    public function destroy(ActionItem $actionItem)
    {
        $this->service->deleteActionItem($actionItem);

        return back()->with('success', 'Action item deleted.');
    }
}

