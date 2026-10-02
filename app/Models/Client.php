<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Client extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'name', 'category', 'emoji', 'color', 'logo', 'cta_video', 'footer_image', 'is_active',
        'website', 'instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'tiktok',
        'notes', 'contact_person', 'contact_email', 'contact_phone',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'client_user')->withTimestamps();
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class)
            ->orderByDesc('is_primary')
            ->orderBy('position')
            ->orderBy('id');
    }

    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    public function activeTasks()
    {
        return $this->tasks()->whereIn('status', Task::ACTIVE_STATUSES);
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class);
    }

    public function festivalSelections()
    {
        return $this->hasMany(FestivalSelection::class);
    }

    public function socialMediaLinks()
    {
        return $this->hasMany(ClientSocialMediaLink::class);
    }

    public function monthlySchedules()
    {
        return $this->hasMany(ClientMonthlySchedule::class);
    }

    public function visits()
    {
        return $this->hasMany(ClientVisit::class)->latest('visit_date');
    }

    /**
     * Get social media links grouped by platform
     */
    public function getSocialLinksByPlatform($platform = null)
    {
        $query = $this->socialMediaLinks();

        if ($platform) {
            $query->where('platform', $platform);
        }

        return $query->orderBy('is_primary', 'desc')->orderBy('created_at')->get();
    }

    /**
     * Get primary social media link for a platform
     */
    public function getPrimarySocialLink($platform)
    {
        return $this->socialMediaLinks()
            ->where('platform', $platform)
            ->where('is_primary', true)
            ->first() ??
            $this->socialMediaLinks()
            ->where('platform', $platform)
            ->first();
    }
}
