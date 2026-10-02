<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type', '');
        $isDevProject = in_array($type, ['website', 'software']);

        $rules = [
            'title'       => ['required', 'string', 'max:255'],
            'additional_titles'   => ['nullable', 'array', 'max:19'],
            'additional_titles.*' => ['required', 'string', 'max:255', 'distinct', 'different:title'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'type'        => ['required', 'in:reel,post,story,video,carousel,brochure,banner,flyer,website,software,others'],
            'priority'    => ['required', 'in:normal,high,urgent'],
            'status'      => ['required', 'in:todo,inprogress'],
            'brief'       => ['nullable', 'string'],
            'caption'     => ['nullable', 'string', 'max:5000'],
            'hashtags'    => ['nullable', 'string', 'max:1000'],
            'reference_links'   => ['nullable', 'array', 'max:15'],
            'reference_links.*' => ['nullable', 'url', 'max:2048'],
            'deadline'    => ['nullable', 'date'],
            'post_date'   => ['nullable', 'date'],
            'logo_received'      => ['nullable', 'boolean'],
            'images_received'    => ['nullable', 'boolean'],
            'content_received'   => ['nullable', 'boolean'],
            'has_wireframes'     => ['nullable', 'boolean'],
            'domain_purchased'   => ['nullable', 'boolean'],
            'hosting_access'     => ['nullable', 'boolean'],
            'tech_stack'         => ['nullable', 'string', 'max:50'],
            'project_notes'      => ['nullable', 'string', 'max:2000'],
            'project_start_date' => ['nullable', 'date'],
            'launch_date'        => ['nullable', 'date'],
            'dev_deadline'       => ['nullable', 'date'],
            'preferred_tech'     => ['nullable', 'string', 'max:100'],
            'modules'            => ['nullable', 'string', 'max:1000'],
            'business_type'      => ['nullable', 'string', 'max:1000'],
        ];

        if ($isDevProject) {
            // Dev projects: assignment is optional; client is still required (DB constraint)
            $rules['client_id']   = ['required', 'exists:clients,id'];
            $rules['platform']    = ['nullable', 'array'];
            $rules['platform.*']  = ['string', 'max:50'];
        } else {
            // Social/design tasks: client, platform, assignee, post date, brief are all required
            $rules['client_id']   = ['required', 'exists:clients,id'];
            $rules['platform']    = ['required', 'array', 'min:1'];
            $rules['platform.*']  = ['string', 'max:50'];
            $rules['post_date']   = ['required', 'date', 'after_or_equal:today'];
            $rules['brief']       = ['required', 'string'];
            $rules['client_social_media_link_id'] = [
                'nullable',
                Rule::exists('client_social_media_links', 'id')->where(function ($query) {
                    return $query->where('client_id', $this->input('client_id'));
                }),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'brief.required' => 'Content brief is required.',
            'additional_titles.max' => 'You can create up to 20 tasks at once.',
            'additional_titles.*.required' => 'Every additional task needs a title.',
            'additional_titles.*.distinct' => 'Additional task titles must be unique.',
            'additional_titles.*.different' => 'Each task title must be unique.',
        ];
    }
}
