<?php

namespace Modules\DOCTOR\Requests\PageSection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'section_key' => ['required', 'string', 'max:120', Rule::unique('page_sections', 'section_key')],
            'badge' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:250'],
            'subtitle' => ['nullable', 'string', 'max:2000'],
            'primary_button_text' => ['nullable', 'string', 'max:80'],
            'primary_button_url' => ['nullable', 'string', 'max:500'],
            'secondary_button_text' => ['nullable', 'string', 'max:80'],
            'secondary_button_url' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['section_key' => 'section key'];
    }
}
