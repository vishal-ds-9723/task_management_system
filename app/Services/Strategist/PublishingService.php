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
        $query = Task::with(['client', 'assignee', 'creator', 'media', 'socialMediaPosts', 'clientApprover'])
            ->strategistVisible(\App\Models\Task::STRATEGIST_PANEL_ROLES)
            ->publishable()
            ->whereIn('status', ['completed', 'published']);

        // Filter
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
            ->strategistVisible(\App\Models\Task::STRATEGIST_PANEL_ROLES)
            ->publishable();
        $pendingCount = (clone $baseQuery)->where('status', 'completed')->count();
        $publishedCount = (clone $baseQuery)->where('status', 'published')->count();
        $totalCount = $pendingCount + $publishedCount;

        // Client approval breakdown for completed (awaiting publish) tasks
        $clientApprovedCount = (clone $baseQuery)->where('status', 'completed')->whereNotNull('client_approved_at')->count();
        $clientPendingCount = (clone $baseQuery)->where('status', 'completed')->whereNull('client_approved_at')->count();

        // Legacy compatibility
        $awaitingCount = $pendingCount;
        $partialCount = 0;
        $totalCompleted = $totalCount;

        $clients = \App\Models\Client::where('is_active', true)->orderBy('name')->get();

        return compact(
            'tasks', 'pendingCount', 'publishedCount', 'totalCount',
            'clientApprovedCount', 'clientPendingCount',
            'awaitingCount', 'partialCount', 'totalCompleted', 'clients'
        );
    }

    public function storeProof(Task $task, array $validated)
    {
        abort_unless($task->inPublishingFlow(), 403, 'This urgent task was delivered before the approval flow and has no publishing step.');

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


    public function publish(Task $task): bool
    {
        abort_unless($task->inPublishingFlow(), 403, 'This urgent task was delivered before the approval flow and has no publishing step.');
        abort_if(!in_array($task->status, ['completed', 'published']), 403, 'Task is not ready for publishing.');

        $task->update(['status' => 'published']);

        // Record a social media post entry for target platforms if none exists
        $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? [$task->platform] : []);
        foreach ($platforms as $platform) {
            $task->socialMediaPosts()->firstOrCreate(
                ['platform' => $platform],
                [
                    'post_type' => $task->type ?: 'post',
                    'posted_at' => now()->toDateString(),
                    'posted_by' => Auth::id() ?? 1,
                ]
            );
        }

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'task_id' => $task->id,
            'action'  => 'Marked task as Published',
        ]);

        // Notify admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id'  => $admin->id,
                'task_id'  => $task->id,
                'icon'     => '🚀',
                'title'    => 'Task Published!',
                'subtitle' => '"' . ($task->title ?: 'Untitled') . '" has been published by ' . (Auth::user()?->name ?? 'Strategist') . '.',
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
                    'subtitle' => '"' . ($task->title ?: 'Untitled') . '" has been published on social media.',
                    'link'     => '/client/tasks/' . $task->id,
                ]);
            }
        }

        return true;
    }

    public function unpublish(Task $task): bool
    {
        abort_unless($task->inPublishingFlow(), 403, 'This urgent task was delivered before the approval flow.');
        abort_if($task->status !== 'published', 403, 'Only published tasks can be reverted to pending.');

        $task->update(['status' => 'completed']);

        // Delete post records that were created for publishing
        $task->socialMediaPosts()->delete();

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'task_id' => $task->id,
            'action'  => 'Reverted task publishing status to Pending',
        ]);

        return true;
    }

    public function markAsPublished(Task $task)
    {
        return $this->publish($task);
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
        abort_unless($task->inPublishingFlow(), 404, 'This urgent task was delivered before the approval flow.');

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

