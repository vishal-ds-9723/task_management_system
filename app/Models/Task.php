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
    public const DESIGN_TYPES = ['brochure', 'banner', 'flyer', 'logo', 'product_design', 'branding'];
    public const URGENT_TYPES = ['reel', 'post', 'story', 'video', 'carousel', 'brochure', 'banner', 'flyer'];
    public const DEV_TYPES = ['website', 'software', 'maintenance'];
    /** Roles allowed to own a development project (designers also build sites in this firm). */
    public const DEV_ASSIGNEE_ROLES = ['developer', 'designer', 'admin', 'manager'];
    public const BUSINESS_TYPES = [
        'business_website' => 'Business website',
        'ecommerce'        => 'E-commerce store',
        'landing_page'     => 'Landing page',
        'portfolio'        => 'Portfolio',
        'blog'             => 'Blog / Magazine',
        'custom_software'  => 'Custom software',
        'etc'              => 'Other',
    ];
    /** Client-side handover items tracked on development projects. */
    public const PROJECT_ASSETS = [
        'logo_received'    => 'Logo',
        'images_received'  => 'Images',
        'content_received' => 'Content / copy',
        'domain_purchased' => 'Domain',
        'hosting_access'   => 'Hosting access',
    ];

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
        // Without this, revision requests silently never increased the counter
        'revision_count',
    ];

    protected function casts(): array
    {
        return [
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
    }

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

    public function workLogEntries()
    {
        return $this->hasMany(DailyWorkLogEntry::class);
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
     * By default, design deadline matches the design task deadline (3 days before post_date).
     * Returns a Carbon instance or null if both are absent.
     */
    public static function calculateDesignDeadline(?string $deadline, ?string $postDate): ?\Carbon\Carbon
    {
        if ($deadline) {
            return \Carbon\Carbon::parse($deadline);
        }

        if ($postDate) {
            $postDateCarbon = \Carbon\Carbon::parse($postDate)->startOfDay();
            $designDeadline = $postDateCarbon->copy()->subDays(3);
            if ($designDeadline->lt(\Carbon\Carbon::today())) {
                $designDeadline = \Carbon\Carbon::today();
            }
            return $designDeadline;
        }

        return null;
    }

    /**
     * Sync task status to 'published' when all platforms have proof links.
     * Call this after a SocialMediaPost is created.
     */
    public function syncPublishingStatus(): void
    {
        // Urgent tasks follow the same publishing step as everything else
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

    /** Roles that work in the strategist panel. */
    public const STRATEGIST_PANEL_ROLES = ['strategist', 'manager', 'editor', 'content_writer'];
    /** Roles whose tasks the strategist panel tracks (admins hand work to strategists too). */
    public const STRATEGIST_ROLES = [...self::STRATEGIST_PANEL_ROLES, 'admin'];

    /**
     * Tasks the strategist team is responsible for: anything created by the given roles,
     * plus urgent tasks designers raise themselves (those go through the same review/approval).
     */
    public function scopeStrategistVisible($query, array $creatorRoles = self::STRATEGIST_ROLES)
    {
        return $query->where(fn ($q) => $q
            ->whereHas('creator', fn ($c) => $c->whereIn('role', $creatorRoles))
            ->orWhere('tasks.is_urgent_task', true));
    }

    /**
     * Tasks that belong in the publishing queue. Urgent tasks join once they have passed
     * admin approval; older urgent tasks were delivered straight to the client and never will.
     */
    public function scopePublishable($query)
    {
        return $query->where(fn ($q) => $q
            ->where('tasks.is_urgent_task', false)
            ->orWhere('tasks.admin_approval_status', 'approved'));
    }

    public function inPublishingFlow(): bool
    {
        return !$this->is_urgent_task || $this->admin_approval_status === 'approved';
    }

    /** Captions and hashtags only matter for content that gets posted on social media. */
    public static function needsCaption(?string $type): bool
    {
        return self::isSocialType($type);
    }

    public static function isDevType(?string $type): bool
    {
        return in_array(strtolower((string) $type), self::DEV_TYPES, true);
    }

    /**
     * Validation rule for assigned_to on development projects.
     */
    public static function devAssigneeRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if (!$value) {
                return;
            }
            $user = User::find($value);
            if ($user && !$user->hasAnyRole(self::DEV_ASSIGNEE_ROLES)) {
                $fail('Website and software projects can only be assigned to a developer or designer.');
            }
        };
    }

    /**
     * The page a user should land on when notified about this task.
     */
    public function linkFor(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.tasks.show', $this);
        }

        if (self::isDevType($this->type) && $user->isDeveloper()) {
            return route('developer.tasks', ['task' => $this->id]);
        }

        if ((self::isDesignType($this->type) || self::isSocialType($this->type)) && $user->isDesigner()) {
            return route('designer.tasks.show', $this);
        }

        if ($user->isDeveloper()) {
            return route('developer.tasks', ['task' => $this->id]);
        }

        if ($user->isDesigner()) {
            return route('designer.tasks.show', $this);
        }

        return route('strategist.tracking', ['task' => $this->id]);
    }

    public static function isDesignType(?string $type): bool
    {
        return in_array((string) $type, self::DESIGN_TYPES, true);
    }

    public static function isUrgentType(?string $type): bool
    {
        return in_array((string) $type, self::URGENT_TYPES, true);
    }

    /**
     * The task's target platforms as a clean list, whatever format they were stored in.
     */
    public function platformList(): array
    {
        return $this->normalizedPlatforms();
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
            'reel'            => 'tag-orange',
            'post'            => 'tag-blue',
            'story'           => 'tag-teal',
            'video'           => 'tag-purple',
            'carousel'        => 'tag-pink',
            'website'         => 'tag-teal',
            'software'        => 'tag-purple',
            'maintenance', 'server_maintenance' => 'tag-blue',
            'brochure'        => 'tag-amber',
            'banner'          => 'tag-red',
            'flyer'           => 'tag-red',
            'logo'            => 'tag-yellow',
            'product_design'  => 'tag-purple',
            'branding'        => 'tag-pink',
            'others'          => 'tag-blue',
            default           => 'tag-blue',
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
