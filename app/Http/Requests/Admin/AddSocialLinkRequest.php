<?php

namespace App\Http\Requests\Admin;

use App\Support\SocialRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddSocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $platform = $this->input('platform');
        $url = $this->input('url');
        $label = $this->input('label');

        $this->merge([
            'platform' => is_string($platform) ? strtolower(trim($platform)) : $platform,
            'url' => is_string($url) ? trim($url) : $url,
            'label' => is_string($label) ? (trim($label) === '' ? null : trim($label)) : $label,
        ]);
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', Rule::in(SocialRegistry::PLATFORMS)],
            'url'      => 'required|url:http,https|max:500',
            'label'    => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'platform.required' => 'Please choose a social platform.',
            'platform.in' => 'Selected social platform is not valid.',
            'url.required' => 'Social link URL is required.',
            'url.url' => 'URL must be valid and start with http:// or https://.',
            'url.max' => 'URL must be 500 characters or fewer.',
            'label.max' => 'Username/label must be 100 characters or fewer.',
        ];
    }
}

