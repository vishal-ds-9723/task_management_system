<?php

namespace App\Http\Controllers\Strategist;

use App\Http\Controllers\Controller;
use App\Services\Strategist\PublishingService;
use Illuminate\Http\Request;
use App\Models\ClientSocialMediaLink;
use App\Models\Task;
use App\Models\SocialMediaPost;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Support\SocialRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PublishingController extends Controller
{
    protected $service;

    public function __construct(PublishingService $service)
    {
        $this->service = $service;
    }

    /**
     * Publishing queue — completed tasks awaiting social media proof
     */
    public function index(Request $request)
    {
        $data = $this->service->getIndexData($request);
        return view('strategist.publishing', $data);
    }

    /**
     * Show publishing detail for a task — upload proof links per platform
     */
    public function show(Task $task)
    {
        abort_if($task->is_urgent_task, 404, 'Urgent tasks bypass publishing.');
        abort_if(!in_array($task->status, ['completed', 'published']), 403, 'Task is not ready for publishing.');

        $task->load(['client', 'assignee', 'creator', 'socialMediaPosts.poster', 'socialMediaPosts.metrics.creator', 'socialMediaPosts.metrics.updater']);

        $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? [$task->platform] : []);
        $postedPlatforms = $task->socialMediaPosts->pluck('platform')->toArray();

        // Build platform status map
        $platformStatus = [];
        foreach ($platforms as $platform) {
            $post = $task->socialMediaPosts->firstWhere('platform', $platform);
            $platformStatus[$platform] = [
                'posted'    => in_array($platform, $postedPlatforms),
                'post'      => $post,
            ];
        }

        return view('strategist.publishing-detail', compact('task', 'platformStatus'));
    }

    /**
     * Store proof link for a specific platform
     */
    public function storeProof(Request $request, Task $task)
    {
        abort_if($task->is_urgent_task, 403, 'Urgent tasks are delivered without a publishing step.');
        abort_if(!in_array($task->status, ['completed', 'published']), 403);

        $allPlatforms = SocialRegistry::platformValidationList();

        $isImagePlatform = in_array($request->platform, SocialRegistry::IMAGE_PROOF_PLATFORMS);

        $rules = [
            'platform'  => ['required', 'string', 'in:' . $allPlatforms],
            'post_type' => ['required', 'string', 'in:' . SocialRegistry::contentTypeValidationList()],
            'posted_at' => ['required', 'date', 'before_or_equal:today'],
        ];

        if ($isImagePlatform) {
            $rules['proof_image'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
            $rules['post_url']   = ['nullable', 'url', 'max:2048'];
        } else {
            $rules['post_url']   = ['required', 'url', 'max:2048'];
            $rules['proof_image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:512000'];
        }

        $validated = $request->validate($rules);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('proof_image')) {
            $imagePath = $request->file('proof_image')->store('publishing-proofs', 'public');
        }

        // Ensure platform is one of the task's platforms
        $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? [$task->platform] : []);
        abort_if(!in_array($validated['platform'], $platforms), 422, 'This platform is not assigned to this task.');

        $clientSocialMediaLinkId = $this->resolveClientSocialLinkId($task, $validated['platform']);

        // Check if already posted for this platform
        $existing = $task->socialMediaPosts()->where('platform', $validated['platform'])->first();
        if ($existing) {
            // Delete old image if replacing
            if ($imagePath && $existing->proof_image) {
                Storage::disk('public')->delete($existing->proof_image);
            }
            $existing->update([
                'post_url'    => $validated['post_url'] ?? null,
                'proof_image' => $imagePath ?? $existing->proof_image,
                'post_type'   => $validated['post_type'],
                'posted_at'   => $validated['posted_at'],
                'posted_by'   => Auth::id(),
                'client_social_media_link_id' => $clientSocialMediaLinkId,
            ]);
        } else {
            SocialMediaPost::create([
                'task_id'     => $task->id,
                'client_social_media_link_id' => $clientSocialMediaLinkId,
                'platform'    => $validated['platform'],
                'post_url'    => $validated['post_url'] ?? null,
                'proof_image' => $imagePath,
                'post_type'   => $validated['post_type'],
                'posted_at'   => $validated['posted_at'],
                'posted_by'   => Auth::id(),
            ]);
        }

        // Check if all platforms are now posted
        $task->refresh();
        if ($task->isFullyPublished() && $task->status !== 'published') {
            $task->update(['status' => 'published']);

            // Log it
            AuditLog::create([
                'user_id' => Auth::id(),
                'task_id' => $task->id,
                'action'  => 'Published task — all platform proofs uploaded',
            ]);

            // Notify admin
            $admins = \App\Models\User::where('role', 'admin')->get();
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

            // 🚀 Notify Client
            if ($task->client_id) {
                $clientUsers = \App\Models\User::where('client_id', $task->client_id)->get();
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

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'All platforms posted! Task marked as Published.',
                    'fully_published' => true,
                ]);
            }
            return back()->with('success', 'All platforms posted! Task marked as Published. 🚀');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Proof link saved for ' . ucfirst($validated['platform']) . '.',
                'fully_published' => false,
            ]);
        }
        return back()->with('success', 'Proof link saved for ' . ucfirst($validated['platform']) . '.');
    }

    /**
     * Remove a proof link
     */
    public function destroyProof(Task $task, SocialMediaPost $proof)
    {
        abort_if($task->is_urgent_task, 403, 'Urgent tasks do not use publishing proofs.');
        abort_if($proof->task_id !== $task->id, 403);

        $platform = $proof->platform;

        // Delete proof image file if exists
        if ($proof->proof_image) {
            Storage::disk('public')->delete($proof->proof_image);
        }

        $proof->delete();

        // If task was published, revert to completed
        if ($task->status === 'published') {
            $task->update(['status' => 'completed']);
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => ucfirst($platform) . ' proof removed.',
            ]);
        }
        return back()->with('success', ucfirst($platform) . ' proof removed.');
    }

    private function resolveClientSocialLinkId(Task $task, string $platform): ?int
    {
        $task->loadMissing(['client.socialMediaLinks', 'socialMediaLink']);

        $clientLinks = $task->client?->socialMediaLinks ?? collect();
        $selectedIds = collect((array) ($task->selected_social_media_link_ids ?? []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedIds->isNotEmpty()) {
            $selectedLink = $clientLinks->first(function (ClientSocialMediaLink $link) use ($selectedIds, $platform) {
                return $selectedIds->contains((int) $link->id)
                    && strtolower((string) $link->platform) === strtolower($platform);
            });

            if ($selectedLink) {
                return (int) $selectedLink->id;
            }
        }

        $taskLink = $task->socialMediaLink;
        if ($taskLink && strtolower((string) $taskLink->platform) === strtolower($platform)) {
            return (int) $taskLink->id;
        }

        $fallback = $clientLinks
            ->where('platform', $platform)
            ->sortByDesc('is_primary')
            ->first();

        return $fallback ? (int) $fallback->id : null;
    }
}
