<?php

namespace App\Http\Requests\Designer;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUrgentTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $type = (string) $this->input('type', 'post');
        $validTypes = \App\Models\DropdownOption::getValues('task_type', Task::URGENT_TYPES);
        $validPlatforms = \App\Models\DropdownOption::getValues('platform', Task::PLATFORMS);

        return [
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'type' => ['required', 'string', 'max:50', Rule::in($validTypes)],
            'platform' => Task::isSocialType($type)
                ? ['required', 'array', 'min:1']
                : ['nullable', 'array'],
            'platform.*' => ['string', 'max:50', Rule::in($validPlatforms)],
            'urgent_requested_by' => 'required|string|max:100',
            'brief' => 'nullable|string|max:2000',
            'auto_pause_task_id' => 'nullable|exists:tasks,id',
            'deadline' => 'nullable|date',
            'design_deadline' => 'nullable|date',
        ];
    }
}
