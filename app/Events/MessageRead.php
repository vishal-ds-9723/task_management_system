<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** Senders to notify on their private channels */
    public array $recipientIds;
    public int $conversationId;
    public array $messageIds;
    public int $readerId;

    public function __construct(int|array $recipients, int $conversationId, array $messageIds, int $readerId)
    {
        $this->recipientIds   = is_array($recipients) ? array_values($recipients) : [$recipients];
        $this->conversationId = $conversationId;
        $this->messageIds     = $messageIds;
        $this->readerId       = $readerId;
    }

    public function broadcastOn(): array
    {
        return array_map(fn ($id) => new PrivateChannel('chat.' . $id), $this->recipientIds);
    }

    public function broadcastAs(): string
    {
        return 'message.read';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'message_ids'     => $this->messageIds,
            'reader_id'       => $this->readerId,
        ];
    }
}
