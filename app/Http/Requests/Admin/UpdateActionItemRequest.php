<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'client_id' => 'nullable|exists:clients,id',
            'assigned_to' => 'required|exists:users,id',
            'due_at' => 'required|date',
            'priority' => 'required|in:normal,urgent',
            'recurring' => 'nullable|in:daily,weekly,monthly',
        ];
    }
}

