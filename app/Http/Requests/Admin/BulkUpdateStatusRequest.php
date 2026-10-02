<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['exists:tasks,id'],
            'status' => ['required', 'in:todo,inprogress,review,pending_approval,completed,published'],
        ];
    }
}

