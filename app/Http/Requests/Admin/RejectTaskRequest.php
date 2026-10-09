<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason'  => 'required|string|max:2000',
            'reference_files'   => 'nullable|array',
            'reference_files.*' => 'file|mimes:jpg,jpeg,jfif,png,gif,webp,svg,mp4,mov,webm,mkv,avi,m4v,3gp,ts,pdf,doc,docx,zip,rar,7z|max:512000',
        ];
    }
}

