<?php

namespace App\Services\Admin;

use App\Models\Task;
use App\Models\Client;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CalendarEventService
{
    public function getEvents(Request $request)
    {
        $clientId = $request->query('client_id');
        $userRole = Auth::user()->role;
        $start = $request->query('start');
        $end = $request->query('end');

        $query = Task::with(['client', 'assignee', 'creator'])
            ->withCount('comments')
            ->where(function ($q) {
                $q->whereNotNull('deadline')
                    ->orWhereNotNull('post_date');
            });

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($userRole === 'designer') {
            $query->where('assigned_to', Auth::id());
        } elseif ($userRole === 'strategist') {
            $query->whereHas('creator', fn($q) => $q->where('role', 'strategist'));
        }

        if ($start && $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('deadline', [$start, $end])
                    ->orWhereBetween('post_date', [$start, $end]);
            });
        }

        $tasks = $query->get();

        $statusColors = [
            'todo' => '#FFA500',
            'inprogress' => '#3B82F6',
            'review' => '#8B5CF6',
            'pending_approval' => '#A855F7',
            'completed' => '#10B981',
        ];

        $events = $tasks->flatMap(function ($task) use ($userRole, $statusColors) {
            $viewUrl = match ($userRole) {
                'admin' => route('admin.tasks.show', $task),
                'designer' => route('designer.tasks.show', $task),
                default => route('strategist.tracking', ['task' => $task->id]),
            };

            $baseEvent = [
                'title' => $task->title,
                'extendedProps' => [
                    'client' => $task->client?->name ?? 'No Client',
                    'clientColor' => $task->client?->color ?? '#6366F1',
                    'clientLogo' => $task->client?->logo,
                    'clientEmoji' => $task->client?->emoji ?? '🏢',
                    'clientCategory' => $task->client?->category ?? '',
                    'status' => $task->status,
                    'type' => $task->type,
                    'platform' => $task->platform,
                    'assignee' => $task->assignee?->name,
                    'creator' => $task->creator?->name,
                    'description' => $task->caption,
                    'priority' => $task->priority,
                    'taskId' => $task->id,
                    'viewUrl' => $viewUrl,
                    'deadline' => $task->deadline ? Carbon::parse($task->deadline)->toDateString() : null,
                    'postDate' => $task->post_date ? Carbon::parse($task->post_date)->toDateString() : null,
                    'referenceLinks' => $task->reference_links,
                    'commentsCount' => $task->comments_count,
                    'createdAt' => $task->created_at ? $task->created_at->format('M d, Y') : '',
                ],
                'backgroundColor' => $statusColors[$task->status] ?? '#6B7280',
                'borderColor' => $statusColors[$task->status] ?? '#6B7280',
                'textColor' => '#FFFFFF',
                'display' => 'block',
            ];

            $taskEvents = [];

            if ($task->deadline) {
                $taskEvents[] = array_merge($baseEvent, [
                    'id' => 'deadline-'.$task->id,
                    'title' => $task->title,
                    'start' => Carbon::parse($task->deadline)->toDateString(),
                    'borderColor' => '#EF4444',
                    'extendedProps' => array_merge($baseEvent['extendedProps'], ['dateType' => 'deadline']),
                ]);
            }

            if ($task->post_date) {
                $taskEvents[] = array_merge($baseEvent, [
                    'id' => 'post-'.$task->id,
                    'title' => $task->title,
                    'start' => Carbon::parse($task->post_date)->toDateString(),
                    'borderColor' => '#10B981',
                    'extendedProps' => array_merge($baseEvent['extendedProps'], ['dateType' => 'post_date']),
                ]);
            }

            return $taskEvents;
        });

        return response()->json($events);
    }

    public function getClients()
    {
        $clients = Client::where('is_active', true)
            ->select('id', 'name', 'emoji', 'color')
            ->orderBy('name')
            ->get();

        return response()->json($clients);
    }

    public function askForReview(Task $task)
    {
        $user = Auth::user();

        if ($task->created_by !== $user->id && $task->assigned_to !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent moving completed/published tasks backwards into review
        if (!in_array($task->status, ['todo', 'inprogress'])) {
            return response()->json([
                'message' => 'Only tasks that are To Do or In Progress can be sent for review.',
            ], 422);
        }

        $task->update(['status' => 'review']);

        $notifyUserId = ($user->id === $task->assigned_to) ? $task->created_by : $task->assigned_to;

        if ($notifyUserId) {
            Notification::create([
                'user_id'  => $notifyUserId,
                'task_id'  => $task->id,
                'icon'     => '📋',
                'title'    => 'Review Requested',
                'subtitle' => $user->name . ' requested review on "' . $task->title . '"',
                'link'     => '/strategist/tracking?task=' . $task->id,
            ]);
        }

        return response()->json(['message' => 'Review requested successfully!']);
    }

    public function reschedule(Request $request, Task $task)
    {
        $user = Auth::user();

        if ($task->created_by !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'date_type' => ['required', 'in:deadline,post_date'],
            'new_date'  => ['required', 'date', 'after_or_equal:today'],
        ]);

        $field   = $validated['date_type'] === 'post_date' ? 'post_date' : 'deadline';
        $updates = [$field => $validated['new_date']];

        // Use shared helper — single source of truth for design_deadline logic
        if ($field === 'deadline') {
            $designDeadline = Task::calculateDesignDeadline(
                $validated['new_date'],
                $task->getRawOriginal('post_date')
            );
        } else {
            $designDeadline = Task::calculateDesignDeadline(
                $task->getRawOriginal('deadline'),
                $validated['new_date']
            );
        }

        if ($designDeadline) {
            $updates['design_deadline'] = $designDeadline->toDateString();
        }

        $task->update($updates);

        // Notify everyone affected — calendar drag-drop is otherwise silent
        $fieldLabel       = $field === 'post_date' ? 'Post date' : 'Deadline';
        $newDateFormatted = Carbon::parse($validated['new_date'])->format('d M Y');

        collect([$task->assigned_to, $task->created_by])
            ->filter()
            ->unique()
            ->reject(fn ($id) => $id === $user->id)
            ->each(fn ($uid) => Notification::create([
                'user_id'  => $uid,
                'task_id'  => $task->id,
                'icon'     => '📅',
                'title'    => $fieldLabel . ' updated',
                'subtitle' => '"' . $task->title . '" ' . $fieldLabel . ' moved to ' . $newDateFormatted . ' by ' . $user->name,
                'link'     => '/admin/tasks/' . $task->id,
            ]));

        return response()->json(['message' => $fieldLabel . ' updated to ' . $newDateFormatted . '!']);
    }
}

