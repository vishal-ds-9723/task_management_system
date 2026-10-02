<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMediaPostMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_media_post_id',
        'snapshot_date',
        'views',
        'impressions',
        'reach',
        'likes',
        'comments',
        'shares',
        'profile_visits',
        'paid_promotion',
        'ad_spend_inr',
        'target_area',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'views' => 'integer',
        'impressions' => 'integer',
        'reach' => 'integer',
        'likes' => 'integer',
        'comments' => 'integer',
        'shares' => 'integer',
        'profile_visits' => 'integer',
        'paid_promotion' => 'boolean',
        'ad_spend_inr' => 'decimal:2',
    ];

    public function socialMediaPost(): BelongsTo
    {
        return $this->belongsTo(SocialMediaPost::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
