<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyWorkLogEntry extends Model
{
    public const WORK_TYPES = [
        // Creative & Graphics Designing
        'graphics_design'    => 'Graphics Design',
        'logo_design'        => 'Logo Design',
        'product_design'     => 'Product Design / UI-UX',
        'banner_flyer'       => 'Banner / Flyer / Print',
        'branding'           => 'Branding & Identity',
        'video_motion'       => 'Video / Motion / Reel',

        // Website Development & Technical
        'development'        => 'Website / Software Development',
        'bug_fix'            => 'Bug Fix / Debugging',
        'server_maintenance' => 'Server Maintenance / DevOps',
        'deployment'         => 'Deployment & Hosting',
        'testing'            => 'Testing & QA',

        // Collaboration, Research & Operations
        'revision'           => 'Client Revision',
        'research'           => 'Research & Planning',
        'meeting'            => 'Meeting & Discussion',
        'support'            => 'Technical Support',
        'other'              => 'Other Work',
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
