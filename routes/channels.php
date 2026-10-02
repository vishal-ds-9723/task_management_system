<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private chat channel — only the user themselves can subscribe to chat.{userId}
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Per-conversation private channel — used for typing whispers between the two participants only
Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    $convo = \App\Models\Conversation::find($conversationId);
    return $convo && $convo->hasParticipant((int) $user->id);
});

// Presence channel — for online status. Returns the user's identity to other subscribers.
Broadcast::channel('chat.online', function ($user) {
    return [
        'id'           => (int) $user->id,
        'name'         => $user->name,
        'role'         => $user->role,
        'avatar_color' => $user->avatar_color ?? '#4F6DF0',
    ];
});
