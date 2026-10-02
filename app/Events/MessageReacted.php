<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReacted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $recipientIds;
    public int $messageId;
    public int $conversationId;
    public array $counts;
    public int $actorId;
    public string $emoji;
    public bool $added;

    public function __construct(
        int|array $recipients,
        int $messageId,
        int $conversationId,
        array $counts,
        int $actorId,
        string $emoji,
        bool $added,
    ) {
        $this->recipientIds   = is_array($recipients) ? array_values($recipients) : [$recipients];
        $this->messageId      = $messageId;
        $this->conversationId = $conversationId;
        $this->counts         = $counts;
        $this->actorId        = $actorId;
        $this->emoji          = $emoji;
        $this->added          = $added;
    }

    public function broadcastOn(): array
    {
        return array_map(fn ($id) => new PrivateChannel('chat.' . $id), $this->recipientIds);
    }

    public function broadcastAs(): string
    {
        return 'message.reacted';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id'      => $this->messageId,
            'conversation_id' => $this->conversationId,
            'counts'          => $this->counts,
            'actor_id'        => $this->actorId,
            'emoji'           => $this->emoji,
            'added'           => $this->added,
        ];
    }
}
