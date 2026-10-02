<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class SyncFestivalCalendarRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content_types' => 'nullable|array',
            'platforms'     => 'nullable|array',
            'notes'         => 'nullable|string|max:500',
        ];
    }
}

