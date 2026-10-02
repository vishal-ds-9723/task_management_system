<?php

namespace App\Services\Admin;

use App\Models\Client;
use App\Models\ClientSocialMediaLink;
use App\Models\ClientMonthlySchedule;
use App\Models\ActionItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ClientService
{
    public function getFilteredClients(Request $request)
    {
        $search = $request->query('search', '');
        $categoryFilter = $request->query('category', '');
        $sortBy = $request->query('sort', 'name');
        $showInactive = $request->query('inactive', '0') === '1';

        $query = Client::with('socialMediaLinks')->withCount([
            'tasks as active_tasks' => fn ($q) => $q->where('status', '!=', 'completed'),
            'tasks as total_tasks',
            'tasks as completed_tasks' => fn ($q) => $q->where('status', 'completed'),
            'actionItems as active_action_items' => fn ($q) => $q->where('status', '!=', 'done'),
        ]);

        if (!$showInactive) {
            $query->where('is_active', true);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($categoryFilter) {
            $query->where('category', $categoryFilter);
        }

        $clients = $query->get()->map(function ($client) {
            $client->completion_pct = $client->total_tasks > 0
                ? round(($client->completed_tasks / $client->total_tasks) * 100)
                : 0;
            return $client;
        });

        $clients = match ($sortBy) {
            'tasks'      => $clients->sortByDesc('active_tasks')->values(),
            'completion' => $clients->sortByDesc('completion_pct')->values(),
            'recent'     => $clients->sortByDesc('created_at')->values(),
            default      => $clients->sortBy('name')->values(),
        };

        $totalClients = Client::where('is_active', true)->count();
        $totalTasks = $clients->sum('total_tasks');
        $totalActiveTasks = $clients->sum('active_tasks');
        $avgCompletion = $clients->count() > 0 ? round($clients->avg('completion_pct')) : 0;

        $categories = Client::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        $topClient = $clients->sortByDesc('active_tasks')->first();
        $maxActiveTasks = $clients->max('active_tasks') ?? 1;

        return compact(
            'clients', 'search', 'categoryFilter', 'sortBy', 'showInactive',
            'totalClients', 'totalTasks', 'totalActiveTasks', 'avgCompletion',
            'categories', 'topClient', 'maxActiveTasks'
        );
    }

    public function createClient(array $data)
    {
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            $data['logo'] = $this->uploadClientFile($data['logo'], 'client-logos');
        }

        if (isset($data['cta_video']) && $data['cta_video'] instanceof \Illuminate\Http\UploadedFile) {
            $data['cta_video'] = $this->uploadClientFile($data['cta_video'], 'client-cta-videos');
        }

        if (isset($data['footer_image']) && $data['footer_image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['footer_image'] = $this->uploadClientFile($data['footer_image'], 'client-footer-images');
        }

        $data['color'] = $data['color'] ?? '#4F6DF0';
        $data['is_active'] = true;

        $client = Client::create(collect($data)->except('create_login')->toArray());

        if (!empty($data['create_login']) && !empty($data['contact_email'])) {
            $colors = ['#4F6DF0','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#F97316'];
            User::create([
                'name'         => $data['contact_person'] ?? $data['name'],
                'email'        => $data['contact_email'],
                'password'     => Hash::make('password'),
                'role'         => 'client',
                'client_id'    => $client->id,
                'avatar_color' => $colors[array_rand($colors)],
            ]);
        }

        return $client;
    }

    public function updateClient(Client $client, array $data)
    {
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            $data['logo'] = $this->uploadClientFile($data['logo'], 'client-logos', $client->logo);
        }

        if (isset($data['cta_video']) && $data['cta_video'] instanceof \Illuminate\Http\UploadedFile) {
            $data['cta_video'] = $this->uploadClientFile($data['cta_video'], 'client-cta-videos', $client->cta_video);
        } elseif (!empty($data['delete_cta_video'])) {
            $this->deleteStoredFile($client->cta_video);
            $data['cta_video'] = null;
        }

        if (isset($data['footer_image']) && $data['footer_image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['footer_image'] = $this->uploadClientFile($data['footer_image'], 'client-footer-images', $client->footer_image);
        } elseif (!empty($data['delete_footer_image'])) {
            $this->deleteStoredFile($client->footer_image);
            $data['footer_image'] = null;
        }

        $data['is_active'] = isset($data['is_active']);

        $client->update($data);

        return $client;
    }

    private function uploadClientFile(\Illuminate\Http\UploadedFile $file, string $folder, ?string $oldPath = null): string
    {
        if ($oldPath) {
            $this->deleteStoredFile($oldPath);
        }

        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());

        $publicDir = public_path('storage/' . $folder);
        $storageDir = storage_path($folder);

        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $file->move($publicDir, $filename);

        copy($publicDir . '/' . $filename, $storageDir . '/' . $filename);

        return $folder . '/' . $filename;
    }

    private function deleteStoredFile(?string $path): void
    {
        if (!$path) return;

        $oldPublicFile = public_path('storage/' . $path);
        $oldStorageFile = storage_path($path);

        if (file_exists($oldPublicFile)) {
            @unlink($oldPublicFile);
        }
        if (file_exists($oldStorageFile)) {
            @unlink($oldStorageFile);
        }
        Storage::disk('public')->delete($path);
    }

    public function getClientDetail(Client $client, Request $request)
    {
        $tab = $request->query('tab', 'tasks');
        $status = $request->query('status', '');
        $priority = $request->query('priority', '');
        $search = $request->query('search', '');
        $sort = $request->query('sort', 'newest');
        $perPage = $request->query('per_page', 15);
        if ($perPage === 'custom') {
            $perPage = $request->query('per_page_custom', 15);
        }
        if (!is_numeric($perPage) || $perPage < 1) $perPage = 15;
        $perPage = (int)$perPage;

        $currentMonth = (int) $request->query('month', now()->month);
        $currentYear = (int) $request->query('year', now()->year);

        // Tasks
        $tasksQuery = Task::where('client_id', $client->id)
            ->with(['assignee', 'comments']);

        if ($status) {
            $tasksQuery->where('status', $status);
        }
        if ($priority) {
            $tasksQuery->where('priority', $priority);
        }
        if ($search) {
            $tasksQuery->where('title', 'like', '%' . $search . '%');
        }

        $tasks = match ($sort) {
            'deadline' => $tasksQuery->orderBy('deadline')->paginate($perPage)->onEachSide(2)->withQueryString(),
            'priority' => $tasksQuery->orderByRaw("FIELD(priority,'urgent','high','medium','low')")->paginate($perPage)->onEachSide(2)->withQueryString(),
            'status'   => $tasksQuery->orderByRaw("FIELD(status,'todo','in_progress','in_review','revision','completed')")->paginate($perPage)->onEachSide(2)->withQueryString(),
            default    => $tasksQuery->latest()->paginate($perPage)->onEachSide(2)->withQueryString(),
        };

        // Action Items
        $actionItemsQuery = ActionItem::where('client_id', $client->id)
            ->with(['assignee', 'notes']);

        if ($status) {
            $actionItemsQuery->where('status', $status === 'completed' ? 'done' : ($status === 'in_progress' ? 'in_progress' : 'pending'));
        }
        if ($search) {
            $actionItemsQuery->where('title', 'like', '%' . $search . '%');
        }

        $actionItems = $actionItemsQuery->latest()->paginate($perPage)->onEachSide(2)->withQueryString();

        // Stats — single query instead of 6 separate COUNTs
        $taskCounts = Task::where('client_id', $client->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status != 'completed') as active,
                SUM(status = 'completed') as completed,
                SUM(deadline < NOW() AND status != 'completed') as overdue,
                SUM(status = 'inprogress') as in_progress,
                SUM(status = 'review') as in_review
            ")
            ->first();

        $taskStats = [
            'total'       => (int) $taskCounts->total,
            'active'      => (int) $taskCounts->active,
            'completed'   => (int) $taskCounts->completed,
            'overdue'     => (int) $taskCounts->overdue,
            'in_progress' => (int) $taskCounts->in_progress,
            'in_review'   => (int) $taskCounts->in_review,
        ];

        $actionCounts = ActionItem::where('client_id', $client->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status = 'in_progress') as in_progress,
                SUM(status = 'done') as done,
                SUM(status != 'done' AND due_at < NOW()) as overdue
            ")
            ->first();

        $actionStats = [
            'total'       => (int) $actionCounts->total,
            'pending'     => (int) $actionCounts->pending,
            'in_progress' => (int) $actionCounts->in_progress,
            'done'        => (int) $actionCounts->done,
            'overdue'     => (int) $actionCounts->overdue,
        ];

        $completionPct = $taskStats['total'] > 0
            ? round(($taskStats['completed'] / $taskStats['total']) * 100)
            : 0;

        // Legacy social columns on clients table (website, instagram, etc.)
        $oldSocialFields = collect(['website','instagram','facebook','twitter','linkedin','youtube','tiktok'])
            ->filter(fn ($f) => !empty($client->$f));

        $clientLogins = User::where('client_id', $client->id)->where('role', 'client')->get();

        $effectiveSchedule = \App\Models\ClientMonthlySchedule::getEffectiveSchedule($client->id, $currentMonth, $currentYear);
        $exactSchedule = \App\Models\ClientMonthlySchedule::where('client_id', $client->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        // ── Post Insights (Organic vs Paid) ──
        $postInsights = $this->getPostInsights($client, $currentMonth, $currentYear);

        // ── Client Visits ──
        $clientVisits = $client->visits()->with(['visitor', 'updates.user'])->get();

        return compact(
            'client',
            'tab', 'status', 'priority', 'search', 'sort',
            'tasks', 'actionItems', 'taskStats', 'actionStats',
            'completionPct', 'oldSocialFields', 'clientLogins', 'perPage',
            'currentMonth', 'currentYear', 'effectiveSchedule', 'exactSchedule',
            'postInsights', 'clientVisits'
        );
    }

    public function deleteClient(Client $client)
    {
        $this->deleteStoredFile($client->logo);
        $this->deleteStoredFile($client->cta_video);
        $this->deleteStoredFile($client->footer_image);
        $client->delete();

        return true;
    }

    public function createClientLogin(Client $client, array $data)
    {
        $colors = ['#4F6DF0','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#F97316'];

        return User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password'] ?? 'password'),
            'role'         => 'client',
            'client_id'    => $client->id,
            'avatar_color' => $colors[array_rand($colors)],
        ]);
    }

    public function updateClientLogin(User $user, array $data)
    {
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return $user;
    }

    public function deleteClientLogin(User $user)
    {
        $user->delete();

        return true;
    }

    public function addSocialLink(Client $client, array $data)
    {
        $link = ClientSocialMediaLink::create([
            'client_id' => $client->id,
            'platform'  => $data['platform'],
            'url'       => $data['url'],
            'label'     => $data['label'],
            'is_primary' => ClientSocialMediaLink::where('client_id', $client->id)
                            ->where('platform', $data['platform'])
                            ->count() === 0,
        ]);

        return $link;
    }

    public function deleteSocialLink(ClientSocialMediaLink $link)
    {
        $link->delete();

        return true;
    }

    public function getSocialLinksApi(Client $client)
    {
        $links = $client->socialMediaLinks()
            ->select('id', 'platform', 'url', 'label', 'is_primary')
            ->orderBy('is_primary', 'desc')
            ->orderBy('platform')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'links' => $links,
            'platforms' => $links->pluck('platform')->unique()->values(),
        ]);
    }

    public function createMonthlySchedule(Client $client, array $data)
    {
        $data['client_id'] = $client->id;

        return ClientMonthlySchedule::create($data);
    }

    public function updateMonthlySchedule(ClientMonthlySchedule $schedule, array $data)
    {
        $schedule->update($data);

        return $schedule;
    }

    /**
     * Aggregate post insights for a client with organic/paid bifurcation.
     */
    public function getPostInsights(Client $client, int $month, int $year): array
    {
        $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // Get all social media posts for this client (through tasks)
        $clientTaskIds = Task::where('client_id', $client->id)->pluck('id');

        if ($clientTaskIds->isEmpty()) {
            return $this->emptyPostInsights();
        }

        $posts = \App\Models\SocialMediaPost::whereIn('task_id', $clientTaskIds)
            ->with(['latestOrganicMetric', 'latestPaidMetric', 'task:id,title,type,platform'])
            ->get();

        if ($posts->isEmpty()) {
            return $this->emptyPostInsights();
        }

        // Posts filtered to the selected month (by posted_at)
        $monthPosts = $posts->filter(fn ($p) =>
            $p->posted_at && $p->posted_at->between($monthStart, $monthEnd)
        );

        // Aggregate metrics from the latest snapshot of each post
        $organic = ['posts' => 0, 'reach' => 0, 'impressions' => 0, 'views' => 0, 'likes' => 0, 'comments' => 0, 'shares' => 0, 'profile_visits' => 0, 'ad_spend' => 0];
        $paid    = ['posts' => 0, 'reach' => 0, 'impressions' => 0, 'views' => 0, 'likes' => 0, 'comments' => 0, 'shares' => 0, 'profile_visits' => 0, 'ad_spend' => 0];
        $platformBreakdown = [];
        $topPosts = [];

        foreach ($posts as $post) {
            $metricsToProcess = [
                ['metric' => $post->latestOrganicMetric, 'bucket' => 'organic', 'isPaid' => false],
                ['metric' => $post->latestPaidMetric, 'bucket' => 'paid', 'isPaid' => true]
            ];

            foreach ($metricsToProcess as $item) {
                $metric = $item['metric'];
                if (!$metric) continue;

                $bucket = $item['bucket'];
                $isPaid = $item['isPaid'];

                ${$bucket}['posts']++;

                ${$bucket}['reach']          += (int) $metric->reach;
                ${$bucket}['impressions']    += (int) $metric->impressions;
                ${$bucket}['views']          += (int) $metric->views;
                ${$bucket}['likes']          += (int) $metric->likes;
                ${$bucket}['comments']       += (int) $metric->comments;
                ${$bucket}['shares']         += (int) $metric->shares;
                ${$bucket}['profile_visits'] += (int) $metric->profile_visits;
                ${$bucket}['ad_spend']       += (float) ($metric->ad_spend_inr ?? 0);

                // Platform breakdown
                $platform = $post->platform;
                if (!isset($platformBreakdown[$platform])) {
                    $platformBreakdown[$platform] = [
                        'organic' => 0, 'paid' => 0,
                        'reach' => 0, 'impressions' => 0, 'likes' => 0,
                        'comments' => 0, 'shares' => 0, 'views' => 0,
                    ];
                }
                $platformBreakdown[$platform][$bucket]++;
                $platformBreakdown[$platform]['reach']       += (int) $metric->reach;
                $platformBreakdown[$platform]['impressions'] += (int) $metric->impressions;
                $platformBreakdown[$platform]['likes']       += (int) $metric->likes;
                $platformBreakdown[$platform]['comments']    += (int) $metric->comments;
                $platformBreakdown[$platform]['shares']      += (int) $metric->shares;
                $platformBreakdown[$platform]['views']       += (int) $metric->views;

                // Track top posts by engagement (likes + comments + shares)
                $engagement = (int)$metric->likes + (int)$metric->comments + (int)$metric->shares;
                $topPosts[] = [
                    'post'       => $post,
                    'metric'     => $metric,
                    'engagement' => $engagement,
                    'is_paid'    => $isPaid,
                ];
            }
        }

        // Sort top posts by engagement descending, take top 5
        usort($topPosts, fn ($a, $b) => $b['engagement'] <=> $a['engagement']);
        $topPosts = array_slice($topPosts, 0, 5);

        // Monthly posts count for the selected month
        $monthlyOrganic = $monthPosts->filter(fn ($p) => (bool)($p->latestOrganicMetric))->count();
        $monthlyPaid    = $monthPosts->filter(fn ($p) => (bool)($p->latestPaidMetric))->count();

        $totalPosts = $organic['posts'] + $paid['posts'];

        return [
            'has_data'    => true,
            'total_posts' => $totalPosts,
            'organic'     => $organic,
            'paid'        => $paid,
            'platforms'   => $platformBreakdown,
            'top_posts'   => $topPosts,
            'month_organic' => $monthlyOrganic,
            'month_paid'    => $monthlyPaid,
            'month_total'   => $monthlyOrganic + $monthlyPaid,
        ];
    }

    private function emptyPostInsights(): array
    {
        $empty = ['posts' => 0, 'reach' => 0, 'impressions' => 0, 'views' => 0, 'likes' => 0, 'comments' => 0, 'shares' => 0, 'profile_visits' => 0, 'ad_spend' => 0];
        return [
            'has_data'    => false,
            'total_posts' => 0,
            'organic'     => $empty,
            'paid'        => $empty,
            'platforms'   => [],
            'top_posts'   => [],
            'month_organic' => 0,
            'month_paid'    => 0,
            'month_total'   => 0,
        ];
    }
}

