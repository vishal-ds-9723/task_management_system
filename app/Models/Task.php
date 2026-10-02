<?php

namespace App\Models;

use App\Support\SocialRegistry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Task extends Model
{
    use HasFactory;

    public const STATUSES = ['todo', 'inprogress', 'review', 'pending_approval', 'completed', 'published', 'on_hold'];
    public const ACTIVE_STATUSES = ['todo', 'inprogress', 'review', 'pending_approval'];
    public const PLATFORMS = SocialRegistry::TASK_PLATFORMS;
    public const SOCIAL_TYPES = ['reel', 'post', 'story', 'video', 'carousel'];
    public const DESIGN_TYPES = ['brochure', 'banner', 'flyer'];
    public const URGENT_TYPES = ['reel', 'post', 'story', 'video', 'carousel', 'brochure', 'banner', 'flyer'];

    protected $fillable = [
        'title',
        'client_id',
        'client_social_media_link_id',
        'selected_social_media_link_ids',
        'festival_selection_id',
        'assigned_to',
        'created_by',
        'type',
        'priority',
        'status',
        'is_paused',
        'paused_at',
        'pause_reason',
        'total_paused_seconds',
        'is_urgent_task',
        'urgent_requested_by',
        'platform',
        'brief',
        'logo_received',
        'images_received',
        'content_received',
        'tech_stack',
        'project_notes',
        'project_start_date',
        'launch_date',
        'dev_deadline',
        'preferred_tech',
        'modules',
        'business_type',
        'domain_purchased',
        'hosting_access',
        'started_at',
        'submitted_at',
        'completed_at',
        'admin_approved_by',
        'admin_approved_at',
        'admin_approval_status',
        'caption',
        'hashtags',
        'reference_links',
        'deadline',
        'design_deadline',
        'post_date',
        'media_path',
        'caption_saved_at',
        'dev_submission_link',
        'dev_submission_notes',
        'has_wireframes',
        'on_hold_reason',
        'on_hold_since',
        'status_before_hold',
        'parent_task_id',
    ];

    protected $casts = [
        'deadline'            => 'date',
        'design_deadline'     => 'date',
        'post_date'           => 'date',
        'project_start_date'  => 'date',
        'launch_date'         => 'date',
        'dev_deadline'        => 'date',
        'started_at'          => 'datetime',
        'submitted_at'        => 'datetime',
        'completed_at'        => 'datetime',
        'admin_approved_at'   => 'datetime',
        'paused_at'           => 'datetime',
        'caption_saved_at'    => 'datetime',
        'on_hold_since'       => 'datetime',
        'reference_links'     => 'array',
        'platform'            => 'array',
        'selected_social_media_link_ids' => 'array',
        'logo_received'       => 'boolean',
        'images_received'     => 'boolean',
        'content_received'    => 'boolean',
        'domain_purchased'    => 'boolean',
        'hosting_access'      => 'boolean',
        'is_paused'           => 'boolean',
        'is_urgent_task'      => 'boolean',
        'total_paused_seconds' => 'integer',
        'revision_count'      => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function socialMediaLink()
    {
        return $this->belongsTo(ClientSocialMediaLink::class, 'client_social_media_link_id');
    }




    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function childTasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function festivalSelection()
    {
        return $this->belongsTo(FestivalSelection::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function socialMediaPosts()
    {
        return $this->hasMany(SocialMediaPost::class);
    }

    /**
     * Calculate design_deadline from deadline and post_date.
     * Rule: deadline minus 5 days, but never earlier than post_date.
     * Returns a Carbon instance or null if deadline is absent.
     */
    public static function calculateDesignDeadline(?string $deadline, ?string $postDate): ?\Carbon\Carbon
    {
        if (!$deadline) return null;

        $deadlineCarbon  = \Carbon\Carbon::parse($deadline);
        $designDeadline  = $deadlineCarbon->copy()->subDays(5);

        if ($postDate) {
            $postDateCarbon = \Carbon\Carbon::parse($postDate);
            if ($designDeadline->lt($postDateCarbon)) {
                $designDeadline = $postDateCarbon->copy();
            }
        }

        return $designDeadline;
    }

    /**
     * Sync task status to 'published' when all platforms have proof links.
     * Call this after a SocialMediaPost is created.
     */
    public function syncPublishingStatus(): void
    {
        if ($this->is_urgent_task) {
            return;
        }

        if ($this->status === 'completed' && $this->isFullyPublished()) {
            $this->update(['status' => 'published']);
        }
    }

    /**
     * Check if all platforms have proof links uploaded.
     */
    public function isFullyPublished(): bool
    {
        $platforms = $this->normalizedPlatforms();
        if (empty($platforms)) return false;

        $postedPlatforms = $this->socialMediaPosts()->pluck('platform')->toArray();
        foreach ($platforms as $p) {
            if (!in_array($p, $postedPlatforms)) return false;
        }
        return true;
    }

    /**
     * Get publishing progress: posted / total platforms.
     */
    public function getPublishingProgress(): array
    {
        $total = count($this->normalizedPlatforms());
        $posted = $this->socialMediaPosts()->count();
        return ['posted' => $posted, 'total' => $total];
    }

    public static function isSocialType(?string $type): bool
    {
        return in_array((string) $type, self::SOCIAL_TYPES, true);
    }

    public static function isDesignType(?string $type): bool
    {
        return in_array((string) $type, self::DESIGN_TYPES, true);
    }

    public static function isUrgentType(?string $type): bool
    {
        return in_array((string) $type, self::URGENT_TYPES, true);
    }

    private function normalizedPlatforms(): array
    {
        if (is_array($this->platform)) {
            return array_values(array_filter($this->platform));
        }

        if (!is_string($this->platform) || $this->platform === '') {
            return [];
        }

        $decoded = json_decode($this->platform, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('strval', $decoded)));
            }

            if (is_string($decoded) && $decoded !== '') {
                return [$decoded];
            }
        }

        return [trim($this->platform, '"')];
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function pauses()
    {
        return $this->hasMany(TaskPause::class)->latest();
    }

    /**
     * Check if the task is currently paused.
     */
    public function isPaused(): bool
    {
        return (bool) $this->is_paused;
    }

    /**
     * Get the effective working time in seconds (total elapsed minus paused time).
     */
    public function getEffectiveWorkingTime(): int
    {
        if (!$this->started_at) return 0;

        $end = $this->completed_at ?? now();
        $totalElapsed = $this->started_at->diffInSeconds($end);

        // Add current pause duration if currently paused
        $currentPause = 0;
        if ($this->is_paused && $this->paused_at) {
            $currentPause = $this->paused_at->diffInSeconds(now());
        }

        return max(0, $totalElapsed - $this->total_paused_seconds - $currentPause);
    }

    /**
     * Format effective working time as human-readable string.
     */
    public function getFormattedWorkingTimeAttribute(): string
    {
        $seconds = $this->getEffectiveWorkingTime();
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) return $hours . 'h ' . $minutes . 'm';
        return $minutes . 'm';
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'task_employees')
            ->withPivot('role', 'estimated_hours', 'actual_hours', 'assigned_date', 'completed_date')
            ->withTimestamps();
    }

    public function isOverdue(): bool
    {
        return $this->deadline
            && Carbon::parse($this->deadline)->isPast()
            && !in_array($this->status, ['completed', 'published']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function getTypeTagClassAttribute(): string
    {
        return match($this->type) {
            'reel'     => 'tag-orange',
            'post'     => 'tag-blue',
            'story'    => 'tag-teal',
            'video'    => 'tag-purple',
            'carousel' => 'tag-pink',
            'website'  => 'tag-indigo',
            'software' => 'tag-purple',
            'brochure' => 'tag-amber',
            'flyer'    => 'tag-red',
            'others'   => 'tag-blue',
            default    => 'tag-blue',
        };
    }

    public function getStatusClassAttribute(): string
    {
        if ($this->is_paused) return 's-paused';

        return match($this->status) {
            'todo'              => 's-todo',
            'inprogress'        => 's-prog',
            'review'            => 's-review',
            'pending_approval'  => 's-review',
            'completed'         => 's-done',
            'published'         => 's-published',
            'on_hold'           => 's-paused',
            default             => 's-todo',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_paused) return 'Paused';

        return match($this->status) {
            'todo'              => 'To Do',
            'inprogress'        => 'In Progress',
            'review'            => 'Review',
            'pending_approval'  => 'Pending Admin Approval',
            'completed'         => 'Completed',
            'published'         => 'Published',
            'on_hold'           => 'On Hold',
            default             => 'To Do',
        };
    }

    public function getPriorityTagAttribute(): string
    {
        return match($this->priority) {
            'urgent' => '<span class="tag tag-red">🔴 Urgent</span>',
            'high'   => '<span class="tag tag-orange">🟠 High</span>',
            default  => '',
        };
    }
}
