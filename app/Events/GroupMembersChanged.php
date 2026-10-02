<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMembersChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $recipientIds;
    public int $conversationId;
    public array $added;       // [{id, name, role, avatar_color}]
    public array $removedIds;  // [int]
    public int $totalMembers;

    public function __construct(
        int|array $recipients,
        int $conversationId,
        array $added,
        array $removedIds,
        int $totalMembers,
    ) {
        $this->recipientIds   = is_array($recipients) ? array_values($recipients) : [$recipients];
        $this->conversationId = $conversationId;
        $this->added          = $added;
        $this->removedIds     = $removedIds;
        $this->totalMembers   = $totalMembers;
    }

    public function broadcastOn(): array
    {
        return array_map(fn ($id) => new PrivateChannel('chat.' . $id), $this->recipientIds);
    }

    public function broadcastAs(): string
    {
        return 'group.members.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'added'           => $this->added,
            'removed_ids'     => $this->removedIds,
            'total_members'   => $this->totalMembers,
        ];
    }
}
