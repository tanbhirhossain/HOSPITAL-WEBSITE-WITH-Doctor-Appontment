<?php

namespace Modules\CORE\Requests\AccessControl;

use Modules\CORE\Support\Http\Requests\DataTableRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class IndexUserRequest extends DataTableRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function filterRules(): array
    {
        return [
            'filters.role' => ['nullable', 'array'],
            'filters.role.*' => ['string', Rule::exists('roles', 'name')],
            'filters.email_verified' => ['nullable', Rule::in(['verified', 'unverified'])],
        ];
    }

    /**
     * Roles are validated against the roles table at request time.
     *
     * @return array<int, string>
     */
    public function roleNames(): array
    {
        return Role::query()->pluck('name')->all();
    }
}
