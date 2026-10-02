<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visit_type'        => 'required|in:lead,client',
            'client_id'         => 'nullable|required_if:visit_type,client|exists:clients,id',
            'lead_name'         => 'nullable|string|max:255',
            'company_name'      => 'nullable|string|max:255',
            'contact_person'    => 'nullable|string|max:255',
            'contact_email'     => 'nullable|email|max:255',
            'contact_phone'     => 'nullable|string|max:50',
            'location'          => 'nullable|string|max:255',
            'meeting_mode'      => 'required|string|max:50',
            'visited_by'        => 'required|exists:users,id',
            'visit_date'        => 'required|date',
            'purpose'           => 'required|string|max:255',
            'status'            => 'required|in:scheduled,completed,follow_up_needed,converted,cancelled',
            'summary'           => 'nullable|string|max:5000',
            'discussion_points' => 'nullable|string|max:5000',
            'action_items'      => 'nullable|string|max:5000',
            'next_follow_up'    => 'nullable|date',
            'attachment'        => 'nullable|file|max:20480|mimes:pdf,doc,docx,jpg,jpeg,png,webp,zip',
            'delete_attachment' => 'nullable|boolean',
        ];
    }
}
