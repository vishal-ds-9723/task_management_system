<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyWorkLog extends Model
{
    /** How many days back a user may still create or edit a log. */
    public const EDIT_WINDOW_DAYS = 7;

    protected $fillable = [
        'user_id',
        'log_date',
        'blockers',
        'plan_next',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entries()
    {
        return $this->hasMany(DailyWorkLogEntry::class);
    }

    public function totalHours(): float
    {
        return (float) $this->entries->sum('hours');
    }

    public function isEditable(): bool
    {
        return $this->log_date->betweenIncluded(today()->subDays(self::EDIT_WINDOW_DAYS), today());
    }
}
