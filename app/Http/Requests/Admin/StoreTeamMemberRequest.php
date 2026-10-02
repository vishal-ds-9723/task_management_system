<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamMemberRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:6'],
            'role'         => ['required', 'string', 'max:50'],
            'additional_roles' => ['nullable', 'array'],
            'additional_roles.*' => ['string', 'max:50'],
            'can_manage_social_metrics' => ['required', 'boolean'],
            'avatar_color' => ['nullable', 'string', 'max:100'],
            'client_id'    => ['nullable', 'exists:clients,id'],
            'client_ids'   => ['nullable', 'array'],
            'client_ids.*' => ['exists:clients,id'],
        ];
    }
}

