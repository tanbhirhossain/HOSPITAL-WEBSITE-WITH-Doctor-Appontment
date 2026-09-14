<?php

namespace Modules\DOCTOR\Requests\Department;

use Modules\CORE\Support\Http\Requests\DataTableRequest;
use Illuminate\Validation\Rule;

class IndexDepartmentRequest extends DataTableRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function filterRules(): array
    {
        return [
            'filters.department_category_id' => ['nullable', 'array'],
            'filters.department_category_id.*' => ['integer', Rule::exists('department_categories', 'id')],
            'filters.is_active' => ['nullable', 'array'],
            'filters.is_active.*' => ['boolean'],
            'filters.is_featured' => ['nullable', 'array'],
            'filters.is_featured.*' => ['boolean'],
        ];
    }
}
