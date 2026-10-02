<?php

namespace App\Models;

use App\Support\SocialRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SocialMediaPost extends Model
{
    /**
     * Platforms that use screenshot/image proof instead of URLs.
     */
    public const IMAGE_PROOF_PLATFORMS = SocialRegistry::IMAGE_PROOF_PLATFORMS;

    protected $fillable = [
        'task_id',
        'client_social_media_link_id',
        'platform',
        'post_type',
        'post_url',
        'proof_image',
        'posted_at',
        'posted_by',
    ];

    protected $casts = [
        'posted_at' => 'date',
    ];

    protected $appends = ['platform_icon', 'platform_label', 'platform_color', 'proof_image_url'];

    public function getProofImageUrlAttribute(): ?string
    {
        if (!$this->proof_image) return null;
        if (str_starts_with($this->proof_image, 'http://') || str_starts_with($this->proof_image, 'https://')) {
            return $this->proof_image;
        }
        if (request()) {
            return request()->root() . '/storage/' . ltrim($this->proof_image, '/');
        }
        return asset('storage/' . ltrim($this->proof_image, '/'));
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function socialMediaLink(): BelongsTo
    {
        return $this->belongsTo(ClientSocialMediaLink::class, 'client_social_media_link_id');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(SocialMediaPostMetric::class)
            ->orderByDesc('snapshot_date')
            ->orderByDesc('id');
    }

    public function latestMetric(): HasOne
    {
        return $this->hasOne(SocialMediaPostMetric::class)->latestOfMany('snapshot_date');
    }

    public function latestOrganicMetric(): HasOne
    {
        return $this->hasOne(SocialMediaPostMetric::class)->where('paid_promotion', false)->latestOfMany('snapshot_date');
    }

    public function latestPaidMetric(): HasOne
    {
        return $this->hasOne(SocialMediaPostMetric::class)->where('paid_promotion', true)->latestOfMany('snapshot_date');
    }

    public function getPlatformIconAttribute(): string
    {
        return match($this->platform) {
            'instagram' => '📸',
            'facebook'  => '📘',
            'linkedin'  => '💼',
            'twitter'   => '🐦',
            'tiktok'    => '🎵',
            'youtube'   => '▶️',
            default     => '🌐',
        };
    }

    public function getPlatformLabelAttribute(): string
    {
        return ucfirst($this->platform);
    }

    public function getPlatformColorAttribute(): string
    {
        return match($this->platform) {
            'instagram' => '#E1306C',
            'facebook'  => '#1877F2',
            'linkedin'  => '#0A66C2',
            'twitter'   => '#1DA1F2',
            'tiktok'    => '#000000',
            'youtube'   => '#FF0000',
            default     => '#6B7280',
        };
    }
}
