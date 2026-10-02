<?php

namespace App\Services\Client;

use App\Models\Task;
use Illuminate\Http\Request;

class PublishedContentService
{
    public function getIndexData(Request $request)
    {
        $clientId = auth()->user()->client_id;

        $query = Task::with(['media', 'socialMediaPosts', 'assignee', 'creator'])
            ->where('client_id', $clientId)
            ->where('status', 'published');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('platform')) {
            $query->whereJsonContains('platform', $request->platform);
        }

        if ($request->filled('month')) {
            $query->whereMonth('completed_at', $request->month);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('caption', 'like', $search);
            });
        }

        $tasks = $query->latest('completed_at')->paginate(12)->withQueryString();

        $totalPublished = Task::where('client_id', $clientId)->where('status', 'published')->count();

        $platformStats = [];
        $allPublished = Task::where('client_id', $clientId)->where('status', 'published')->get();
        foreach ($allPublished as $t) {
            $platforms = is_array($t->platform) ? $t->platform : [];
            foreach ($platforms as $p) {
                $platformStats[$p] = ($platformStats[$p] ?? 0) + 1;
            }
        }

        return compact('tasks', 'totalPublished', 'platformStats');
    }
}

