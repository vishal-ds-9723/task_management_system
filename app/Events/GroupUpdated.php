<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $recipientIds;
    public int $conversationId;
    public string $name;

    public function __construct(int|array $recipients, int $conversationId, string $name)
    {
        $this->recipientIds   = is_array($recipients) ? array_values($recipients) : [$recipients];
        $this->conversationId = $conversationId;
        $this->name           = $name;
    }

    public function broadcastOn(): array
    {
        return array_map(fn ($id) => new PrivateChannel('chat.' . $id), $this->recipientIds);
    }

    public function broadcastAs(): string
    {
        return 'group.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'name'            => $this->name,
        ];
    }
}
