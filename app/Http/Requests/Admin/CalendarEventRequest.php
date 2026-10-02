<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'nullable|exists:clients,id',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'date_type' => 'required_with:new_date|in:deadline,post_date',
            'new_date' => 'required_with:date_type|date',
        ];
    }
}

