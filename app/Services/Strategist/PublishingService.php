<?php

namespace App\Services\Strategist;

use App\Models\Task;
use App\Models\SocialMediaPost;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class PublishingService
{
    public function getIndexData(Request $request)
    {
        $query = Task::with(['client', 'assignee', 'creator', 'socialMediaPosts'])
            ->whereHas('creator', fn($q) => $q->where('role', 'strategist'))
            ->where('is_urgent_task', false)
            ->whereIn('status', ['completed', 'published']);

        // Filter
        if ($request->filled('filter')) {
            match ($request->filter) {
                'awaiting'  => $query->where('status', 'completed')->whereDoesntHave('socialMediaPosts'),
                'partial'   => $query->where('status', 'completed')->whereHas('socialMediaPosts'),
                'published' => $query->where('status', 'published'),
                default     => null,
            };
        }

        if ($request->filled('client')) {
            $query->where('client_id', $request->client);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', $search));
            });
        }

        $query->latest('completed_at');

        $tasks = $query->paginate(20)->withQueryString();

        // Stats
        $baseQuery = Task::query()
            ->whereHas('creator', fn($q) => $q->where('role', 'strategist'))
            ->where('is_urgent_task', false);
        $awaitingCount = (clone $baseQuery)->where('status', 'completed')->whereDoesntHave('socialMediaPosts')->count();
        $partialCount = (clone $baseQuery)->where('status', 'completed')->whereHas('socialMediaPosts')->count();
        $publishedCount = (clone $baseQuery)->where('status', 'published')->count();
        $totalCompleted = $awaitingCount + $partialCount + $publishedCount;

        $clients = \App\Models\Client::where('is_active', true)->orderBy('name')->get();

        return compact(
            'tasks', 'awaitingCount', 'partialCount', 'publishedCount',
            'totalCompleted', 'clients'
        );
    }

    public function storeProof(Task $task, array $validated)
    {
        abort_if($task->is_urgent_task, 403, 'Urgent tasks are delivered without a publishing step.');

        $allPlatforms = 'instagram,facebook,linkedin,twitter,tiktok,youtube,pinterest,snapchat,whatsapp,telegram,reddit,discord,tumblr,spotify,twitch,threads,behance,dribbble,medium,vimeo,sharechat,moj,koo,quora,github,weibo,signal,clubhouse';

        $isImagePlatform = in_array($validated['platform'], SocialMediaPost::IMAGE_PROOF_PLATFORMS);

        // Platforms validation
        $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? [$task->platform] : []);
        abort_if(!in_array($validated['platform'], $platforms), 422, 'Platform not assigned to this task.');

        // Image upload
        $imagePath = null;
        if (isset($validated['proof_image'])) {
            $imagePath = $validated['proof_image']->store('publishing-proofs', 'public');
        }

        $socialMediaPost = $task->socialMediaPosts()->updateOrCreate(
            ['platform' => $validated['platform']],
            [
                'post_url'    => $validated['post_url'] ?? null,
                'proof_image' => $imagePath ?? null,
                'post_type'   => $validated['post_type'],
                'posted_at'   => $validated['posted_at'],
                'posted_by'   => Auth::id(),
            ]
        );

        // Clean up old image if replacing
        if ($imagePath && $socialMediaPost->wasChanged('proof_image') && $socialMediaPost->getOriginal('proof_image')) {
            Storage::disk('public')->delete($socialMediaPost->getOriginal('proof_image'));
        }

        // Check full publish status
        $task->refresh();
        $isFullyPublished = $this->isFullyPublished($task);

        if ($isFullyPublished && $task->status !== 'published') {
            $this->markAsPublished($task);
            $socialMediaPost['fully_published'] = true;
            $socialMediaPost['message'] = 'All platforms posted! Task marked as Published.';
        } else {
            $socialMediaPost['fully_published'] = false;
            $socialMediaPost['message'] = 'Proof saved for ' . ucfirst($validated['platform']) . '.';
        }

        return $socialMediaPost;
    }

    public function isFullyPublished(Task $task): bool
    {
        $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? [$task->platform] : []);
        $postedCount = $task->socialMediaPosts()->count();
        return count($platforms) === $postedCount && $postedCount > 0;
    }


    public function markAsPublished(Task $task)
    {
        if ($task->is_urgent_task) {
            return;
        }

        $task->update(['status' => 'published']);

        AuditLog::create([
            'user_id' => Auth::id(),
            'task_id' => $task->id,
            'action'  => 'Published task — all platform proofs uploaded',
        ]);

        // Notify admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id'  => $admin->id,
                'task_id'  => $task->id,
                'icon'     => '🚀',
                'title'    => 'Task Fully Published!',
                'subtitle' => '"' . $task->title . '" has all social media proofs uploaded.',
                'link'     => '/admin/publishing/' . $task->id,
            ]);
        }

        // Notify client users
        if ($task->client_id) {
            $clientUsers = User::where('client_id', $task->client_id)->get();
            foreach ($clientUsers as $cu) {
                Notification::create([
                    'user_id'  => $cu->id,
                    'task_id'  => $task->id,
                    'icon'     => '✨',
                    'title'    => 'Your content is live!',
                    'subtitle' => '"' . $task->title . '" has been published on social media.',
                    'link'     => '/client/tasks/' . $task->id,
                ]);
            }
        }
    }


    public function destroyProof(Task $task, SocialMediaPost $proof)
    {
        abort_if($proof->task_id !== $task->id, 403);

        if ($proof->proof_image) {
            Storage::disk('public')->delete($proof->proof_image);
        }

        $proof->delete();

        if ($task->status === 'published') {
            $task->update(['status' => 'completed']);
        }

        return [
            'success' => true,
            'message' => ucfirst($proof->platform) . ' proof removed.',
        ];
    }


    public function getTaskDetail(Task $task)
    {
        abort_if($task->is_urgent_task, 404, 'Urgent tasks bypass publishing.');

        $task->load(['client', 'assignee', 'creator', 'socialMediaPosts.poster', 'socialMediaPosts.metrics.creator', 'socialMediaPosts.metrics.updater']);
        $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? [$task->platform] : []);

        $platformStatus = [];
        foreach ($platforms as $platform) {
            $post = $task->socialMediaPosts->firstWhere('platform', $platform);
            $platformStatus[$platform] = [
                'posted' => $post !== null,
                'post'   => $post,
            ];
        }

        return compact('task', 'platformStatus');
    }
}

