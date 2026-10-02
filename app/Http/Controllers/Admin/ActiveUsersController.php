<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ActiveUserService;

class ActiveUsersController extends Controller
{
    protected $service;

    public function __construct(ActiveUserService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $users = $this->service->getActiveUsers();

        return view('admin.active-users', compact('users'));
    }
}

