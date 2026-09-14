<?php

namespace Modules\CORE\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Segregated contract for the access-control repositories (roles, permissions
 * and users). Anything the ACL screens need is declared here — and nothing else.
 */
interface ACLRepositoryInterface extends DataTableRepositoryInterface
{
    /**
     * Persist a record together with its Spatie permission assignments.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $permissions
     */
    public function saveWithPermissions(Model $model, array $data, array $permissions): Model;

    /**
     * @param  array<int, string>  $permissions
     * @return array<int, string>
     */
    public function syncPermissions(Model $model, array $permissions): array;

    /**
     * Guard roles are immutable from the UI — they bootstrap the system.
     */
    public function isProtected(string $name): bool;

    /**
     * @return array<int, string>
     */
    public function protectedNames(): array;
}
