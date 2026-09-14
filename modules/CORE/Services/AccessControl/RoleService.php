<?php

namespace Modules\CORE\Services\AccessControl;

use Modules\CORE\Interfaces\PermissionRepositoryInterface;
use Modules\CORE\Interfaces\RoleRepositoryInterface;

class RoleService extends AccessControlService
{
    public function __construct(
        RoleRepositoryInterface $repository,
        PermissionRepositoryInterface $permissions,
    ) {
        parent::__construct($repository, $permissions);
    }

    /**
     * Roles offered as a filter facet on the users screen.
     *
     * @return array<int, string>
     */
    public function roleOptions(): array
    {
        return $this->repository->query()->orderBy('name')->pluck('name', 'name')->all();
    }
}
