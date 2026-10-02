<?php

namespace App\Http\Requests\Admin;

use App\Models\Task;
use App\Models\ClientSocialMediaLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isSocialType = Task::isSocialType($this->input('type'));
        $isDevProject = Task::isDevType($this->input('type'));

        $rules = [
            'title'       => ['required', 'string', 'max:255'],
            'client_id'   => ['required', 'exists:clients,id'],
            'client_social_media_link_id' => [
                Rule::requiredIf($isSocialType),
                'nullable',
                Rule::exists('client_social_media_links', 'id')->where(function ($query) {
                    return $query->where('client_id', $this->input('client_id'));
                }),
            ],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'type'        => ['required', 'in:reel,post,story,video,carousel,brochure,banner,flyer,website,software,maintenance,others'],
            'platform'    => [$isSocialType ? 'required' : 'nullable', 'array', $this->platformRule()],
            'priority'    => ['required', 'in:normal,high,urgent'],
            'status'      => ['required', 'in:todo,inprogress,review,pending_approval,completed,published'],
            'brief'       => ['nullable', 'string'],
            'deadline'    => ['nullable', 'date'],
            // Development projects run on dev_deadline/launch_date, not a social post date
            'post_date'   => [$isDevProject ? 'nullable' : 'required', 'date'],
        ];

        if ($isDevProject) {
            $rules['assigned_to'][] = Task::devAssigneeRule();
            $rules += [
                'project_start_date' => ['nullable', 'date'],
                'dev_deadline'       => ['nullable', 'date'],
                'launch_date'        => ['nullable', 'date'],
                'preferred_tech'     => ['nullable', 'string', 'max:100'],
                'modules'            => ['nullable', 'string', 'max:1000'],
                'business_type'      => ['nullable', 'string', 'max:1000'],
                'logo_received'      => ['nullable', 'boolean'],
                'images_received'    => ['nullable', 'boolean'],
                'content_received'   => ['nullable', 'boolean'],
                'domain_purchased'   => ['nullable', 'boolean'],
                'hosting_access'     => ['nullable', 'boolean'],
                'has_wireframes'     => ['nullable', 'boolean'],
            ];
            if ($this->filled('project_start_date')) {
                $rules['dev_deadline'][] = 'after_or_equal:project_start_date';
                $rules['launch_date'][] = 'after_or_equal:project_start_date';
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'launch_date.after_or_equal' => 'Launch date cannot be before the project start date.',
            'dev_deadline.after_or_equal' => 'Development deadline cannot be before the project start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!Task::isSocialType($this->input('type')) || $this->filled('platform')) {
            return;
        }

        $socialLinkId = $this->input('client_social_media_link_id');
        if (!$socialLinkId) {
            return;
        }

        $platform = ClientSocialMediaLink::query()
            ->whereKey($socialLinkId)
            ->where('client_id', $this->input('client_id'))
            ->value('platform');

        if ($platform) {
            $this->merge(['platform' => [$platform]]);
        }
    }

    private function platformRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            foreach ((array) $value as $v) {
                if (!in_array($v, Task::PLATFORMS, true)) {
                    $fail("The {$attribute} contains an invalid value.");
                    return;
                }
            }
        };
    }
}
