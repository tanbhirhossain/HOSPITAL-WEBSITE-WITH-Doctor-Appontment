<?php

namespace Modules\CORE\Repositories;

use Modules\CORE\Interfaces\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;

class RoleRepository extends ACLRepository implements RoleRepositoryInterface
{
    /** @var class-string<Role> */
    protected string $modelClass = Role::class;

    protected array $searchable = ['name', 'guard_name'];

    protected array $sortable = ['id', 'name', 'guard_name', 'created_at'];

    protected array $filterable = [
        'guard_name' => 'guard_name',
    ];

    protected array $with = ['permissions'];

    public function findByName(string $name, ?string $guard = null): ?Role
    {
        return Role::query()
            ->where('name', $name)
            ->when($guard, fn ($query) => $query->where('guard_name', $guard))
            ->first();
    }
}
