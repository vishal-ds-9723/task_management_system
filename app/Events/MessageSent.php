<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public array $recipientIds;

    /**
     * @param  Message  $message
     * @param  int|array  $recipients  single user id or list of ids (for groups)
     */
    public function __construct(Message $message, int|array $recipients)
    {
        $this->message      = $message->load('user:id,name,role,avatar_color');
        $this->recipientIds = is_array($recipients) ? array_values($recipients) : [$recipients];
    }

    public function broadcastOn(): array
    {
        return array_map(fn ($id) => new PrivateChannel('chat.' . $id), $this->recipientIds);
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id'         => $this->message->user_id,
            'user_name'       => $this->message->user?->name,
            'user_role'       => $this->message->user?->role,
            'body'            => $this->message->body,
            'created_at'      => $this->message->created_at?->toIso8601String(),
            'attachment'      => $this->message->attachment_path ? [
                'url'  => $this->message->attachmentUrl(),
                'type' => $this->message->attachment_type,
                'name' => $this->message->attachment_name,
                'size' => $this->message->attachment_size,
                'is_image' => $this->message->isImageAttachment(),
            ] : null,
        ];
    }
}
