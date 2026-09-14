<?php

namespace Modules\DOCTOR\Requests\Symptom;

use Modules\CORE\Support\Http\Requests\DataTableRequest;

class IndexSymptomRequest extends DataTableRequest
{
    protected function filterRules(): array
    {
        return [
            'filters.is_active' => ['nullable', 'array'],
            'filters.is_active.*' => ['boolean'],
        ];
    }
}
