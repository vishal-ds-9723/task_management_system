<?php

namespace App\Services\Admin;

use App\Models\Task;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class PublishingService
{
    public function getPublishingIndexData(Request $request)
    {
        $query = Task::with(['client', 'assignee', 'creator', 'socialMediaPosts', 'media', 'clientApprover'])
            ->publishable()
            ->whereIn('status', ['completed', 'published']);

        if ($request->filled('filter')) {
            match ($request->filter) {
                'pending', 'awaiting' => $query->where('status', 'completed'),
                'published'           => $query->where('status', 'published'),
                'client_yes'          => $query->where('status', 'completed')->whereNotNull('client_approved_at'),
                'client_no'           => $query->where('status', 'completed')->whereNull('client_approved_at'),
                default               => null,
            };
        }

        if ($request->filled('client')) {
            $query->where('client_id', $request->client);
        }

        if ($request->filled('creator')) {
            $query->where('created_by', $request->creator);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', $search));
            });
        }

        $query->latest('completed_at');
        $tasks = $query->paginate(15)->withQueryString();

        $pendingCount = Task::publishable()->where('status', 'completed')->count();
        $publishedCount = Task::publishable()->where('status', 'published')->count();
        $totalCount = $pendingCount + $publishedCount;
        $clientApprovedCount = Task::publishable()->where('status', 'completed')->whereNotNull('client_approved_at')->count();
        $clientPendingCount = Task::publishable()->where('status', 'completed')->whereNull('client_approved_at')->count();
        $awaitingCount = $pendingCount;
        $partialCount = 0;

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $strategists = User::where('role', 'strategist')->orderBy('name')->get();

        return compact(
            'tasks', 'pendingCount', 'publishedCount', 'totalCount',
            'clientApprovedCount', 'clientPendingCount',
            'awaitingCount', 'partialCount', 'clients', 'strategists'
        );
    }

    public function getPublishingShowData(Task $task)
    {
        abort_unless($task->inPublishingFlow(), 404, 'This urgent task was delivered before the approval flow.');
        abort_if(!in_array($task->status, ['completed', 'published']), 404);

        $task->load(['client', 'assignee', 'creator', 'socialMediaPosts.poster', 'socialMediaPosts.metrics.creator', 'socialMediaPosts.metrics.updater', 'media']);

        $platforms = is_array($task->platform) ? $task->platform : [];
        $progress = $task->getPublishingProgress();

        return compact('task', 'platforms', 'progress');
    }

    public function publish(Task $task): bool
    {
        abort_unless($task->inPublishingFlow(), 403, 'This urgent task was delivered before the approval flow and has no publishing step.');
        abort_if(!in_array($task->status, ['completed', 'published']), 403, 'Task is not ready for publishing.');

        $task->update(['status' => 'published']);

        $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? [$task->platform] : []);
        foreach ($platforms as $platform) {
            $task->socialMediaPosts()->firstOrCreate(
                ['platform' => $platform],
                [
                    'post_type' => $task->type ?: 'post',
                    'posted_at' => now()->toDateString(),
                    'posted_by' => auth()->id() ?? 1,
                ]
            );
        }

        \App\Models\AuditLog::create([
            'user_id' => auth()->id() ?? 1,
            'task_id' => $task->id,
            'action'  => 'Admin marked task as Published',
        ]);

        return true;
    }

    public function unpublish(Task $task): bool
    {
        abort_unless($task->inPublishingFlow(), 403, 'This urgent task was delivered before the approval flow.');
        abort_if($task->status !== 'published', 403, 'Only published tasks can be reverted to pending.');

        $task->update(['status' => 'completed']);
        $task->socialMediaPosts()->delete();

        \App\Models\AuditLog::create([
            'user_id' => auth()->id() ?? 1,
            'task_id' => $task->id,
            'action'  => 'Admin reverted task publishing status to Pending',
        ]);

        return true;
    }
}

