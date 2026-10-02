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
        $query = Task::with(['client', 'assignee', 'creator', 'socialMediaPosts'])
            ->where('is_urgent_task', false)
            ->whereIn('status', ['completed', 'published']);

        if ($request->filled('filter')) {
            match ($request->filter) {
                'awaiting'  => $query->where('status', 'completed')
                                     ->whereDoesntHave('socialMediaPosts'),
                'partial'   => $query->where('status', 'completed')
                                     ->whereHas('socialMediaPosts'),
                'published' => $query->where('status', 'published'),
                default     => null,
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

        $awaitingCount = Task::where('is_urgent_task', false)->where('status', 'completed')->whereDoesntHave('socialMediaPosts')->count();
        $partialCount = Task::where('is_urgent_task', false)->where('status', 'completed')->whereHas('socialMediaPosts')->count();
        $publishedCount = Task::where('is_urgent_task', false)->where('status', 'published')->count();
        $totalCount = $awaitingCount + $partialCount + $publishedCount;

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $strategists = User::where('role', 'strategist')->orderBy('name')->get();

        return compact(
            'tasks', 'awaitingCount', 'partialCount', 'publishedCount',
            'totalCount', 'clients', 'strategists'
        );
    }

    public function getPublishingShowData(Task $task)
    {
        abort_if($task->is_urgent_task, 404, 'Urgent tasks bypass publishing.');
        abort_if(!in_array($task->status, ['completed', 'published']), 404);

        $task->load(['client', 'assignee', 'creator', 'socialMediaPosts.poster', 'socialMediaPosts.metrics.creator', 'socialMediaPosts.metrics.updater']);

        $platforms = is_array($task->platform) ? $task->platform : [];
        $progress = $task->getPublishingProgress();

        return compact('task', 'platforms', 'progress');
    }
}

