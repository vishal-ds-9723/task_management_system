<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PublishingIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'filter'  => 'nullable|in:pending,awaiting,partial,published,overdue,client_yes,client_no',
            'client'  => 'nullable|exists:clients,id',
            'creator' => 'nullable|exists:users,id',
            'search'  => 'nullable|string|max:100',
        ];
    }
}

