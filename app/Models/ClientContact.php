<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContact extends Model
{
    protected $fillable = [
        'client_id', 'name', 'email', 'phone', 'role', 'is_primary', 'position',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'position'   => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
