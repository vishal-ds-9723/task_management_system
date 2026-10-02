<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $fields = [
            'name', 'category', 'emoji', 'color', 'website',
            'instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'tiktok',
            'notes', 'contact_person', 'contact_email', 'contact_phone',
        ];

        $normalized = [];
        foreach ($fields as $field) {
            if (!$this->has($field)) {
                continue;
            }

            $value = $this->input($field);
            if (!is_string($value)) {
                $normalized[$field] = $value;
                continue;
            }

            $trimmed = trim($value);
            $normalized[$field] = $trimmed === '' ? null : $trimmed;
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'category'        => 'nullable|string|max:255',
            'emoji'           => 'nullable|string|max:10',
            'color'           => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,svg|max:2048',
            'cta_video'       => 'nullable|file|mimes:mp4,mov,avi,webm,mkv,wmv|max:102400',
            'footer_image'    => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,svg|max:10240',
            'is_active'       => 'nullable|boolean',
            'website'         => 'nullable|url:http,https|max:255',
            'instagram'       => 'nullable|string|max:255',
            'facebook'        => 'nullable|string|max:255',
            'twitter'         => 'nullable|string|max:255',
            'linkedin'        => 'nullable|string|max:255',
            'youtube'         => 'nullable|string|max:255',
            'tiktok'          => 'nullable|string|max:255',
            'notes'           => 'nullable|string|max:2000',
            'contact_person'  => 'nullable|string|max:255',
            'contact_email'   => 'nullable|email|max:255',
            'contact_phone'   => ['nullable', 'string', 'max:50', 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Client name is required.',
            'color.regex' => 'Brand color must be a valid hex color like #4F6DF0.',
            'website.url' => 'Website must be a valid URL and start with http:// or https://.',
            'contact_email.email' => 'Please enter a valid email address.',
            'contact_phone.regex' => 'Phone must contain only digits, spaces, +, -, or parentheses and be 7-20 characters.',
            'logo.max' => 'Logo must be 2MB or smaller.',
            'logo.mimes' => 'Logo must be JPG, JPEG, PNG, GIF, WEBP, or SVG.',
            'cta_video.max' => 'CTA Video must be 100MB or smaller.',
            'cta_video.mimes' => 'CTA Video must be a valid video format (MP4, MOV, AVI, WEBM, MKV).',
            'footer_image.max' => 'Footer image must be 10MB or smaller.',
            'footer_image.mimes' => 'Footer image must be JPG, JPEG, PNG, GIF, WEBP, or SVG.',
        ];
    }
}

