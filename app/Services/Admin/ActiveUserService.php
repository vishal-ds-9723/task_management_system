<?php

namespace App\Services\Admin;

use App\Models\User;

class ActiveUserService
{
    public function getActiveUsers()
    {
        return User::orderByDesc('last_active_at')->get();
    }
}

