<?php

namespace App\Http\Controllers\Api;

use App\Events\GroupMembersChanged;
use App\Events\GroupUpdated;
use App\Events\MessageDeleted;
use App\Events\MessageEdited;
use App\Events\MessageReacted;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobileChatController extends Controller
{
    /**
     * Get directory of all team members available to chat with.
     */
    public function users(Request $request)
    {
        $userId = $request->user()->id;

        $users = User::where('id', '!=', $userId)
            ->where('role', '!=', 'client')
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'email', 'avatar_color']);

        $result = $users->map(function ($u) use ($userId) {
            $convo = Conversation::where('is_group', false)
                ->where(function ($q) use ($userId, $u) {
                    $q->where(function ($sub) use ($userId, $u) {
                        $sub->where('user_one_id', $userId)->where('user_two_id', $u->id);
                    })->orWhere(function ($sub) use ($userId, $u) {
                        $sub->where('user_one_id', $u->id)->where('user_two_id', $userId);
                    });
                })->first();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role,
                'email' => $u->email,
                'avatar_color' => $u->avatar_color,
                'is_online' => $u->isOnline(),
                'conversation_id' => $convo?->id,
            ];
        });

        return response()->json([
            'success' => true,
            'users' => $result,
        ]);
    }

    /**
     * Start or find a 1:1 conversation with a specific user.
     */
    public function startDirectChat(Request $request, $targetUserId)
    {
        $userId = $request->user()->id;
        $target = User::findOrFail($targetUserId);

        if ($target->id === $userId) {
            return response()->json(['success' => false, 'message' => 'Cannot chat with yourself'], 400);
        }

        $conversation = Conversation::between($userId, $target->id);

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'name' => $target->name,
                'is_group' => false,
                'other_user' => [
                    'id' => $target->id,
                    'name' => $target->name,
                    'role' => $target->role,
                    'avatar_color' => $target->avatar_color,
                    'is_online' => $target->isOnline(),
                ],
                'participants_count' => 2,
            ],
        ]);
    }

    /**
     * List user conversations (1:1 and groups) with latest message and unread count.
     */
    public function conversations(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::with([
            'userOne:id,name,role,avatar_color',
            'userTwo:id,name,role,avatar_color',
            'participants:id,name,role,avatar_color',
            'messages' => function ($q) {
                $q->latest()->take(1);
            }
        ])
        ->where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)
              ->orWhere('user_two_id', $userId)
              ->orWhereHas('participants', function ($p) use ($userId) {
                  $p->where('users.id', $userId)->whereNull('conversation_participants.left_at');
              });
        })
        ->orderByDesc('last_message_at')
        ->get();

        // Calculate unread counts per conversation
        $unreadCounts = Message::select('conversation_id', DB::raw('count(*) as count'))
            ->whereIn('conversation_id', $conversations->pluck('id'))
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->groupBy('conversation_id')
            ->pluck('count', 'conversation_id')
            ->all();

        $formatted = $conversations->map(function ($c) use ($userId, $unreadCounts) {
            $lastMsg = $c->messages->first();
            $otherUser = $c->is_group ? null : $c->otherParticipant($userId);

            return [
                'id' => $c->id,
                'is_group' => $c->is_group,
                'name' => $c->is_group ? $c->name : ($otherUser ? $otherUser->name : 'Chat'),
                'unread_count' => $unreadCounts[$c->id] ?? 0,
                'other_user' => $otherUser ? [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'avatar_color' => $otherUser->avatar_color,
                    'is_online' => $otherUser->isOnline(),
                    'role' => $otherUser->role,
                ] : null,
                'participants_count' => $c->is_group ? $c->participants->count() : 2,
                'last_message' => $lastMsg ? [
                    'id' => $lastMsg->id,
                    'sender_id' => $lastMsg->user_id,
                    'body' => $lastMsg->body,
                    'has_attachment' => !empty($lastMsg->attachment_path),
                    'attachment_name' => $lastMsg->attachment_name,
                    'is_image' => $lastMsg->isImageAttachment(),
                    'created_at' => $lastMsg->created_at ? $lastMsg->created_at->toIso8601String() : null,
                ] : null,
                'last_message_at' => $c->last_message_at ? $c->last_message_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'conversations' => $formatted,
        ]);
    }

    /**
     * Get messages for a conversation.
     */
    public function messages(Request $request, $conversationId)
    {
        $userId = $request->user()->id;
        $conversation = Conversation::with(['participants', 'userOne', 'userTwo'])->findOrFail($conversationId);

        if (!$conversation->hasParticipant($userId)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $messages = Message::with(['user:id,name,role,avatar_color', 'reactions.user:id,name'])
            ->where('conversation_id', $conversationId)
            ->latest()
            ->paginate(50);

        // Mark unread messages from others as read and broadcast MessageRead
        $unreadMessages = Message::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->select('id', 'user_id')
            ->get();

        if ($unreadMessages->isNotEmpty()) {
            Message::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $bySender = $unreadMessages->groupBy('user_id');
            foreach ($bySender as $senderId => $msgs) {
                try {
                    broadcast(new MessageRead(
                        recipients: (int) $senderId,
                        conversationId: $conversation->id,
                        messageIds: $msgs->pluck('id')->all(),
                        readerId: $userId,
                    ));
                } catch (\Exception $e) {}
            }
        }

        $formattedMessages = collect($messages->items())->map(fn ($m) => $this->formatMessage($m));

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'name' => $conversation->is_group ? $conversation->name : optional($conversation->otherParticipant($userId))->name,
                'is_group' => $conversation->is_group,
                'participants_count' => $conversation->is_group ? $conversation->participants->count() : 2,
            ],
            'messages' => $formattedMessages->reverse()->values(),
            'current_page' => $messages->currentPage(),
            'last_page' => $messages->lastPage(),
        ]);
    }

    /**
     * Send a message with optional file/image attachment.
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $userId = $request->user()->id;
        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->hasParticipant($userId)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'nullable|string',
            'attachment' => 'nullable|file|max:51200', // 50 MB
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $body = $request->input('body');
        $file = $request->file('attachment');

        if (empty($body) && !$file) {
            return response()->json(['success' => false, 'message' => 'Message or attachment required'], 422);
        }

        $attachmentPath = null;
        $attachmentType = null;
        $attachmentName = null;
        $attachmentSize = null;

        if ($file && $file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension() ?: 'bin';
            $safeName = time() . '_' . Str::random(8) . '.' . $ext;
            $attachmentPath = $file->storeAs('chat', $safeName, 'public');
            $attachmentType = $file->getMimeType();
            $attachmentName = $originalName;
            $attachmentSize = $file->getSize();
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'body' => $body ?: '',
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'attachment_name' => $attachmentName,
            'attachment_size' => $attachmentSize,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $formatted = $this->formatMessage($message->load(['user:id,name,role,avatar_color', 'reactions']));

        // Realtime Broadcast to Web & other participants
        try {
            $recipients = $conversation->recipientIds($userId);
            if (!empty($recipients)) {
                broadcast(new MessageSent($message, $recipients));
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => $formatted,
        ]);
    }

    /**
     * Edit a message.
     */
    public function updateMessage(Request $request, $id)
    {
        $userId = $request->user()->id;
        $message = Message::with('conversation')->findOrFail($id);

        if ($message->user_id !== $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error'], 422);
        }

        $message->update([
            'body' => $request->body,
            'edited_at' => now(),
        ]);

        $formatted = $this->formatMessage($message->load(['user', 'reactions']));

        try {
            $recipients = $message->conversation->recipientIds($userId);
            broadcast(new MessageEdited($message, $recipients));
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => $formatted,
        ]);
    }

    /**
     * Delete a message.
     */
    public function deleteMessage(Request $request, $id)
    {
        $userId = $request->user()->id;
        $message = Message::with('conversation')->findOrFail($id);

        if ($message->user_id !== $userId && !$request->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $conversationId = $message->conversation_id;
        $recipients = $message->conversation->recipientIds($userId);

        $message->delete();

        try {
            broadcast(new MessageDeleted($id, $conversationId, $recipients));
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Message deleted',
        ]);
    }

    /**
     * React to a message.
     */
    public function react(Request $request, $messageId)
    {
        $userId = $request->user()->id;
        $message = Message::with('conversation')->findOrFail($messageId);

        $validator = Validator::make($request->all(), [
            'emoji' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error'], 422);
        }

        $existing = MessageReaction::where('message_id', $message->id)
            ->where('user_id', $userId)
            ->where('emoji', $request->emoji)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id' => $userId,
                'emoji' => $request->emoji,
            ]);
            $action = 'added';
        }

        try {
            $recipients = $message->conversation->recipientIds($userId);
            broadcast(new MessageReacted($message, $request->emoji, $action, $userId, $recipients));
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'action' => $action,
            'reactions' => MessageReaction::where('message_id', $message->id)->with('user:id,name')->get(),
        ]);
    }

    /**
     * Create a group chat.
     */
    public function createGroup(Request $request)
    {
        $userId = $request->user()->id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $memberIds = array_unique(array_merge([$userId], $request->member_ids));

        $conversation = Conversation::create([
            'is_group' => true,
            'name' => $request->name,
            'created_by' => $userId,
            'last_message_at' => now(),
        ]);

        foreach ($memberIds as $mId) {
            DB::table('conversation_participants')->insert([
                'conversation_id' => $conversation->id,
                'user_id' => $mId,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add system welcome message
        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'body' => $request->user()->name . ' created group "' . $request->name . '"',
        ]);

        try {
            broadcast(new GroupMembersChanged($conversation, $memberIds, 'added'));
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'name' => $conversation->name,
                'is_group' => true,
                'participants_count' => count($memberIds),
            ],
        ]);
    }

    /**
     * Format a Message model for mobile JSON.
     */
    protected function formatMessage(Message $message): array
    {
        $attachmentUrl = null;
        if ($message->attachment_path) {
            $attachmentUrl = $message->attachmentUrl();
        }

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->user_id,
            'sender_name' => $message->user?->name ?? 'User',
            'sender_avatar' => $message->user?->avatar_color,
            'sender_role' => $message->user?->role,
            'body' => $message->body,
            'attachment_url' => $attachmentUrl,
            'attachment_name' => $message->attachment_name,
            'attachment_type' => $message->attachment_type,
            'attachment_size' => $message->attachment_size,
            'is_image' => $message->isImageAttachment(),
            'read_at' => $message->read_at ? $message->read_at->toIso8601String() : null,
            'edited_at' => $message->edited_at ? $message->edited_at->toIso8601String() : null,
            'created_at' => $message->created_at ? $message->created_at->toIso8601String() : null,
            'reactions' => $message->reactions->map(fn ($r) => [
                'id' => $r->id,
                'user_id' => $r->user_id,
                'emoji' => $r->emoji,
                'user_name' => $r->user?->name,
            ])->values(),
        ];
    }
}
