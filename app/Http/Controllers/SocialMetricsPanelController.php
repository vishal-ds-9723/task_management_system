<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SocialMediaPost;
use App\Support\SocialRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SocialMetricsPanelController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'logo', 'emoji', 'color']);

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $clients = $query
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        $clients->getCollection()->transform(function (Client $client) {
            $client->social_posts_count = SocialMediaPost::query()
                ->whereHas('task', fn ($taskQuery) => $taskQuery->where('client_id', $client->id))
                ->count();

            return $client;
        });

        return view('social-metrics.companies', [
            'clients' => $clients,
        ]);
    }

    public function show(Request $request, Client $client): View
    {
        $query = SocialMediaPost::query()
            ->with([
                'task.client',
                'task.media',
                'poster',
                'metrics.creator:id,name',
                'metrics.updater:id,name',
                'latestOrganicMetric.creator:id,name',
                'latestOrganicMetric.updater:id,name',
                'latestPaidMetric.creator:id,name',
                'latestPaidMetric.updater:id,name',
            ])
            ->whereHas('task', fn ($taskQuery) => $taskQuery->where('client_id', $client->id));

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('platform', 'like', "%{$search}%")
                    ->orWhere('post_type', 'like', "%{$search}%")
                    ->orWhereHas('task', function ($taskQuery) use ($search) {
                        $taskQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->string('platform'));
        }

        if ($request->filled('content_type')) {
            $query->where('post_type', $request->string('content_type'));
        }

        $posts = $query->latest('posted_at')->paginate(15)->withQueryString();

        $platforms = SocialRegistry::PLATFORMS;

        $defaultContentTypes = array_merge(SocialRegistry::CONTENT_TYPES, ['flyer', 'brochure']);
        $dbContentTypes = SocialMediaPost::query()
            ->whereHas('task', fn ($taskQuery) => $taskQuery->where('client_id', $client->id))
            ->whereNotNull('post_type')
            ->distinct()
            ->pluck('post_type')
            ->map(fn ($type) => strtolower(trim((string) $type)))
            ->filter()
            ->values()
            ->all();
        $contentTypes = array_values(array_unique(array_merge($defaultContentTypes, $dbContentTypes)));

        return view('social-metrics.panel', [
            'client' => $client,
            'posts' => $posts,
            'platforms' => $platforms,
            'contentTypes' => $contentTypes,
        ]);
    }
}
