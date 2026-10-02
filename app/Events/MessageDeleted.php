<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $messageId;
    public int $conversationId;
    public array $recipientIds;

    public function __construct(int $messageId, int $conversationId, int|array $recipients)
    {
        $this->messageId      = $messageId;
        $this->conversationId = $conversationId;
        $this->recipientIds   = is_array($recipients) ? array_values($recipients) : [$recipients];
    }

    public function broadcastOn(): array
    {
        return array_map(fn ($id) => new PrivateChannel('chat.' . $id), $this->recipientIds);
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->messageId,
            'conversation_id' => $this->conversationId,
        ];
    }
}
