<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'client_id', 'assigned_to', 'created_by',
        'due_at', 'priority', 'status', 'completed_at', 'completion_note',
        'follow_up_date', 'recurring', 'parent_id',
        'reminder_at', 'reminder_note',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'follow_up_date' => 'date',
        'reminder_at' => 'datetime',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parent()
    {
        return $this->belongsTo(ActionItem::class, 'parent_id');
    }

    public function followUps()
    {
        return $this->hasMany(ActionItem::class, 'parent_id');
    }

    public function notes()
    {
        return $this->hasMany(ActionItemNote::class);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'done' && $this->due_at && $this->due_at->isPast();
    }

    public function hasActiveReminder(): bool
    {
        return $this->reminder_at !== null && $this->status !== 'done';
    }

    public function isReminderDue(): bool
    {
        return $this->hasActiveReminder() && $this->reminder_at->isPast();
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'done')->where('due_at', '<', now());
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_at', today());
    }

    public function scopeDueSoon($query)
    {
        return $query->where('due_at', '>=', now())->where('due_at', '<=', now()->addHours(24));
    }
}
