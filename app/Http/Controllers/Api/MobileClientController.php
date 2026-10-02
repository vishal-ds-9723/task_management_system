<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientMonthlySchedule;
use App\Models\ClientSocialMediaLink;
use App\Models\SocialMediaPost;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MobileClientController extends Controller
{
    /**
     * Get clients list.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Client::with(['contacts', 'socialMediaLinks'])
            ->withCount([
                'tasks as active_tasks_count' => fn($q) => $q->whereIn('status', Task::ACTIVE_STATUSES),
                'tasks as completed_tasks_count' => fn($q) => $q->whereIn('status', ['completed', 'published']),
            ]);

        if ($user->isClient()) {
            $query->where('id', $user->client_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('category', 'like', $term);
            });
        }

        $clients = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'clients' => $clients,
        ]);
    }

    /**
     * Create client.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'notes' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $client = Client::create([
            'name' => $request->name,
            'category' => $request->category ?: 'General',
            'emoji' => $request->emoji ?: '🏢',
            'color' => $request->color ?: '#6366F1',
            'website' => $request->website,
            'notes' => $request->notes,
            'contact_person' => $request->contact_person,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'is_active' => true,
        ]);

        // Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'created_client',
            'new_values' => ['name' => $client->name, 'id' => $client->id],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully',
            'client' => $client,
        ], 201);
    }

    /**
     * Get client detail.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if ($user->isClient() && $user->client_id != $id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $client = Client::with(['contacts', 'socialMediaLinks', 'monthlySchedules'])->findOrFail($id);

        $portalUsers = User::where('client_id', $client->id)->get(['id', 'name', 'email', 'avatar_color', 'last_active_at']);

        $recentTasks = Task::with('assignee')
            ->where('client_id', $client->id)
            ->latest()
            ->take(15)
            ->get();

        $publishedPosts = SocialMediaPost::whereHas('task', fn ($q) => $q->where('client_id', $client->id))
            ->latest('posted_at')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'client' => $client,
            'portal_users' => $portalUsers,
            'tasks' => $recentTasks,
            'published_posts' => $publishedPosts,
        ]);
    }

    /**
     * Update client.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $client = Client::findOrFail($id);

        $client->update($request->only([
            'name', 'category', 'emoji', 'color', 'website', 'notes',
            'contact_person', 'contact_email', 'contact_phone', 'is_active',
            'instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'tiktok',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Client updated successfully',
            'client' => $client,
        ]);
    }

    /**
     * Delete client.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client deleted successfully',
        ]);
    }

    /**
     * Create client portal login user.
     */
    public function createLogin(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $client = Client::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $portalUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'client_id' => $client->id,
            'avatar_color' => $client->color ?: '#6366F1',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client portal login created',
            'user' => $portalUser,
        ], 201);
    }

    /**
     * Delete client portal login user.
     */
    public function deleteLogin(Request $request, $id, $userId)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $portalUser = User::where('client_id', $id)->findOrFail($userId);
        $portalUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client portal user deleted',
        ]);
    }

    /**
     * Add client contact person.
     */
    public function storeContact(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $client = Client::findOrFail($id);

        $contact = ClientContact::create([
            'client_id' => $client->id,
            'name' => $request->name,
            'position' => $request->position ?: 'Contact Person',
            'email' => $request->email,
            'phone' => $request->phone,
            'is_primary' => $request->boolean('is_primary', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact added',
            'contact' => $contact,
        ], 201);
    }

    /**
     * Delete client contact person.
     */
    public function destroyContact(Request $request, $id, $contactId)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $contact = ClientContact::where('client_id', $id)->findOrFail($contactId);
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted',
        ]);
    }

    /**
     * Add social media link.
     */
    public function addSocialLink(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $client = Client::findOrFail($id);

        $link = ClientSocialMediaLink::create([
            'client_id' => $client->id,
            'platform' => $request->platform,
            'account_name' => $request->account_name ?: $client->name,
            'handle' => $request->handle,
            'url' => $request->url,
            'is_primary' => $request->boolean('is_primary', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social media link added',
            'link' => $link,
        ], 201);
    }

    /**
     * Delete social media link.
     */
    public function deleteSocialLink(Request $request, $id, $linkId)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $link = ClientSocialMediaLink::where('client_id', $id)->findOrFail($linkId);
        $link->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social link deleted',
        ]);
    }

    /**
     * Get / update monthly schedule for a client.
     */
    public function monthlySchedules(Request $request, $id)
    {
        $schedules = ClientMonthlySchedule::where('client_id', $id)
            ->orderBy('month_year', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Create monthly schedule.
     */
    public function createMonthlySchedule(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isStrategist()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $client = Client::findOrFail($id);

        $schedule = ClientMonthlySchedule::updateOrCreate(
            ['client_id' => $client->id, 'month_year' => $request->month_year],
            [
                'reels_count' => $request->reels_count ?: 0,
                'posts_count' => $request->posts_count ?: 0,
                'stories_count' => $request->stories_count ?: 0,
                'videos_count' => $request->videos_count ?: 0,
                'notes' => $request->notes,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Monthly schedule saved',
            'schedule' => $schedule,
        ]);
    }
}
