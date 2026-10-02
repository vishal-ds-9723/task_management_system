<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateMonthlyScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . now()->addYears(10)->year,
            'posts' => 'required|integer|min:0',
            'reels' => 'required|integer|min:0',
            'stories' => 'required|integer|min:0',
            'carousel' => 'required|integer|min:0',
            'videos' => 'required|integer|min:0',
            'guides' => 'nullable|integer|min:0',
            'collections' => 'nullable|integer|min:0',
            'other' => 'required|integer|min:0',
        ];
    }
}

