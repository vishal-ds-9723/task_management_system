<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\Strategist\UpdateTaskRequest;
use App\Services\Strategist\TaskService;
use App\Models\Task;
use App\Models\Client;
use App\Models\ClientSocialMediaLink;
use App\Models\User;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Media;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    public function create()
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $users = User::where('role', '!=', 'client')
            ->orderBy('name')
            ->get();
        return view('strategist.create-task', compact('clients', 'users'));
    }

    public function store(StoreTaskRequest $request)
    {
        $taskService = app(TaskService::class);
        $validated = $request->validated();
        $additionalTitles = collect($validated['additional_titles'] ?? [])
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->values();
        unset($validated['additional_titles']);

        $selectedSocialLinkIds = collect((array) $request->input('selected_social_media_link_ids', []));

        foreach ((array) ($validated['platform'] ?? []) as $platform) {
            $dynamicId = $request->input('social_media_link_id_' . $platform);
            if (!empty($dynamicId)) {
                $selectedSocialLinkIds->push($dynamicId);
            }
        }

        if (!empty($validated['client_social_media_link_id'])) {
            $selectedSocialLinkIds->push($validated['client_social_media_link_id']);
        }

        $selectedSocialLinkIds = $selectedSocialLinkIds
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedSocialLinkIds->isNotEmpty()) {
            $validLinkIds = ClientSocialMediaLink::query()
                ->where('client_id', '=', (int) $validated['client_id'], 'and')
                ->whereIn('id', $selectedSocialLinkIds->all())
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();

            $validated['selected_social_media_link_ids'] = $validLinkIds->isNotEmpty()
                ? $validLinkIds->all()
                : null;
            $validated['client_social_media_link_id'] = $validLinkIds->first();
        }

        $tasks = DB::transaction(function () use ($taskService, $validated, $additionalTitles) {
            $tasks = collect([$taskService->createTask($validated)]);

            foreach ($additionalTitles as $title) {
                $tasks->push($taskService->createTask([
                    ...$validated,
                    'title' => $title,
                ]));
            }

            return $tasks;
        });
        $task = $tasks->first();
        $createdCount = $tasks->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $createdCount === 1
                    ? 'Task created successfully!'
                    : "{$createdCount} tasks created successfully!",
                'task_id' => $task->id,
                'task_ids' => $tasks->pluck('id')->all(),
                'created_count' => $createdCount,
            ]);
        }

        return redirect()
            ->route('strategist.tracking', ['task' => $task->id])
            ->with('success', $createdCount === 1
                ? 'Task created successfully!'
                : "{$createdCount} tasks created successfully!");
    }

    public function show(Task $task)
    {
        $task->loadMissing('creator');
        $task->load(['client.socialMediaLinks', 'assignee', 'comments.user', 'socialMediaLink', 'media']);
        return view('strategist.task-detail', compact('task'));
    }

    public function edit(Task $task)
    {
        $task->load([
            'comments' => fn ($q) => $q->select('id', 'task_id', 'user_id', 'body', 'created_at')
                ->orderBy('created_at'),
            'comments.user:id,name,avatar_color',
        ]);

        $clients = Client::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'logo', 'emoji']);

        $users = User::whereIn('role', ['designer', 'strategist', 'developer', 'editor', 'content_writer'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('strategist.edit-task', compact('task', 'clients', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $isDevProject = in_array($request->input('type'), ['website', 'software', 'maintenance']);

        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'client_id'      => ['required', 'exists:clients,id'],
            'assigned_to'    => ['nullable', 'exists:users,id'],
            'type'           => ['required', 'in:reel,post,story,video,carousel,brochure,banner,flyer,website,software,maintenance,others'],
            'platform'       => ['nullable', 'array'],
            'platform.*'     => ['string', 'max:50'],
            'priority'       => ['required', 'in:normal,high,urgent'],
            'status'         => ['required', 'in:todo,inprogress,review,pending_approval,completed,published,on_hold'],
            'brief'          => ['nullable', 'string'],
            'caption'        => ['nullable', 'string', 'max:5000'],
            'hashtags'       => ['nullable', 'string', 'max:1000'],
            'reference_links'   => ['nullable', 'array', 'max:15'],
            'reference_links.*' => ['nullable', 'url', 'max:2048'],
            'deadline'       => ['nullable', 'date'],
            'post_date'      => ['nullable', 'date'],
            'client_social_media_link_id' => ['nullable', 'exists:client_social_media_links,id'],
            'selected_social_media_link_ids' => ['nullable', 'array'],
            'selected_social_media_link_ids.*' => ['integer', 'exists:client_social_media_links,id'],
            'logo_received'      => ['nullable', 'boolean'],
            'images_received'    => ['nullable', 'boolean'],
            'content_received'   => ['nullable', 'boolean'],
            'domain_purchased'   => ['nullable', 'boolean'],
            'hosting_access'     => ['nullable', 'boolean'],
            'has_wireframes'     => ['nullable', 'boolean'],
            'tech_stack'         => ['nullable', 'string', 'max:255'],
            'project_notes'      => ['nullable', 'string', 'max:2000'],
            'project_start_date' => ['nullable', 'date'],
            'launch_date'        => ['nullable', 'date'],
            'dev_deadline'       => ['nullable', 'date'],
            'preferred_tech'     => ['nullable', 'string', 'max:100'],
            'modules'            => ['nullable', 'string', 'max:1000'],
            'business_type'      => ['nullable', 'string', 'max:1000'],
        ]);

        $oldAssignee = $task->assigned_to;
        $oldPostDate = $task->post_date ? Carbon::parse($task->post_date)->format('Y-m-d') : null;
        $oldDeadline = $task->deadline ? Carbon::parse($task->deadline)->format('Y-m-d') : null;
        $oldDevDeadline = $task->dev_deadline ? Carbon::parse($task->dev_deadline)->format('Y-m-d') : null;
        $oldLaunchDate = $task->launch_date ? Carbon::parse($task->launch_date)->format('Y-m-d') : null;

        $selectedSocialLinkIds = collect($validated['selected_social_media_link_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $validSocialLinkIds = ClientSocialMediaLink::query()
            ->where('client_id', (int) $validated['client_id'])
            ->whereIn('id', $selectedSocialLinkIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
        $validated['selected_social_media_link_ids'] = $validSocialLinkIds->isEmpty() ? null : $validSocialLinkIds->all();
        $validated['client_social_media_link_id'] = $validSocialLinkIds->first();

        $task->update($validated);

        // Detect if any timeline dates changed
        $newPostDate = $task->post_date ? Carbon::parse($task->post_date)->format('Y-m-d') : null;
        $newDeadline = $task->deadline ? Carbon::parse($task->deadline)->format('Y-m-d') : null;
        $newDevDeadline = $task->dev_deadline ? Carbon::parse($task->dev_deadline)->format('Y-m-d') : null;
        $newLaunchDate = $task->launch_date ? Carbon::parse($task->launch_date)->format('Y-m-d') : null;

        $dateChanged = ($oldPostDate !== $newPostDate)
            || ($oldDeadline !== $newDeadline)
            || ($oldDevDeadline !== $newDevDeadline)
            || ($oldLaunchDate !== $newLaunchDate);

        if ($dateChanged) {
            $changedParts = [];
            if ($oldPostDate !== $newPostDate) {
                $changedParts[] = 'Post Date: ' . ($oldPostDate ? Carbon::parse($oldPostDate)->format('d M Y') . ' → ' : '') . ($newPostDate ? Carbon::parse($newPostDate)->format('d M Y') : 'None');
            }
            if ($oldDeadline !== $newDeadline) {
                $changedParts[] = 'Deadline: ' . ($oldDeadline ? Carbon::parse($oldDeadline)->format('d M Y') . ' → ' : '') . ($newDeadline ? Carbon::parse($newDeadline)->format('d M Y') : 'None');
            }
            if ($oldDevDeadline !== $newDevDeadline) {
                $changedParts[] = 'Dev Deadline: ' . ($oldDevDeadline ? Carbon::parse($oldDevDeadline)->format('d M Y') . ' → ' : '') . ($newDevDeadline ? Carbon::parse($newDevDeadline)->format('d M Y') : 'None');
            }
            if ($oldLaunchDate !== $newLaunchDate) {
                $changedParts[] = 'Launch Date: ' . ($oldLaunchDate ? Carbon::parse($oldLaunchDate)->format('d M Y') . ' → ' : '') . ($newLaunchDate ? Carbon::parse($newLaunchDate)->format('d M Y') : 'None');
            }

            $changerName = Auth::user()->name ?? 'Strategist';
            $dateSubtitle = $changerName . ' changed ' . implode(', ', $changedParts) . ' for "' . $task->title . '"';

            // Notify all admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id'  => $admin->id,
                    'task_id'  => $task->id,
                    'icon'     => '📅',
                    'title'    => 'Task date changed by strategist',
                    'subtitle' => $dateSubtitle,
                    'link'     => '/admin/tasks/' . $task->id,
                ]);
            }

            // Also notify the assigned designer/developer if one is assigned
            if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
                $assignedUser = User::find($task->assigned_to);
                $assigneeLink = ($assignedUser && $assignedUser->role === 'developer') ? '/developer/tasks' : '/designer/tasks';
                Notification::create([
                    'user_id'  => $task->assigned_to,
                    'task_id'  => $task->id,
                    'icon'     => '📅',
                    'title'    => 'Task schedule updated',
                    'subtitle' => $dateSubtitle,
                    'link'     => $assigneeLink,
                ]);
            }

            // Add an activity note to the task comments
            Comment::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'body'    => '📅 Date updated by ' . $changerName . ': ' . implode(', ', $changedParts),
            ]);

            // Audit log entry
            AuditLog::create([
                'user_id'    => Auth::id(),
                'task_id'    => $task->id,
                'action'     => 'date_changed',
                'old_values' => ['post_date' => $oldPostDate, 'deadline' => $oldDeadline, 'dev_deadline' => $oldDevDeadline, 'launch_date' => $oldLaunchDate],
                'new_values' => ['post_date' => $newPostDate, 'deadline' => $newDeadline, 'dev_deadline' => $newDevDeadline, 'launch_date' => $newLaunchDate],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        if (!empty($validated['assigned_to']) && $validated['assigned_to'] != $oldAssignee) {
            $assignedUser = User::find($validated['assigned_to']);
            $link = '/designer/tasks';
            if ($assignedUser && $assignedUser->role === 'developer') {
                $link = '/developer/tasks';
            }
            Notification::create([
                'user_id' => $validated['assigned_to'],
                'task_id' => $task->id,
                'icon'    => '🔄',
                'title'   => 'Task reassigned to you',
                'subtitle' => $task->title,
                'link'    => $link,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully!'
            ]);
        }

        return redirect()
            ->route('strategist.tracking')
            ->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        $taskTitle = $task->title ?? 'Untitled Task';

        DB::transaction(function () use ($task, $taskTitle) {
            AuditLog::create([
                'user_id'    => Auth::id(),
                'task_id'    => $task->id,
                'action'     => 'deleted',
                'old_values' => ['title' => $taskTitle, 'created_by' => $task->created_by, 'client_id' => $task->client_id],
                'new_values' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $task->comments()->delete();
            Notification::where('task_id', $task->id)->delete();
            $task->media()->delete();
            $task->delete();
        });

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Task '{$taskTitle}' deleted successfully!"
            ]);
        }

        return redirect()
            ->route('strategist.tracking')
            ->with('success', "Task '{$taskTitle}' deleted successfully!");
    }

    public function reassign(Request $request, Task $task)
    {
        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $oldAssignee = $task->assigned_to;
        $task->update(['assigned_to' => $validated['assigned_to']]);

        if ($validated['assigned_to'] != $oldAssignee) {
            Notification::create([
                'user_id' => $validated['assigned_to'],
                'task_id' => $task->id,
                'icon'    => '🔄',
                'title'   => 'Task reassigned to you',
                'subtitle' => $task->title,
                'link'    => '/designer/tasks',
            ]);
        }

        return back()->with('success', 'Task reassigned successfully!');
    }

    public function followUp(Task $task)
    {
        if (!$task->assigned_to) {
            return back()->with('error', 'Cannot follow up on unassigned task.');
        }

        Notification::create([
            'user_id' => $task->assigned_to,
            'task_id' => $task->id,
            'icon'    => '⏰',
            'title'   => 'Follow-up reminder',
            'subtitle' => Auth::user()->name . ' is following up on "' . $task->title . '"',
            'link'    => '/designer/tasks',
        ]);

        return back()->with('success', 'Follow-up sent to ' . $task->assignee->name . '!');
    }

    public function addComment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body'    => $validated['body'],
        ]);

        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'icon'    => '💬',
                'title'   => 'New comment on task',
                'subtitle' => Auth::user()->name . ' commented on "' . $task->title . '"',
                'link'    => '/designer/tasks/' . $task->id,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Comment added!',
                'user_name'    => Auth::user()->name,
                'user_initial' => strtoupper(substr(Auth::user()->name, 0, 1)),
                'user_color'   => Auth::user()->avatar_color ?? 'var(--primary)',
                'body'         => $validated['body'],
                'time'         => 'just now',
            ]);
        }

        return back()->with('success', 'Comment added!');
    }

    public function updateCaptionHashtags(Request $request, Task $task)
    {
        $validated = $request->validate([
            'caption'  => ['nullable', 'string', 'max:5000'],
            'hashtags' => ['nullable', 'string', 'max:1000'],
        ]);

        $updates = [];
        if (array_key_exists('caption', $validated)) {
            $updates['caption'] = $validated['caption'];
        }
        if (array_key_exists('hashtags', $validated)) {
            $updates['hashtags'] = $validated['hashtags'];
        }

        if (!empty($updates)) {
            $task->update($updates);
        }

        if ($task->assigned_to && $task->assigned_to !== Auth::id()) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'icon'    => '📕',
                'title'   => 'Caption/hashtags updated',
                'subtitle' => Auth::user()->name . ' updated "' . $task->title . '"',
                'link'    => '/designer/tasks/' . $task->id,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Caption & hashtags updated!',
                'caption'  => $task->caption,
                'hashtags' => $task->hashtags,
            ]);
        }

        return back()->with('success', 'Caption & hashtags updated!');
    }

    public function requestDateChange(Request $request, Task $task)
    {
        $validated = $request->validate([
            'requested_date' => ['required', 'date'],
            'reason'         => ['required', 'string', 'max:1000'],
        ]);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id'  => $admin->id,
                'task_id'  => $task->id,
                'icon'     => '📅',
                'title'    => 'Post date change requested',
                'subtitle' => Auth::user()->name . ' requests "' . $task->title . '" post date → ' . $validated['requested_date'],
                'link'     => '/admin/tasks/' . $task->id,
            ]);
        }

        Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body'    => '📅 Requested post date change to ' . $validated['requested_date'] . '. Reason: ' . $validated['reason'],
        ]);

        return back()->with('success', 'Post date change request sent to admin!');
    }

    public function updateHashtags(Request $request, Task $task)
    {
        $validated = $request->validate([
            'hashtags' => ['nullable', 'string', 'max:1000'],
        ]);

        $task->update(['hashtags' => $validated['hashtags'] ?? null]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Hashtags saved!', 'hashtags' => $task->hashtags]);
        }

        return back()->with('success', 'Hashtags updated!');
    }

    public function approve(Request $request, Task $task)
    {
        $isDeveloperTask = in_array($task->type, ['software', 'website']);

        if (!$isDeveloperTask && (empty($task->caption) || empty($task->hashtags))) {
            $errorMsg = 'Please add caption and hashtags before approving.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return back()->with('error', $errorMsg);
        }

        $task->update(['status' => 'pending_approval']);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'task_id' => $task->id,
                'icon'    => '👀',
                'title'   => 'Task ready for approval',
                'subtitle' => 'Strategist approved "' . $task->title . '" - review media and approve to complete',
                'link'    => route('admin.approvals.show', $task),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task sent to admins for final approval!',
                'task'    => $task,
            ]);
        }

        return back()->with('success', 'Task sent to admins for final approval!');
    }

    public function requestRevision(Request $request, Task $task)
    {
        $validated = $request->validate([
            'revision_note' => ['required', 'string', 'max:2000'],
        ]);

        $oldStatus = $task->status;
        $task->update([
            'status'         => 'todo',
            'revision_count' => $task->revision_count + 1,
        ]);

        Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body'    => '🔄 Revision requested: ' . $validated['revision_note'],
        ]);

        \App\Models\AuditLog::create([
            'user_id'    => Auth::id(),
            'task_id'    => $task->id,
            'action'     => 'revision_requested',
            'old_values' => ['status' => $oldStatus, 'revision_count' => $task->revision_count - 1],
            'new_values' => ['status' => 'inprogress', 'revision_count' => $task->revision_count, 'note' => $validated['revision_note']],
        ]);

        if ($task->assigned_to) {
            $assignedUser = \App\Models\User::find($task->assigned_to);
            $link = '/designer/tasks/' . $task->id;
            if ($assignedUser && $assignedUser->role === 'developer') {
                $link = '/developer/tasks';
            }

            Notification::create([
                'user_id' => $task->assigned_to,
                'task_id' => $task->id,
                'icon'    => '🔄',
                'title'   => 'Revision requested',
                'subtitle' => Auth::user()->name . ' requested changes on "' . $task->title . '"',
                'link'    => $link,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Revision requested — task moved back to To Do.',
                'task'    => $task,
            ]);
        }

        return back()->with('success', 'Revision requested — task moved back to To Do.');
    }

    public function putOnHold(Request $request, Task $task)
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        app(TaskService::class)->putOnHold($task, $validated);
        return back()->with('success', 'Task put on hold.');
    }

    public function resumeFromHold(Request $request, Task $task)
    {
        app(TaskService::class)->resumeFromHold($task);
        return back()->with('success', 'Task resumed from hold. Deadlines shifted automatically.');
    }

    public function createMaintenanceTask(Request $request, Task $task)
    {
        $validated = $request->validate([
            'brief' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
            'dev_deadline' => 'nullable|date',
            'priority' => 'nullable|in:normal,high,urgent'
        ]);
        $newTask = app(TaskService::class)->createMaintenanceTask($task, $validated);
        return redirect()->route('strategist.tasks.show', $newTask)->with('success', 'Maintenance task created.');
    }

    public function uploadMedia(Request $request, Task $task)
    {
        $request->validate([
            'media'   => ['required', 'array'],
            'media.*' => ['required', 'file', 'mimes:jpg,jpeg,jfif,png,gif,webp,mp4,mov', 'max:51200'],
        ]);

        try {
            $files         = $request->file('media');
            $uploadedCount = 0;
            $failedFiles   = [];
            $collection    = 'task-media';

            foreach ($files as $file) {
                try {
                    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                    $filename = time() . '_' . uniqid() . '_' . $safeName;

                    $stored = $file->storeAs($collection, $filename, 'public');
                    if (!$stored) {
                        $failedFiles[] = $file->getClientOriginalName();
                        continue;
                    }

                    $mimeType = $file->getMimeType();
                    $type     = str_contains($mimeType, 'image') ? 'image' : (str_contains($mimeType, 'video') ? 'video' : 'document');

                    Media::create([
                        'model_type'      => Task::class,
                        'model_id'        => $task->id,
                        'collection_name' => $collection,
                        'name'            => $file->getClientOriginalName(),
                        'file_name'       => $filename,
                        'mime_type'       => $mimeType,
                        'disk'            => 'public',
                        'path'            => "{$collection}/{$filename}",
                        'size'            => $file->getSize(),
                        'type'            => $type,
                        'metadata'        => [],
                    ]);

                    $uploadedCount++;
                } catch (\Exception $fileException) {
                    $failedFiles[] = $file->getClientOriginalName();
                }
            }

            if ($uploadedCount === 0) {
                $message = 'All files failed to upload: ' . implode(', ', $failedFiles);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->with('error', $message);
            }

            $successMessage = $uploadedCount . ' file(s) uploaded successfully';
            if (!empty($failedFiles)) {
                $successMessage .= '. Failed: ' . implode(', ', $failedFiles);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'        => true,
                    'message'        => $successMessage,
                    'uploaded_count' => $uploadedCount,
                    'failed_count'   => count($failedFiles),
                ]);
            }

            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            $message = 'Failed to upload: ' . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return back()->with('error', $message);
        }
    }

    public function deleteMedia(Request $request, Task $task)
    {
        $request->validate([
            'media_id' => ['required', 'integer'],
        ]);

        try {
            $media = Media::find($request->input('media_id'));

            if (!$media || $media->model_type !== Task::class || $media->model_id !== $task->id) {
                $message = 'Media not found or does not belong to this task.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 404);
                }
                return back()->with('error', $message);
            }

            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }

            $media->delete();

            $message = 'Media removed successfully.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', $message);
        } catch (\Exception $e) {
            $message = 'Failed to delete media: ' . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return back()->with('error', $message);
        }
    }

    public function tracking(Request $request)
    {
        $strategistId = Auth::id();
        $focusedTaskId = $request->filled('task') ? (int) $request->input('task') : null;
        $scope = $request->input('scope', 'all');

        $query = Task::with([
                'client:id,name,logo,emoji',
                'assignee:id,name,avatar_color',
                'creator:id,name,role',
            ])
            ->withCount('comments');

        if ($scope === 'my') {
            $query->where('created_by', $strategistId);
        } else {
            $query->whereHas('creator', fn($q) => $q->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin']));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'overdue') {
                $query->whereNotIn('status', ['completed', 'published'])
                    ->whereNotNull('deadline')
                    ->where('deadline', '<', now());
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        if ($request->filled('client')) {
            $query->where('client_id', $request->input('client'));
        }

        if ($request->filled('creator_id')) {
            $query->where('created_by', $request->input('creator_id'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereNotNull('deadline')
                ->whereDate('deadline', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereNotNull('deadline')
                ->whereDate('deadline', '<=', $request->input('date_to'));
        }

        if ($focusedTaskId) {
            // Keep the requested task visible on top regardless of selected sort/filter state.
            $query->orderByRaw('CASE WHEN tasks.id = ? THEN 0 ELSE 1 END', [$focusedTaskId]);
        }

        $sort = $request->input('sort', 'deadline_asc');
        if ($sort === 'deadline_asc') {
            $query->orderByRaw('deadline IS NULL, deadline ASC');
        } elseif ($sort === 'deadline_desc') {
            $query->orderByRaw('deadline IS NULL, deadline DESC');
        } elseif ($sort === 'post_date_asc') {
            $query->orderByRaw('post_date IS NULL, post_date ASC');
        } elseif ($sort === 'post_date_desc') {
            $query->orderByRaw('post_date IS NULL, post_date DESC');
        } elseif ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'newest') {
            $query->latest();
        } else {
            $query->orderByRaw('post_date IS NULL, post_date ASC');
        }

        $perPage = in_array((int) $request->input('per_page'), [25, 50, 100]) ? (int) $request->input('per_page') : 25;
        $tasks   = $query->paginate($perPage)->withQueryString();

        $clients     = Client::where('is_active', true)->orderBy('name')->get();
        $designers   = User::whereIn('role', ['designer', 'developer', 'editor', 'content_writer'])->orderBy('name')->get();
        $strategists = User::whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin'])->orderBy('name')->get();

        $allTasks = ($scope === 'my')
            ? Task::where('created_by', $strategistId)
            : Task::whereHas('creator', fn($q) => $q->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin']));

        $totalCount     = $allTasks->count();
        $todoCount      = (clone $allTasks)->where('status', 'todo')->count();
        $inProgressCount = (clone $allTasks)->where('status', 'inprogress')->count();
        $reviewCount    = (clone $allTasks)->where('status', 'review')->count();
        $completedCount = (clone $allTasks)->whereIn('status', ['completed', 'published'])->count();
        $overdueCount   = (clone $allTasks)->whereNotIn('status', ['completed', 'published'])
            ->whereNotNull('deadline')->where('deadline', '<', now())->count();

        return view('strategist.tracking', compact(
            'tasks', 'clients', 'designers', 'strategists', 'scope',
            'totalCount', 'todoCount', 'inProgressCount', 'reviewCount', 'completedCount', 'overdueCount'
        ));
    }

    public function approvals(Request $request)
    {
        $clients = Client::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'logo', 'emoji']);

        $designers = User::where('role', 'designer')
            ->orderBy('name')
            ->get(['id', 'name']);

        $pendingReviewCount = Task::query()
            ->whereHas('creator', fn($q) => $q->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin']))
            ->where('status', 'review')
            ->count();

        return view('strategist.approvals', compact('clients', 'designers', 'pendingReviewCount'));
    }

    public function approvalsData(Request $request)
    {
        $query = $this->buildApprovalsQuery();

        $this->applyApprovalsFilters($query, $request);
        $this->applyApprovalsSort($query, $request->input('sort', 'deadline_asc'));

        return DataTables::eloquent($query)
            ->addColumn('task', fn (Task $task) => $this->formatApprovalTaskCell($task))
            ->editColumn('client', fn (Task $task) => $this->formatApprovalClientCell($task))
            ->editColumn('type', fn (Task $task) => $this->formatApprovalTypeCell($task))
            ->editColumn('assignee', fn (Task $task) => $this->formatApprovalAssigneeCell($task))
            ->editColumn('priority', fn (Task $task) => $this->formatApprovalPriorityCell($task))
            ->editColumn('deadline', fn (Task $task) => $this->formatApprovalDeadlineCell($task))
            ->editColumn('comments_count', fn (Task $task) => '<span class="apv-comment-count">' . (int) $task->comments_count . '</span>')
            ->addColumn('actions', fn (Task $task) => $this->formatApprovalActionsCell($task))
            ->rawColumns(['task', 'client', 'type', 'assignee', 'priority', 'deadline', 'comments_count', 'actions'])
            ->toJson();
    }

    private function buildApprovalsQuery(): Builder
    {
        return Task::query()
            ->select([
                'tasks.id',
                'tasks.title',
                'tasks.client_id',
                'tasks.assigned_to',
                'tasks.type',
                'tasks.priority',
                'tasks.platform',
                'tasks.deadline',
                'tasks.status',
                'tasks.caption',
                'tasks.hashtags',
                'tasks.created_at',
            ])
            ->with([
                'client:id,name,emoji',
                'assignee:id,name,avatar_color',
            ])
            ->withCount('comments')
            ->whereHas('creator', fn($q) => $q->whereIn('role', ['strategist', 'manager', 'editor', 'content_writer', 'admin']))
            ->where('tasks.status', 'review');
    }

    private function applyApprovalsFilters(Builder $query, Request $request): void
    {
        $search = trim((string) $request->input('search_term', ''));

        if ($search === '') {
            $searchInput = $request->input('search');

            if (is_array($searchInput)) {
                $search = trim((string) ($searchInput['value'] ?? ''));
            } else {
                $search = trim((string) ($searchInput ?? ''));
            }
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('tasks.title', 'like', "%{$search}%")
                    ->orWhere('tasks.caption', 'like', "%{$search}%")
                    ->orWhere('tasks.hashtags', 'like', "%{$search}%")
                    ->orWhere('tasks.type', 'like', "%{$search}%")
                    ->orWhereHas('client', function (Builder $clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignee', function (Builder $assigneeQuery) use ($search) {
                        $assigneeQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('client')) {
            $query->where('tasks.client_id', $request->integer('client'));
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('tasks.type', $request->input('type'));
        }

        if ($request->filled('priority') && $request->input('priority') !== 'all') {
            $query->where('tasks.priority', $request->input('priority'));
        }

        if ($request->filled('platform') && $request->input('platform') !== 'all') {
            $platform = $request->input('platform');
            $query->where(function (Builder $platformQuery) use ($platform) {
                $platformQuery->where('tasks.platform', $platform)
                    ->orWhereJsonContains('tasks.platform', $platform);
            });
        }

        if ($request->filled('assigned_to')) {
            $query->where('tasks.assigned_to', $request->integer('assigned_to'));
        }

        if ($request->filled('date_from')) {
            $query->whereNotNull('tasks.deadline')
                ->whereDate('tasks.deadline', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereNotNull('tasks.deadline')
                ->whereDate('tasks.deadline', '<=', $request->input('date_to'));
        }
    }

    private function applyApprovalsSort(Builder $query, string $sort): void
    {
        if ($sort === 'deadline_asc') {
            $query->orderByRaw('tasks.deadline IS NULL, tasks.deadline ASC')
                ->orderBy('tasks.id', 'desc');
            return;
        }

        if ($sort === 'deadline_desc') {
            $query->orderByRaw('tasks.deadline IS NULL DESC, tasks.deadline DESC')
                ->orderBy('tasks.id', 'desc');
            return;
        }

        if ($sort === 'oldest') {
            $query->orderBy('tasks.created_at', 'asc')
                ->orderBy('tasks.id', 'asc');
            return;
        }

        $query->orderBy('tasks.created_at', 'desc')
            ->orderBy('tasks.id', 'desc');
    }

    private function formatApprovalTaskCell(Task $task): string
    {
        $platformLabel = 'N/A';
        $platform = $task->platform;

        if (is_array($platform)) {
            $platformLabel = implode(', ', array_map(static fn ($value) => ucfirst((string) $value), $platform));
        } elseif (is_scalar($platform) && (string) $platform !== '') {
            $platformLabel = ucfirst((string) $platform);
        }

        return '<div class="apv-task-title">' . e($task->title ?? 'Untitled task') . '</div>'
            . '<div class="apv-task-sub">' . e($platformLabel) . '</div>';
    }

    private function formatApprovalClientCell(Task $task): string
    {
        $emoji = e($task->client?->emoji ?: '🏢');
        $name = e($task->client?->name ?? 'Unknown client');

        return '<span class="apv-client"><span class="apv-client-emoji">' . $emoji . '</span> ' . $name . '</span>';
    }

    private function formatApprovalTypeCell(Task $task): string
    {
        return '<span class="tag ' . e($task->type_tag_class) . '" style="font-size:10px;padding:2px 7px">'
            . e(ucfirst((string) $task->type))
            . '</span>';
    }

    private function formatApprovalAssigneeCell(Task $task): string
    {
        if ($task->assignee) {
            $name = (string) $task->assignee->name;
            $initial = e(strtoupper(substr($name, 0, 1)));
            $color = e($task->assignee->avatar_color ?? '#555');

            return '<div class="apv-assignee">'
                . '<div class="av-sm" style="background:' . $color . '">' . $initial . '</div>'
                . '<span>' . e($name) . '</span>'
                . '</div>';
        }

        return '<span style="font-size:11px;color:var(--red)"><i class="fa-solid fa-circle-exclamation"></i> Unassigned</span>';
    }

    private function formatApprovalPriorityCell(Task $task): string
    {
        return match ($task->priority) {
            'urgent' => '<span class="apv-prio apv-prio-urgent"><i class="fas fa-circle" style="font-size:8px;color:#EF4444"></i> Urgent</span>',
            'high' => '<span class="apv-prio apv-prio-high"><i class="fas fa-circle" style="font-size:8px;color:#F97316"></i> High</span>',
            default => '<span class="apv-prio apv-prio-normal"><i class="fas fa-circle" style="font-size:8px;color:#22C55E"></i> Normal</span>',
        };
    }

    private function formatApprovalDeadlineCell(Task $task): string
    {
        if (!$task->deadline) {
            return '<span style="color:var(--text3)">—</span>';
        }

        $isOverdue = $task->isOverdue();
        $icon = $isOverdue ? 'fa-clock' : 'fa-calendar';
        $badge = $isOverdue ? '<span class="apv-overdue-badge">!</span>' : '';
        $class = $isOverdue ? 'apv-deadline apv-deadline-overdue' : 'apv-deadline';
        $deadline = $task->deadline instanceof Carbon
            ? $task->deadline
            : Carbon::parse((string) $task->deadline);

        return '<div class="' . $class . '">'
            . '<i class="fa-regular ' . $icon . '"></i>'
            . e($deadline->format('d M'))
            . $badge
            . '</div>';
    }

    private function formatApprovalActionsCell(Task $task): string
    {
        $titleJson = json_encode((string) ($task->title ?? 'Untitled task'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $approveUrl = route('strategist.tasks.approve', $task);
        $showUrl = route('strategist.tasks.show', $task);
        $editUrl = route('strategist.tasks.edit', $task);
        $csrf = csrf_token();

        return '<div class="apv-actions">'
            . '<form method="POST" action="' . $approveUrl . '" class="js-approve-form" style="margin:0">'
            . '<input type="hidden" name="_token" value="' . $csrf . '">'
            . '<input type="hidden" name="_method" value="PATCH">'
            . '<button type="submit" class="apv-act-btn apv-act-approve" title="Approve"><i class="fa-solid fa-check"></i></button>'
            . '</form>'
            . '<button type="button" class="apv-act-btn apv-act-revision" title="Request Revision" onclick="openRevisionModal(' . (int) $task->id . ', ' . $titleJson . ')"><i class="fa-solid fa-rotate-left"></i></button>'
            . '<button type="button" class="apv-act-btn apv-act-comment" title="Comment" onclick="openCommentModal(' . (int) $task->id . ', ' . $titleJson . ')"><i class="fa-solid fa-comment"></i></button>'
            . '<a href="' . $showUrl . '" class="apv-act-btn apv-act-view" title="View"><i class="fa-solid fa-eye"></i></a>'
            . '<a href="' . $editUrl . '" class="apv-act-btn apv-act-edit" title="Edit" data-no-loader><i class="fa-solid fa-pen-to-square"></i></a>'
            . '</div>';
    }
}
