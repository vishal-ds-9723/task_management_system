<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    protected $fillable = [
        'name', 'email', 'role', 'phone', 'avatar_url', 'avatar_color',
        'bio', 'status',
    ];

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_employees')
            ->withPivot('role', 'estimated_hours', 'actual_hours', 'assigned_date', 'completed_date')
            ->withTimestamps();
    }

    public function getInitialAttribute(): string
    {
        $parts = explode(' ', $this->name);
        return strtoupper(substr($parts[0], 0, 1)) . (isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '');
    }

    public function getInitialsAttribute(): string
    {
        return $this->getInitialAttribute();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function getTotalHoursForTaskAttribute($taskId): int
    {
        return $this->tasks()
            ->wherePivot('task_id', $taskId)
            ->sum('task_employees.estimated_hours') ?? 0;
    }

    public function getCurrentWorkloadAttribute(): int
    {
        return $this->tasks()
            ->wherePivotNull('completed_date')
            ->sum('task_employees.estimated_hours') ?? 0;
    }
}
