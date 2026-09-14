<?php

namespace Modules\CORE\Repositories;

use Modules\CORE\Interfaces\PermissionRepositoryInterface;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends ACLRepository implements PermissionRepositoryInterface
{
    /** @var class-string<Permission> */
    protected string $modelClass = Permission::class;

    protected array $searchable = ['name', 'guard_name'];

    protected array $sortable = ['id', 'name', 'guard_name', 'created_at'];

    protected array $filterable = [
        'guard_name' => 'guard_name',
    ];

    public function groupedByResource(): array
    {
        $grouped = [];

        foreach ($this->all() as $permission) {
            $resource = str($permission->name)->before('.')->toString();
            $grouped[$resource][] = $permission->name;
        }

        ksort($grouped);

        return $grouped;
    }

    public function findByName(string $name, ?string $guard = null): ?Permission
    {
        return Permission::query()
            ->where('name', $name)
            ->when($guard, fn ($query) => $query->where('guard_name', $guard))
            ->first();
    }
}
