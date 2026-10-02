<?php

namespace App\Models;

use App\Services\FcmService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications_custom';

    protected $fillable = [
        'user_id',
        'task_id',
        'icon',
        'title',
        'subtitle',
        'link',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Every in-app notification (task assigned, approval needed, deadline
        // reminder, etc. — created from ~16 call sites app-wide) also fires a
        // push to that user's registered devices, so this is the single place
        // push needs to be wired rather than touching every call site.
        static::created(function (Notification $notification) {
            $user = $notification->user;
            if (!$user) return;

            app(FcmService::class)->sendToUser(
                $user,
                $notification->title ?: 'TMS Notification',
                $notification->subtitle ?: '',
                [
                    'notification_id' => (string) $notification->id,
                    'task_id' => $notification->task_id ? (string) $notification->task_id : '',
                    'link' => $notification->link ?: '',
                ]
            );
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
