<?php

namespace Modules\CORE\Requests\AccessControl;

use Modules\CORE\Support\Http\Requests\DataTableRequest;
use Illuminate\Validation\Rule;

class IndexRoleRequest extends DataTableRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function filterRules(): array
    {
        return [
            'filters.guard_name' => ['nullable', 'array'],
            'filters.guard_name.*' => ['string', Rule::in(['web'])],
        ];
    }
}
