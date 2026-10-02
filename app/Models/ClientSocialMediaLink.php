<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSocialMediaLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'platform',
        'url',
        'label',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get platform icon information
     */
    public function getPlatformIcon()
    {
        $icons = [
            'instagram' => ['icon' => 'fa-instagram', 'color' => '#E1306C'],
            'facebook' => ['icon' => 'fa-facebook-f', 'color' => '#1877F2'],
            'twitter' => ['icon' => 'fa-x-twitter', 'color' => '#000000'],
            'linkedin' => ['icon' => 'fa-linkedin-in', 'color' => '#0077B5'],
            'youtube' => ['icon' => 'fa-youtube', 'color' => '#FF0000'],
            'tiktok' => ['icon' => 'fa-tiktok', 'color' => '#000000'],
            'whatsapp' => ['icon' => 'fa-whatsapp', 'color' => '#25D366'],
        ];

        return $icons[$this->platform] ?? ['icon' => 'fa-link', 'color' => '#888888'];
    }
}
