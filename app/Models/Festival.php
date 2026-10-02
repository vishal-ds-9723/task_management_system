<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Festival extends Model
{
    protected $fillable = [
        'name', 'date', 'emoji', 'description', 'category', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function selections()
    {
        return $this->hasMany(FestivalSelection::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', today())->orderBy('date');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
