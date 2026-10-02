<?php

namespace App\Http\Requests\Designer;

use Illuminate\Foundation\Http\FormRequest;

class IndexTasksRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:100',
            'client_id' => 'nullable|exists:clients,id',
            'status' => 'nullable|string',
        ];
    }
}

