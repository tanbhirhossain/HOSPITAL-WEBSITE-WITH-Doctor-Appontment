<?php

namespace Modules\DOCTOR\Requests\PageSection;

use Modules\CORE\Support\Http\Requests\DataTableRequest;

class IndexPageSectionRequest extends DataTableRequest
{
    protected function filterRules(): array
    {
        return [
            'filters.is_active' => ['nullable', 'array'],
            'filters.is_active.*' => ['boolean'],
        ];
    }
}
