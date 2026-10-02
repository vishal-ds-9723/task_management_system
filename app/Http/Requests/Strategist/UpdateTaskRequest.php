<?php

namespace App\Http\Requests\Strategist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization in controller
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'client_id'   => ['required', 'exists:clients,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'type' => [
                'required',
                Rule::in(['reel', 'post', 'story', 'video', 'carousel', 'brochure', 'banner', 'flyer', 'website', 'software'])
            ],
            'platform' => [
                'nullable',
                'array',
                Rule::requiredIf(fn() => !in_array($this->type, ['website', 'software']))
            ],
            'platform.*'  => ['string', 'max:50'],
            'priority'    => ['required', Rule::in(['normal', 'high', 'urgent'])],
            'status'      => ['required', Rule::in(['todo', 'inprogress', 'review', 'completed'])],
            'brief'       => ['nullable', 'string'],
            'hashtags'    => ['nullable', 'string', 'max:1000'],
            'reference_links'   => ['nullable', 'array', 'max:15'],
            'reference_links.*' => ['nullable', 'url', 'max:2048'],
            'deadline'    => ['nullable', 'date'],
            'post_date'   => ['nullable', 'date'],
            'client_social_media_link_id' => ['nullable', 'exists:client_social_media_links,id'],
            'logo_received'    => ['nullable', 'boolean'],
            'images_received'  => ['nullable', 'boolean'],
            'content_received' => ['nullable', 'boolean'],
            'tech_stack'       => ['nullable', 'string', 'max:255'],
            'project_notes'    => ['nullable', 'string', 'max:2000'],
        ];
    }
}

