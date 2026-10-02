<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_type',
        'client_id',
        'lead_name',
        'company_name',
        'contact_person',
        'contact_email',
        'contact_phone',
        'location',
        'meeting_mode',
        'visited_by',
        'visit_date',
        'purpose',
        'status',
        'summary',
        'discussion_points',
        'action_items',
        'next_follow_up',
        'attachment',
        'created_by',
    ];

    protected $casts = [
        'visit_date'     => 'datetime',
        'next_follow_up' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function visitor()
    {
        return $this->belongsTo(User::class, 'visited_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updates()
    {
        return $this->hasMany(ClientVisitUpdate::class, 'client_visit_id')->latest();
    }

    /**
     * Get primary display name (Client name or Lead name / company)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->visit_type === 'client' && $this->client) {
            return $this->client->name;
        }

        return $this->company_name ?: ($this->lead_name ?: ($this->contact_person ?: 'Unnamed Lead'));
    }

    /**
     * Get primary contact name
     */
    public function getDisplayContactAttribute(): string
    {
        if ($this->visit_type === 'client' && $this->client) {
            return $this->client->contact_person ?: $this->client->name;
        }

        return $this->lead_name ?: ($this->contact_person ?: '—');
    }

    /**
     * Get meeting mode label & icon
     */
    public function getMeetingModeDetailsAttribute(): array
    {
        return match ($this->meeting_mode) {
            'client_office' => ['label' => 'Client Office', 'icon' => 'fa-solid fa-building'],
            'agency_office' => ['label' => 'Agency Office', 'icon' => 'fa-solid fa-house-laptop'],
            'virtual_call'  => ['label' => 'Virtual / Online', 'icon' => 'fa-solid fa-video'],
            'on_site'       => ['label' => 'On-Site / Location', 'icon' => 'fa-solid fa-location-dot'],
            default         => ['label' => 'In-Person', 'icon' => 'fa-solid fa-user-group'],
        };
    }

    /**
     * Get status styling details
     */
    public function getStatusDetailsAttribute(): array
    {
        return match ($this->status) {
            'completed'        => ['label' => 'Completed', 'color' => '#059669', 'bg' => 'rgba(16,185,129,0.12)', 'icon' => 'fa-solid fa-circle-check'],
            'follow_up_needed' => ['label' => 'Follow-up Needed', 'color' => '#D97706', 'bg' => 'rgba(245,158,11,0.12)', 'icon' => 'fa-solid fa-clock-rotate-left'],
            'converted'        => ['label' => 'Converted to Client', 'color' => '#6366F1', 'bg' => 'rgba(99,102,241,0.12)', 'icon' => 'fa-solid fa-award'],
            'cancelled'        => ['label' => 'Cancelled', 'color' => '#DC2626', 'bg' => 'rgba(239,68,68,0.12)', 'icon' => 'fa-solid fa-ban'],
            default            => ['label' => 'Scheduled', 'color' => '#2563EB', 'bg' => 'rgba(37,99,235,0.12)', 'icon' => 'fa-solid fa-calendar-check'],
        };
    }
}
