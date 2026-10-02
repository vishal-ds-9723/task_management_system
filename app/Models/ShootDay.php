<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShootDay extends Model
{
    protected $fillable = [
        'title', 'type', 'shoot_date', 'start_date', 'number_of_days',
        'location', 'notes', 'client_id', 'created_by', 'status',
    ];

    protected $casts = [
        'shoot_date' => 'date',
        'start_date' => 'datetime:H:i',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
