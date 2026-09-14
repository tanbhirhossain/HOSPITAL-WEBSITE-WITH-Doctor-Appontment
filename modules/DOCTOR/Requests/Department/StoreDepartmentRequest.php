<?php

namespace Modules\DOCTOR\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CORE\Support\Http\Requests\Concerns\SeoRules;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    use SeoRules;

    public function rules(): array
    {
        return [
            'department_category_id' => ['nullable', 'integer', Rule::exists('department_categories', 'id')],
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('departments', 'slug')],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_popular_search' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'page_sections' => ['nullable', 'array', 'max:20'],
            'page_sections.*.section_key' => ['required_with:page_sections', 'string', 'max:80'],
            'page_sections.*.title' => ['nullable', 'string', 'max:250'],
            'page_sections.*.badge' => ['nullable', 'string', 'max:150'],
            'page_sections.*.subtitle' => ['nullable', 'string', 'max:2000'],
            'page_sections.*.primary_button_text' => ['nullable', 'string', 'max:80'],
            'page_sections.*.primary_button_url' => ['nullable', 'string', 'max:500'],
            'page_sections.*.secondary_button_text' => ['nullable', 'string', 'max:80'],
            'page_sections.*.secondary_button_url' => ['nullable', 'string', 'max:500'],
            'page_sections.*.image' => ['nullable', 'string', 'max:500'],
            'page_sections.*.is_active' => ['nullable', 'boolean'],
            'symptoms' => ['nullable', 'array'],
            'symptoms.*' => ['integer', Rule::exists('symptoms', 'id')],
            ...$this->seoRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'department_category_id' => 'category',
            'short_description' => 'description',
        ];
    }
}
