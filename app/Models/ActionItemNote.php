<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionItemNote extends Model
{
    protected $fillable = ['action_item_id', 'user_id', 'note'];

    public function actionItem()
    {
        return $this->belongsTo(ActionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
