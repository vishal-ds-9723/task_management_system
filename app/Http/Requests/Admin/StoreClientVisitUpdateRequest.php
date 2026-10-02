<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientVisitUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'update_type'    => 'required|in:note,call,meeting,proposal,status_change,follow_up',
            'note'           => 'required|string|max:5000',
            'status'         => 'nullable|in:scheduled,completed,follow_up_needed,converted,cancelled',
            'next_follow_up' => 'nullable|date',
            'attachment'     => 'nullable|file|max:20480|mimes:pdf,doc,docx,jpg,jpeg,png,webp,zip',
        ];
    }

    public function messages(): array
    {
        return [
            'note.required' => 'Please write an update note or meeting outcome.',
        ];
    }
}
