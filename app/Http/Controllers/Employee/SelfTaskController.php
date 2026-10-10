<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SelfTaskController extends Controller
{
    /**
     * Allow an employee (designer, developer, admin, strategist) to create
     * a task assigned to themselves on the fly.
     */
    public function store(Request $request)
    {
        $defaultTypes = [
            'reel', 'post', 'story', 'video', 'carousel',
            'brochure', 'banner', 'flyer', 'logo', 'product_design', 'branding',
            'website', 'software', 'maintenance', 'server_maintenance',
            'others',
        ];
        $validTypes = \App\Models\DropdownOption::getValues('task_type', $defaultTypes);
        $validPriorities = \App\Models\DropdownOption::getValues('priority', ['normal', 'high', 'urgent']);
        $validStatuses = \App\Models\DropdownOption::getValues('status', ['todo', 'inprogress', 'review', 'completed']);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'client_id'   => ['nullable', 'integer', 'exists:clients,id'],
            'type'        => ['required', 'string', Rule::in($validTypes)],
            'priority'    => ['nullable', 'string', Rule::in($validPriorities)],
            'status'      => ['nullable', 'string', Rule::in($validStatuses)],
            'brief'       => ['nullable', 'string', 'max:3000'],
            'deadline'    => ['nullable', 'date'],
        ]);

        $clientId = $validated['client_id'] ?? null;
        if (!$clientId) {
            $internalClient = Client::firstOrCreate(
                ['name' => 'Internal / Agency Office'],
                ['emoji' => '🏢', 'color' => '#6B7280', 'is_active' => true]
            );
            $clientId = $internalClient->id;
        }

        $userId = Auth::id();
        $type = $validated['type'];
        $deadline = $validated['deadline'] ?? today()->toDateString();
        $status = $validated['status'] ?? 'inprogress';
        $priority = $validated['priority'] ?? 'normal';

        if (Auth::user()?->role === 'designer' && in_array($status, ['review', 'pending_approval', 'completed'], true)) {
            $status = 'inprogress';
        }

        $taskData = [
            'title'       => trim($validated['title']),
            'client_id'   => $clientId,
            'assigned_to' => $userId,
            'created_by'  => $userId,
            'type'        => $type,
            'priority'    => $priority,
            'status'      => $status,
            'brief'       => $validated['brief'] ?? null,
            'deadline'    => $deadline,
        ];

        if (Task::isDevType($type) || in_array($type, ['website', 'software', 'maintenance', 'server_maintenance'])) {
            $taskData['dev_deadline'] = $deadline;
        }

        if (Task::isDesignType($type)) {
            $taskData['design_deadline'] = $deadline;
        }

        if ($status === 'inprogress') {
            $taskData['started_at'] = now();
        }

        $task = Task::create($taskData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully and assigned to you.',
                'task'    => [
                    'id'          => $task->id,
                    'title'       => $task->title,
                    'type'        => $task->type,
                    'status'      => $task->status,
                    'priority'    => $task->priority,
                    'client_name' => $task->client ? $task->client->name : 'Internal',
                    'client_emoji'=> $task->client ? ($task->client->emoji ?? '🏢') : '🏢',
                ],
            ]);
        }

        return back()->with('success', "Task \"{$task->title}\" created and added to your tasks.");
    }
}
