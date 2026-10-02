<?php

namespace App\Http\Requests\Strategist;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFestivalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:120',
            'date'        => 'required|date',
            'emoji'       => 'nullable|string|max:10',
            'category'    => 'nullable|string|max:60',
            'description' => 'nullable|string|max:500',
        ];
    }
}

