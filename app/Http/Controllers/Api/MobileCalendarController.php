<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\FestivalSelection;
use App\Models\ShootDay;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobileCalendarController extends Controller
{
    /**
     * Get aggregated calendar events (Tasks, Festivals, Shoot Days).
     */
    public function events(Request $request)
    {
        $user = $request->user();
        $start = $request->has('start') ? Carbon::parse($request->start)->startOfMonth() : Carbon::now()->startOfMonth();
        $end = $request->has('end') ? Carbon::parse($request->end)->endOfMonth() : Carbon::now()->endOfMonth();

        // 1. Task Deadlines & Post dates
        $tasksQuery = Task::with(['client', 'assignee'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('deadline', [$start, $end])
                  ->orWhereBetween('post_date', [$start, $end]);
            });

        if ($user->isClient()) {
            $tasksQuery->where('client_id', $user->client_id);
        } elseif ($user->isDesigner() || $user->isDeveloper()) {
            $tasksQuery->where('assigned_to', $user->id);
        }

        $tasks = $tasksQuery->get()->map(function ($task) {
            $date = $task->post_date ? $task->post_date->toDateString() : ($task->deadline ? $task->deadline->toDateString() : null);
            return [
                'id' => 'task_' . $task->id,
                'task_id' => $task->id,
                'type' => 'task',
                'title' => $task->title ?: ($task->client ? $task->client->name . ' - ' . ucfirst($task->type) : 'Task #' . $task->id),
                'date' => $date,
                'status' => $task->status,
                'priority' => $task->priority,
                'client_name' => $task->client ? $task->client->name : null,
                'assignee_name' => $task->assignee ? $task->assignee->name : null,
                'is_urgent' => (bool)$task->is_urgent_task,
            ];
        });

        // 2. Shoot days
        $shootDays = [];
        if (!$user->isClient()) {
            $shootDays = ShootDay::with('client')
                ->whereBetween('shoot_date', [$start, $end])
                ->get()
                ->map(function ($shoot) {
                    return [
                        'id' => 'shoot_' . $shoot->id,
                        'shoot_id' => $shoot->id,
                        'type' => 'shoot_day',
                        'title' => '📸 ' . $shoot->title,
                        'date' => $shoot->shoot_date->toDateString(),
                        'client_name' => $shoot->client ? $shoot->client->name : null,
                        'location' => $shoot->location,
                        'notes' => $shoot->notes,
                    ];
                });
        }

        // 3. Festivals
        $festivals = Festival::whereBetween('date', [$start, $end])
            ->get()
            ->map(function ($festival) {
                return [
                    'id' => 'festival_' . $festival->id,
                    'festival_id' => $festival->id,
                    'type' => 'festival',
                    'title' => '🎉 ' . $festival->name,
                    'date' => $festival->date->toDateString(),
                    'description' => $festival->description,
                ];
            });

        return response()->json([
            'success' => true,
            'events' => $tasks->concat($shootDays)->concat($festivals)->values(),
        ]);
    }

    /**
     * Get shoot days list.
     */
    public function shootDays(Request $request)
    {
        $shootDays = ShootDay::with(['client'])
            ->orderBy('shoot_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'shoot_days' => $shootDays,
        ]);
    }

    /**
     * Create shoot day.
     */
    public function storeShootDay(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,photo,both',
            'shoot_date' => 'required|date',
            'number_of_days' => 'nullable',
            'start_date' => 'nullable|date_format:H:i',
            'client_id' => 'nullable|exists:clients,id',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $shoot = ShootDay::create([
            'title' => $request->title,
            'type' => $request->type,
            'shoot_date' => $request->shoot_date,
            'number_of_days' => $request->number_of_days ?: '1',
            'start_date' => $request->start_date,
            'client_id' => $request->client_id,
            'location' => $request->location,
            'notes' => $request->notes,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shoot day planned',
            'shoot_day' => $shoot->fresh(['client']),
        ], 201);
    }

    /**
     * Update shoot day.
     */
    public function updateShootDay(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $shoot = ShootDay::findOrFail($id);
        $shoot->update($request->only(['title', 'type', 'shoot_date', 'number_of_days', 'start_date', 'client_id', 'location', 'notes', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Shoot day updated',
            'shoot_day' => $shoot->fresh(['client']),
        ]);
    }

    /**
     * Delete shoot day.
     */
    public function deleteShootDay(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $shoot = ShootDay::findOrFail($id);
        $shoot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shoot day deleted',
        ]);
    }

    /**
     * Get festival selections summary for Admin.
     */
    public function festivalSelections(Request $request)
    {
        $selections = FestivalSelection::with(['client', 'festival'])
            ->latest()
            ->get()
            ->map(function ($s) {
                $data = $s->toArray();
                $data['status'] = 'opt_in'; // presence of a row = opted in; there is no opt-out state to persist
                return $data;
            });

        return response()->json([
            'success' => true,
            'selections' => $selections,
        ]);
    }

    /**
     * Get festivals list.
     */
    public function festivals(Request $request)
    {
        $user = $request->user();
        $festivals = Festival::orderBy('date', 'asc')->get();

        $selectedIds = [];
        if ($user->isClient() && $user->client_id) {
            $selectedIds = FestivalSelection::where('client_id', $user->client_id)
                ->pluck('festival_id')
                ->toArray();
        }

        $formatted = $festivals->map(function ($f) use ($selectedIds) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'date' => $f->date ? $f->date->toDateString() : null,
                'description' => $f->description,
                'is_active' => (bool)$f->is_active,
                'selection_status' => in_array($f->id, $selectedIds) ? 'opt_in' : 'pending',
            ];
        });

        return response()->json([
            'success' => true,
            'festivals' => $formatted,
        ]);
    }

    /**
     * Opt-in or Opt-out of festival greetings.
     */
    public function selectFestival(Request $request, $festivalId)
    {
        $user = $request->user();
        if (!$user->isClient() || !$user->client_id) {
            return response()->json(['success' => false, 'message' => 'Only clients can select festivals'], 403);
        }

        $status = $request->input('status', 'opt_in');

        if ($status === 'opt_out') {
            FestivalSelection::where('client_id', $user->client_id)
                ->where('festival_id', $festivalId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Festival deselected',
                'selection' => null,
            ]);
        }

        $selection = FestivalSelection::updateOrCreate(
            ['client_id' => $user->client_id, 'festival_id' => $festivalId],
            [
                'user_id' => $user->id,
                'content_types' => $request->input('content_types'),
                'notes' => $request->input('notes'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Festival preference saved',
            'selection' => $selection,
        ]);
    }
}
