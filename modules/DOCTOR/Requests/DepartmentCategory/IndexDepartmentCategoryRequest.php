<?php

namespace Modules\DOCTOR\Requests\DepartmentCategory;

use Modules\CORE\Support\Http\Requests\DataTableRequest;

class IndexDepartmentCategoryRequest extends DataTableRequest
{
    protected function filterRules(): array
    {
        return [
            'filters.is_active' => ['nullable', 'array'],
            'filters.is_active.*' => ['boolean'],
        ];
    }
}
