<?php

namespace Modules\CORE\Requests\AuditTrail;

use Modules\CORE\Support\Http\Requests\DataTableRequest;
use Illuminate\Validation\Rule;

class IndexAuditTrailRequest extends DataTableRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function filterRules(): array
    {
        return [
            'filters.event' => ['nullable', 'array'],
            'filters.event.*' => ['string', Rule::in(['created', 'updated', 'deleted', 'restored', 'login', 'logout', 'failed-login'])],
            'filters.module' => ['nullable', 'array'],
            'filters.module.*' => ['string', 'max:32'],
            'filters.user_id' => ['nullable', 'array'],
            'filters.user_id.*' => ['integer', Rule::exists('users', 'id')],
            'filters.created_at' => ['nullable', 'array'],
            'filters.created_at.from' => ['nullable', 'date'],
            'filters.created_at.to' => ['nullable', 'date', 'after_or_equal:filters.created_at.from'],
        ];
    }
}
