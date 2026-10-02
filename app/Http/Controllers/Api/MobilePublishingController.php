<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\SocialMediaPost;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobilePublishingController extends Controller
{
    /**
     * Get publishing queue.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Stats
        $awaitingCount = Task::where('is_urgent_task', false)->where('status', 'completed')->whereDoesntHave('socialMediaPosts')->count();
        $partialCount = Task::where('is_urgent_task', false)->where('status', 'completed')->whereHas('socialMediaPosts')->count();
        $publishedCount = Task::where('is_urgent_task', false)->where('status', 'published')->count();

        $query = Task::with(['client.socialMediaLinks', 'socialMediaPosts.poster', 'assignee'])
            ->whereIn('status', ['completed', 'published']);

        if ($request->has('tab')) {
            if ($request->tab === 'awaiting') {
                $query->where('status', 'completed')->whereDoesntHave('socialMediaPosts');
            } elseif ($request->tab === 'partial') {
                $query->where('status', 'completed')->whereHas('socialMediaPosts');
            } elseif ($request->tab === 'published') {
                $query->where('status', 'published');
            }
        }

        if ($request->has('client_id') && !empty($request->client_id)) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('platform') && !empty($request->platform)) {
            $query->whereJsonContains('platform', $request->platform);
        }

        if ($request->has('search') && !empty($request->search)) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)->orWhere('brief', 'like', $term);
            });
        }

        $tasks = $query->latest('updated_at')->paginate(20);

        return response()->json([
            'success' => true,
            'stats' => [
                'awaiting' => $awaitingCount,
                'partial' => $partialCount,
                'published' => $publishedCount,
                'total' => $awaitingCount + $partialCount + $publishedCount,
            ],
            'tasks' => $tasks->items(),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    /**
     * Get publishing task details.
     */
    public function show(Request $request, $id)
    {
        $task = Task::with(['client.socialMediaLinks', 'socialMediaPosts.poster', 'assignee'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'task' => $task,
            'posts' => $task->socialMediaPosts,
            'social_links' => $task->client ? $task->client->socialMediaLinks : [],
        ]);
    }

    /**
     * Store social publishing proof.
     */
    public function storeProof(Request $request, $taskId)
    {
        $user = $request->user();
        $task = Task::findOrFail($taskId);

        $validator = Validator::make($request->all(), [
            'platform' => 'required|string',
            'post_type' => 'nullable|string|in:' . \App\Support\SocialRegistry::contentTypeValidationList(),
            'post_url' => 'nullable|url',
            'notes' => 'nullable|string',
            'posted_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $clientSocialLink = $task->client
            ? $task->client->socialMediaLinks()->where('platform', $request->platform)->first()
            : null;

        $post = SocialMediaPost::create([
            'task_id' => $task->id,
            'client_social_media_link_id' => $clientSocialLink?->id,
            'posted_by' => $user->id,
            'platform' => $request->platform,
            'post_type' => $request->post_type ?: 'post',
            'post_url' => $request->post_url,
            'posted_at' => $request->posted_at ?? now(),
        ]);

        if ($request->filled('notes')) {
            Comment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'body' => "📤 Published on {$request->platform}: " . $request->notes,
            ]);
        }

        // If mark complete is requested or all platforms posted, mark published
        if ($request->boolean('mark_published', false)) {
            $task->update(['status' => 'published']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Publishing proof logged successfully',
            'post' => $post,
        ], 201);
    }

    /**
     * Delete publishing proof.
     */
    public function destroyProof(Request $request, $taskId, $proofId)
    {
        $post = SocialMediaPost::where('task_id', $taskId)->findOrFail($proofId);
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proof deleted successfully',
        ]);
    }
}
