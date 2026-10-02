<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ALLOWED_ROLES = [
        'admin',
        'strategist',
        'designer',
        'developer',
        'client',
        'manager',
        'editor',
        'content_writer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'additional_roles',
        'can_manage_social_metrics',
        'client_id',
        'avatar_color',
        'last_active_at',
        'current_url',
        'google_calendar_token',
        'google_calendar_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_calendar_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_active_at' => 'datetime',
            'can_manage_social_metrics' => 'boolean',
            'additional_roles' => 'array',
        ];
    }

    public function getAllRoles(): array
    {
        $primary = $this->role ? [$this->role] : [];
        $additional = is_array($this->additional_roles) ? $this->additional_roles : [];
        return array_values(array_unique(array_filter(array_merge($primary, $additional))));
    }

    public function hasAnyRole(array $roles): bool
    {
        $userRoles = $this->getAllRoles();
        return !empty(array_intersect($roles, $userRoles));
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->hasAnyRole($roles);
    }

    public function isOnline(): bool
    {
        return $this->last_active_at && $this->last_active_at->greaterThan(now()->subMinutes(5));
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isStrategist(): bool
    {
        return $this->hasAnyRole(['strategist', 'manager', 'editor', 'content_writer']);
    }

    public function isDesigner(): bool
    {
        return $this->hasRole('designer');
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isDeveloper(): bool
    {
        return $this->hasRole('developer');
    }

    public function canManageSocialMetrics(): bool
    {
        return (bool) $this->can_manage_social_metrics;
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_user')->withTimestamps();
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function customNotifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function unreadCustomNotifications()
    {
        return $this->hasMany(Notification::class)->whereNull('read_at')->latest();
    }

    public function visibleCustomNotifications()
    {
        return $this->applyClientNotificationFilter($this->customNotifications());
    }

    public function visibleUnreadCustomNotifications()
    {
        return $this->applyClientNotificationFilter($this->unreadCustomNotifications());
    }

    protected function applyClientNotificationFilter($query)
    {
        if (! $this->isClient()) {
            return $query;
        }

        return $query
            ->where(function ($builder) {
                $builder->where('link', 'not like', '%/chat%')
                    ->orWhereNull('link');
            })
            ->where(function ($builder) {
                $builder->where('icon', '!=', '💬')
                    ->orWhereNull('icon');
            });
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? '', 0, 1) ?: 'U');
    }

    public function actionItemsAssigned()
    {
        return $this->hasMany(ActionItem::class, 'assigned_to');
    }

    public function actionItemsCreated()
    {
        return $this->hasMany(ActionItem::class, 'created_by');
    }

    public function developerSkills()
    {
        return $this->hasMany(DeveloperSkill::class);
    }
}
