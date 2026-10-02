<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class PublishedContentIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'nullable|string|max:50',
            'platform' => 'nullable|string|max:50',
            'month' => 'nullable|date_format:Y-m',
            'search' => 'nullable|string|max:100',
        ];
    }
}

