<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskPause extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'reason',
        'work_logged',
        'replacement_task_id',
        'resumed_at',
        'duration_seconds',
    ];

    protected $casts = [
        'resumed_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replacementTask()
    {
        return $this->belongsTo(Task::class, 'replacement_task_id');
    }

    /**
     * Check if this pause is still active (not yet resumed).
     */
    public function isActive(): bool
    {
        return is_null($this->resumed_at);
    }
}
