<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id', 'user_id', 'body', 'read_at',
        'attachment_path', 'attachment_type', 'attachment_name', 'attachment_size',
        'edited_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'edited_at' => 'datetime',
        'attachment_size' => 'integer',
    ];

    public function isImageAttachment(): bool
    {
        return $this->attachment_type && str_starts_with($this->attachment_type, 'image/');
    }

    public function attachmentUrl(): ?string
    {
        if (!$this->attachment_path) return null;
        $cleanPath = str_replace('\\', '/', $this->attachment_path);
        if (str_starts_with($cleanPath, 'http://') || str_starts_with($cleanPath, 'https://')) {
            return $cleanPath;
        }
        if (request()) {
            return request()->root() . '/api/v1/media/file/' . ltrim($cleanPath, '/');
        }
        return url('/api/v1/media/file/' . ltrim($cleanPath, '/'));
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }
}
