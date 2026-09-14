<?php

namespace Modules\CORE\Interfaces;

interface RoleRepositoryInterface extends ACLRepositoryInterface
{
    /**
     * Roles are identified by name in every permission guard call, so lookups
     * by name are part of the contract rather than an implementation detail.
     */
    public function findByName(string $name, ?string $guard = null): ?\Spatie\Permission\Models\Role;
}
