<?php

namespace Modules\CORE\Interfaces;

interface PermissionRepositoryInterface extends ACLRepositoryInterface
{
    /**
     * Permission names grouped by the resource segment they belong to
     * (`doctor.create` => `doctor`), which drives the matrix UI.
     *
     * @return array<string, array<int, string>>
     */
    public function groupedByResource(): array;

    public function findByName(string $name, ?string $guard = null): ?\Spatie\Permission\Models\Permission;
}
