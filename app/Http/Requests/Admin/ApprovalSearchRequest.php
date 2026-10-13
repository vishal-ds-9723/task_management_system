<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'      => 'nullable|string|max:255',
            'filter'      => 'nullable|string',
            'task_id'     => 'nullable|integer',
            'client_id'   => 'nullable|integer',
            'assignee_id' => 'nullable|integer',
            'urgent_only' => 'nullable|string',
            'has_media'   => 'nullable|string',
        ];
    }
}

