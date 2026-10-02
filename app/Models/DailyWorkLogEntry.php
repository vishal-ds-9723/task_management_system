<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyWorkLogEntry extends Model
{
    public const WORK_TYPES = [
        'development' => 'Development',
        'bug_fix'     => 'Bug Fix',
        'revision'    => 'Revision',
        'testing'     => 'Testing',
        'deployment'  => 'Deployment',
        'research'    => 'Research',
        'meeting'     => 'Meeting',
        'support'     => 'Support',
        'other'       => 'Other',
    ];

    protected $fillable = [
        'daily_work_log_id',
        'task_id',
        'work_type',
        'description',
        'hours',
        'progress',
    ];

    protected $casts = [
        'hours'    => 'float',
        'progress' => 'integer',
    ];

    public function log()
    {
        return $this->belongsTo(DailyWorkLog::class, 'daily_work_log_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function workTypeLabel(): string
    {
        return self::WORK_TYPES[$this->work_type] ?? ucfirst($this->work_type);
    }
}
