<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientVisitUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_visit_id',
        'user_id',
        'update_type',
        'note',
        'status_from',
        'status_to',
        'next_follow_up',
        'attachment',
    ];

    protected $casts = [
        'next_follow_up' => 'date',
    ];

    public function visit()
    {
        return $this->belongsTo(ClientVisit::class, 'client_visit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUpdateTypeDetailsAttribute(): array
    {
        return match ($this->update_type) {
            'call'          => ['label' => 'Phone Call', 'icon' => 'fa-solid fa-phone', 'color' => '#3B82F6'],
            'meeting'       => ['label' => 'Meeting / Discussion', 'icon' => 'fa-solid fa-handshake', 'color' => '#8B5CF6'],
            'proposal'      => ['label' => 'Proposal Sent', 'icon' => 'fa-solid fa-file-invoice-dollar', 'color' => '#10B981'],
            'status_change' => ['label' => 'Status Changed', 'icon' => 'fa-solid fa-arrows-rotate', 'color' => '#F59E0B'],
            'follow_up'     => ['label' => 'Follow-up Note', 'icon' => 'fa-solid fa-calendar-check', 'color' => '#EC4899'],
            default         => ['label' => 'Note', 'icon' => 'fa-solid fa-comment-dots', 'color' => '#6366F1'],
        };
    }
}
