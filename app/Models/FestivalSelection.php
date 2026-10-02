<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FestivalSelection extends Model
{
    protected $fillable = [
        'festival_id', 'client_id', 'user_id', 'content_types', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'content_types' => 'array',
        ];
    }

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
