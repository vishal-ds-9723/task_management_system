<?php

namespace App\Http\Requests\Admin;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'client_id'   => ['sometimes', 'required', 'exists:clients,id'],
            'type'        => ['sometimes', 'required', 'in:reel,post,story,video,carousel,brochure,banner,flyer,website,software,others'],
            'platform'    => ['sometimes', 'required', $this->platformRule()],
            'status'      => ['sometimes', 'in:todo,inprogress,review,pending_approval,completed,published'],
            'assigned_to' => ['sometimes', 'nullable', 'exists:users,id'],
            'priority'    => ['sometimes', 'in:normal,high,urgent'],
            'deadline'    => ['sometimes', 'nullable', 'date'],
            'post_date'   => ['sometimes', 'nullable', 'date'],
            'brief'       => ['sometimes', 'nullable', 'string'],
            'assigned_employees' => ['sometimes', 'nullable', 'string'],
        ];
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

