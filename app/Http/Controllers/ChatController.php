<?php

namespace App\Http\Controllers;

use App\Events\GroupMembersChanged;
use App\Events\GroupUpdated;
use App\Events\MessageDeleted;
use App\Events\MessageEdited;
use App\Events\MessageReacted;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /** Max attachment size (bytes) — 500 MB */
    private const MAX_ATTACHMENT_BYTES = 500 * 1024 * 1024;

    /** Whitelisted attachment MIME types */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        'application/msword', // .doc
        'text/plain',
    ];

    public function index()
    {
        $me = Auth::user();

        $users = $this->availableChatUsersQuery($me->id)
            ->get(['id', 'name', 'role', 'avatar_color']);

        $conversations = $this->myConversations($me->id);
        $unread = $this->unreadCountsByConversation($me->id);

        return view('chat.index', compact('users', 'conversations', 'me', 'unread'));
    }

    /**
     * Load all conversations the user participates in (1:1 + groups).
     */
    private function myConversations(int $userId)
    {
        return Conversation::query()
            ->whereIn('id', $this->internalConversationIdsQuery($userId))
            ->with([
                'userOne:id,name,role,avatar_color',
                'userTwo:id,name,role,avatar_color',
                'participants:id,name,role,avatar_color',
            ])
            ->orderByDesc('last_message_at')
            ->get();
    }

    public function show(User $user)
    {
        $me = Auth::user();

        if ($user->id === $me->id) {
            abort(400, 'Cannot chat with yourself.');
        }

        $this->abortIfClientChatUser($user);

        $conversation = Conversation::between($me->id, $user->id);

        $messages = $conversation->messages()
            ->with(['user:id,name,role,avatar_color', 'reactions:id,message_id,user_id,emoji'])
            ->orderBy('created_at')
            ->limit(200)
            ->get();

        // Mark all unread messages from the other user as read, then notify the sender(s)
        $unreadBySender = Message::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $me->id)
            ->whereNull('read_at')
            ->select('id', 'user_id')
            ->get()
            ->groupBy('user_id');

        if ($unreadBySender->isNotEmpty()) {
            Message::where('conversation_id', $conversation->id)
                ->where('user_id', '!=', $me->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            foreach ($unreadBySender as $senderId => $msgs) {
                broadcast(new MessageRead(
                    recipients: (int) $senderId,
                    conversationId: $conversation->id,
                    messageIds: $msgs->pluck('id')->all(),
                    readerId: $me->id,
                ))->toOthers();
            }
        }

        // Clear bell-dropdown notifications pointing to this 1:1 thread
        DB::table('notifications_custom')
            ->where('user_id', $me->id)
            ->where('link', route('chat.show', $user))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json([
                'conversation_id' => $conversation->id,
                'messages' => $messages,
                'partner' => $user->only(['id', 'name', 'role', 'avatar_color']),
            ]);
        }

        $users = $this->availableChatUsersQuery($me->id)
            ->get(['id', 'name', 'role', 'avatar_color']);

        $conversations = $this->myConversations($me->id);
        $unread = $this->unreadCountsByConversation($me->id);

        return view('chat.index', [
            'users'         => $users,
            'conversations' => $conversations,
            'me'            => $me,
            'partner'       => $user,
            'conversation'  => $conversation,
            'messages'      => $messages,
            'unread'        => $unread,
        ]);
    }

    public function search(Request $request)
    {
        $me = Auth::user();
        $q = trim($request->get('q', ''));

        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $myConvos = DB::query()
            ->fromSub($this->internalConversationIdsQuery($me->id), 'chat_conversations')
            ->pluck('conversation_id');

        $messages = Message::whereIn('conversation_id', $myConvos)
            ->where('body', 'like', '%' . $q . '%')
            ->with([
                'user:id,name,role,avatar_color',
                'conversation:id,user_one_id,user_two_id',
                'conversation.userOne:id,name,role,avatar_color',
                'conversation.userTwo:id,name,role,avatar_color',
            ])
            ->orderByDesc('created_at')
            ->limit(40)
            ->get()
            ->map(function ($m) use ($me) {
                $other = $m->conversation->otherParticipant($me->id);
                return [
                    'id'              => $m->id,
                    'conversation_id' => $m->conversation_id,
                    'body'            => $m->body,
                    'sender_id'       => $m->user_id,
                    'sender_name'     => $m->user?->name,
                    'created_at'      => $m->created_at?->toIso8601String(),
                    'partner'         => $other ? [
                        'id'           => $other->id,
                        'name'         => $other->name,
                        'role'         => $other->role,
                        'avatar_color' => $other->avatar_color ?? '#4F6DF0',
                    ] : null,
                ];
            });

        return response()->json(['results' => $messages, 'query' => $q]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $me = Auth::user();

        if (! $conversation->hasParticipant($me->id)) {
            abort(403);
        }

        $this->abortIfConversationTargetsClient($conversation);

        $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachment' => [
                'nullable',
                'file',
                'max:' . (self::MAX_ATTACHMENT_BYTES / 1024), // Laravel expects KB
            ],
        ]);

        $body = trim((string) $request->input('body', ''));
        $hasFile = $request->hasFile('attachment');

        if ($body === '' && ! $hasFile) {
            return response()->json(['message' => 'Message cannot be empty.'], 422);
        }

        $attachmentFields = [];

        if ($hasFile) {
            $file = $request->file('attachment');
            $mime = $file->getMimeType();

            if (! in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
                return response()->json([
                    'message' => 'Unsupported file type.',
                    'errors' => ['attachment' => ['Unsupported file type.']],
                ], 422);
            }

            $path = $file->store('chat', 'public');

            $attachmentFields = [
                'attachment_path' => $path,
                'attachment_type' => $mime,
                'attachment_name' => $file->getClientOriginalName(),
                'attachment_size' => $file->getSize(),
            ];
        }

        $message = DB::transaction(function () use ($conversation, $me, $body, $attachmentFields) {
            $message = $conversation->messages()->create(array_merge([
                'user_id' => $me->id,
                'body'    => $body,
            ], $attachmentFields));

            $conversation->update(['last_message_at' => $message->created_at]);

            return $message;
        });

        $recipientId = $conversation->recipientIds($me->id);

        broadcast(new MessageSent($message, $recipientId))->toOthers();

        $message->load('user:id,name,role,avatar_color');

        // Persist a notification row per recipient so it shows up in the bell dropdown.
        $this->createChatNotifications($conversation, $message, $me, $recipientId);

        return response()->json($this->serializeMessage($message));
    }

    /**
     * Write one Notification row per recipient (used by the existing bell-dropdown poll).
     */
    private function createChatNotifications(Conversation $conversation, Message $message, User $sender, array $recipientIds): void
    {
        if (empty($recipientIds)) return;

        $link = $conversation->is_group
            ? route('chat.groups.show', $conversation)
            : route('chat.show', $sender);

        $title = $conversation->is_group
            ? ($conversation->name ?: 'Group') . ' · ' . $sender->name
            : $sender->name;

        $preview = trim((string) $message->body);
        if ($preview === '' && $message->attachment_path) {
            $preview = $message->isImageAttachment() ? '📷 Photo' : '📎 ' . ($message->attachment_name ?: 'Attachment');
        }
        $subtitle = mb_strlen($preview) > 120 ? mb_substr($preview, 0, 117) . '…' : $preview;

        $now = now();
        $rows = array_map(fn ($uid) => [
            'user_id'    => $uid,
            'icon'       => '💬',
            'title'      => $title,
            'subtitle'   => $subtitle,
            'link'       => $link,
            'created_at' => $now,
            'updated_at' => $now,
        ], $recipientIds);

        DB::table('notifications_custom')->insert($rows);
    }

    public function update(Request $request, Message $message)
    {
        $me = Auth::user();

        if ($message->user_id !== $me->id) {
            abort(403, 'You can only edit your own messages.');
        }

        $this->abortIfConversationTargetsClient($message->conversation);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message->update([
            'body'      => $data['body'],
            'edited_at' => now(),
        ]);

        $conversation = $message->conversation;
        $recipientId = $conversation->recipientIds($me->id);

        broadcast(new MessageEdited($message->fresh(), $recipientId))->toOthers();

        return response()->json([
            'id'        => $message->id,
            'body'      => $message->body,
            'edited_at' => $message->edited_at?->toIso8601String(),
        ]);
    }

    public function react(Request $request, Message $message)
    {
        $me = Auth::user();

        $conversation = $message->conversation;
        if (! $conversation->hasParticipant($me->id)) {
            abort(403);
        }

        $this->abortIfConversationTargetsClient($conversation);

        $data = $request->validate([
            'emoji' => ['required', 'string', 'max:16'],
        ]);

        $emoji = $data['emoji'];

        // Toggle: if I already reacted with this emoji, remove it; otherwise add.
        $existing = MessageReaction::where('message_id', $message->id)
            ->where('user_id', $me->id)
            ->where('emoji', $emoji)
            ->first();

        $added = false;
        if ($existing) {
            $existing->delete();
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id'    => $me->id,
                'emoji'      => $emoji,
            ]);
            $added = true;
        }

        // Recompute counts for this message
        $counts = MessageReaction::where('message_id', $message->id)
            ->select('emoji', DB::raw('COUNT(*) as c'))
            ->groupBy('emoji')
            ->pluck('c', 'emoji')
            ->all();

        $recipientId = $conversation->recipientIds($me->id);

        broadcast(new MessageReacted(
            recipients: $recipientId,
            messageId: $message->id,
            conversationId: $conversation->id,
            counts: $counts,
            actorId: $me->id,
            emoji: $emoji,
            added: $added,
        ))->toOthers();

        // Return my own current reactions on this message (used by client to update its UI)
        $myEmojis = MessageReaction::where('message_id', $message->id)
            ->where('user_id', $me->id)
            ->pluck('emoji')
            ->all();

        return response()->json([
            'message_id' => $message->id,
            'counts'     => $counts,
            'mine'       => $myEmojis,
            'added'      => $added,
            'emoji'      => $emoji,
        ]);
    }

    public function destroy(Message $message)
    {
        $me = Auth::user();

        if ($message->user_id !== $me->id) {
            abort(403, 'You can only delete your own messages.');
        }

        $conversation = $message->conversation;
        $this->abortIfConversationTargetsClient($conversation);
        $messageId = $message->id;
        $conversationId = $message->conversation_id;
        $recipientId = $conversation->recipientIds($me->id);

        $message->delete(); // soft delete

        broadcast(new MessageDeleted($messageId, $conversationId, $recipientId))->toOthers();

        return response()->json(['id' => $messageId, 'deleted' => true]);
    }

    public function showGroup(Conversation $conversation)
    {
        $me = Auth::user();

        if (! $conversation->is_group || ! $conversation->hasParticipant($me->id)) {
            abort(404);
        }

        $this->abortIfConversationTargetsClient($conversation);

        $messages = $conversation->messages()
            ->with(['user:id,name,role,avatar_color', 'reactions:id,message_id,user_id,emoji'])
            ->orderBy('created_at')
            ->limit(200)
            ->get();

        // Mark unread (from anyone but me) as read, then notify each sender
        $unreadBySender = Message::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $me->id)
            ->whereNull('read_at')
            ->select('id', 'user_id')
            ->get()
            ->groupBy('user_id');

        if ($unreadBySender->isNotEmpty()) {
            Message::where('conversation_id', $conversation->id)
                ->where('user_id', '!=', $me->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            foreach ($unreadBySender as $senderId => $msgs) {
                broadcast(new MessageRead(
                    recipients: (int) $senderId,
                    conversationId: $conversation->id,
                    messageIds: $msgs->pluck('id')->all(),
                    readerId: $me->id,
                ))->toOthers();
            }
        }

        // Clear bell-dropdown notifications pointing to this group thread
        DB::table('notifications_custom')
            ->where('user_id', $me->id)
            ->where('link', route('chat.groups.show', $conversation))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $users = $this->availableChatUsersQuery($me->id)
            ->get(['id', 'name', 'role', 'avatar_color']);
        $conversations = $this->myConversations($me->id);
        $unread = $this->unreadCountsByConversation($me->id);

        $conversation->load('participants:id,name,role,avatar_color');

        return view('chat.index', [
            'users'         => $users,
            'conversations' => $conversations,
            'me'            => $me,
            'group'         => $conversation,
            'conversation'  => $conversation,
            'messages'      => $messages,
            'unread'        => $unread,
        ]);
    }

    public function createGroup(Request $request)
    {
        $me = Auth::user();
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:80'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        // Filter out the creator (createGroup auto-includes them) and ensure no duplicates
        $requestedMemberIds = array_values(array_unique(array_filter(
            array_map('intval', $data['member_ids']),
            fn ($id) => $id !== $me->id
        )));

        $memberIds = $this->eligibleChatMemberIds($requestedMemberIds);

        if (count($memberIds) !== count($requestedMemberIds)) {
            return response()->json(['message' => 'Client users cannot be added to chat groups.'], 422);
        }

        if (empty($memberIds)) {
            return response()->json(['message' => 'Add at least one other member.'], 422);
        }

        $group = Conversation::createGroup($data['name'], $me->id, $memberIds);

        return response()->json([
            'id'   => $group->id,
            'name' => $group->name,
            'url'  => route('chat.groups.show', $group),
        ]);
    }

    public function updateGroup(Request $request, Conversation $conversation)
    {
        $me = Auth::user();

        if (! $conversation->is_group || ! $conversation->hasParticipant($me->id)) {
            abort(404);
        }

        $this->abortIfConversationTargetsClient($conversation);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $conversation->update(['name' => $data['name']]);

        // Broadcast to every other participant
        broadcast(new GroupUpdated(
            recipients: $conversation->recipientIds($me->id),
            conversationId: $conversation->id,
            name: $conversation->name,
        ))->toOthers();

        return response()->json([
            'id'   => $conversation->id,
            'name' => $conversation->name,
        ]);
    }

    public function addGroupMembers(Request $request, Conversation $conversation)
    {
        $me = Auth::user();

        if (! $conversation->is_group || ! $conversation->hasParticipant($me->id)) {
            abort(404);
        }

        $this->abortIfConversationTargetsClient($conversation);

        $data = $request->validate([
            'member_ids'   => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $existingIds = $conversation->activeParticipantIds();
        $requestedIds = array_values(array_unique(array_diff(
            array_map('intval', $data['member_ids']),
            $existingIds
        )));

        $toAdd = $this->eligibleChatMemberIds($requestedIds);

        if (count($toAdd) !== count($requestedIds)) {
            return response()->json(['added' => [], 'message' => 'Client users cannot be added to chat groups.'], 422);
        }

        if (empty($toAdd)) {
            return response()->json(['added' => [], 'message' => 'Those users are already in the group.']);
        }

        $now = now();
        $rows = array_map(fn ($id) => [
            'conversation_id' => $conversation->id,
            'user_id'         => $id,
            'joined_at'       => $now,
            'created_at'      => $now,
            'updated_at'      => $now,
        ], $toAdd);

        // Use insertOrIgnore to safely re-add anyone who previously left
        DB::table('conversation_participants')->insertOrIgnore($rows);

        // For people who previously left (have a left_at row), clear it so they're active again
        DB::table('conversation_participants')
            ->where('conversation_id', $conversation->id)
            ->whereIn('user_id', $toAdd)
            ->whereNotNull('left_at')
            ->update(['left_at' => null]);

        $added = User::whereIn('id', $toAdd)->get(['id', 'name', 'role', 'avatar_color']);
        $totalMembers = count($conversation->activeParticipantIds());

        // Broadcast to ALL active participants (including the newly added ones)
        broadcast(new GroupMembersChanged(
            recipients: $conversation->recipientIds($me->id),
            conversationId: $conversation->id,
            added: $added->toArray(),
            removedIds: [],
            totalMembers: $totalMembers,
        ));

        return response()->json([
            'added' => $added,
            'count' => $added->count(),
            'total_members' => $totalMembers,
        ]);
    }

    public function removeGroupMember(Conversation $conversation, User $user)
    {
        $me = Auth::user();

        if (! $conversation->is_group || ! $conversation->hasParticipant($me->id)) {
            abort(404);
        }

        $this->abortIfConversationTargetsClient($conversation);

        // Only the group creator can remove others
        if ($conversation->created_by !== $me->id) {
            abort(403, 'Only the group admin can remove members.');
        }

        if ($user->id === $me->id) {
            return response()->json(['message' => 'Use Leave Group instead.'], 422);
        }

        if (! $conversation->hasParticipant($user->id)) {
            return response()->json(['message' => 'User is not in the group.'], 422);
        }

        DB::table('conversation_participants')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update(['left_at' => now()]);

        $totalMembers = count($conversation->activeParticipantIds());

        broadcast(new GroupMembersChanged(
            recipients: $conversation->recipientIds($me->id),
            conversationId: $conversation->id,
            added: [],
            removedIds: [$user->id],
            totalMembers: $totalMembers,
        ));

        return response()->json([
            'removed_id' => $user->id,
            'total_members' => $totalMembers,
        ]);
    }

    public function leaveGroup(Conversation $conversation)
    {
        $me = Auth::user();

        if (! $conversation->is_group || ! $conversation->hasParticipant($me->id)) {
            abort(404);
        }

        $this->abortIfConversationTargetsClient($conversation);

        DB::table('conversation_participants')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $me->id)
            ->update(['left_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /**
     * Returns [conversation_id => unread_count] for the given user — single grouped query.
     */
    private function unreadCountsByConversation(int $userId): array
    {
        return Message::query()
            ->select('conversation_id', DB::raw('COUNT(*) as unread'))
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->whereIn('conversation_id', $this->internalConversationIdsQuery($userId))
            ->groupBy('conversation_id')
            ->pluck('unread', 'conversation_id')
            ->all();
    }

    private function availableChatUsersQuery(int $excludeUserId)
    {
        return User::query()
            ->where('id', '!=', $excludeUserId)
            ->where('role', '!=', 'client')
            ->orderBy('name');
    }

    private function internalConversationIdsQuery(int $userId)
    {
        return DB::table('conversation_participants')
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->whereNotIn('conversation_id', function ($query) {
                $query->select('conversation_participants.conversation_id')
                    ->from('conversation_participants')
                    ->join('users', 'users.id', '=', 'conversation_participants.user_id')
                    ->where('users.role', 'client');
            })
            ->select('conversation_id');
    }

    private function eligibleChatMemberIds(array $memberIds): array
    {
        if (empty($memberIds)) {
            return [];
        }

        return User::query()
            ->whereIn('id', $memberIds)
            ->where('role', '!=', 'client')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function abortIfClientChatUser(User $user): void
    {
        if ($user->isClient()) {
            abort(404);
        }
    }

    private function abortIfConversationTargetsClient(Conversation $conversation): void
    {
        $hasClientParticipant = DB::table('conversation_participants')
            ->join('users', 'users.id', '=', 'conversation_participants.user_id')
            ->where('conversation_participants.conversation_id', $conversation->id)
            ->where('users.role', 'client')
            ->exists();

        if ($hasClientParticipant) {
            abort(404);
        }
    }

    private function serializeMessage(Message $message): array
    {
        return [
            'id'              => $message->id,
            'conversation_id' => $message->conversation_id,
            'user_id'         => $message->user_id,
            'user_name'       => $message->user?->name,
            'user_role'       => $message->user?->role,
            'body'            => $message->body,
            'created_at'      => $message->created_at?->toIso8601String(),
            'attachment'      => $message->attachment_path ? [
                'url'  => $message->attachmentUrl(),
                'type' => $message->attachment_type,
                'name' => $message->attachment_name,
                'size' => $message->attachment_size,
                'is_image' => $message->isImageAttachment(),
            ] : null,
        ];
    }
}
