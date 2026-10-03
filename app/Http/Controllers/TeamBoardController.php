<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\Strategist\TeamBoardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Team board: who is working on what, and what each person picks up next.
 * Served to strategists (strategist.team-board) and admins (admin.team-board).
 */
class TeamBoardController extends Controller
{
    public function index(Request $request, TeamBoardService $service)
    {
        $clientId = $request->integer('client') ?: null;
        $mine = $request->boolean('mine');

        return view('team-board.index', $service->build($clientId, $mine, Auth::id()) + [
            'boardRoute' => $request->route()->getName(),
            'clients' => Client::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'clientId' => $clientId,
            'mine' => $mine,
        ]);
    }
}
