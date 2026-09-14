<?php

namespace Modules\CORE\Services\AccessControl;

use Modules\CORE\Support\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\CORE\Interfaces\ACLRepositoryInterface;
use Modules\CORE\Interfaces\PermissionRepositoryInterface;
use RuntimeException;

/**
 * Business rules shared by the role / permission / user screens.
 *
 * The "protected record" rule (a guard role may not be renamed or removed)
 * lives here so it is enforced identically from HTTP, Artisan and tests.
 */
abstract class AccessControlService extends BaseService
{
    public function __construct(
        ACLRepositoryInterface $repository,
        protected readonly PermissionRepositoryInterface $permissions,
    ) {
        parent::__construct($repository);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $permissions
     */
    public function createWithPermissions(array $data, array $permissions): Model
    {
        return DB::transaction(function () use ($data, $permissions): Model {
            $model = $this->repository->create($data);
            $this->repository->syncPermissions($model, $this->validPermissions($permissions));

            return $model->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $permissions
     */
    public function updateWithPermissions(int|string|Model $model, array $data, array $permissions): Model
    {
        $resolved = $model instanceof Model ? $model : $this->findOrFail($model);

        $this->assertEditable($resolved, $data);

        return DB::transaction(function () use ($resolved, $data, $permissions): Model {
            return $this->repository->saveWithPermissions(
                $resolved,
                $data,
                $this->validPermissions($permissions),
            );
        });
    }

    public function delete(int|string|Model $model): bool
    {
        $resolved = $model instanceof Model ? $model : $this->findOrFail($model);

        $this->assertDeletable($resolved);

        return parent::delete($resolved);
    }

    /**
     * Whether the UI should lock this record (guard roles cannot be renamed
     * or removed because the system depends on them).
     */
    public function isProtectedName(string $name): bool
    {
        return $this->repository->isProtected($name);
    }

    /**
     * Every permission known to the system, for the permission matrix.
     *
     * @return array<int, string>
     */
    public function permissionOptions(): array
    {
        return $this->permissions->all()->pluck('name')->all();
    }

    /**
     * Permissions grouped by resource for the collapsed matrix UI.
     *
     * @return array<string, array<int, string>>
     */
    public function permissionMatrix(): array
    {
        return $this->permissions->groupedByResource();
    }

    /**
     * Drop permission names that no longer exist so a stale form payload can
     * never resurrect a deleted permission.
     *
     * @param  array<int, string>  $permissions
     * @return array<int, string>
     */
    protected function validPermissions(array $permissions): array
    {
        $known = array_flip($this->permissionOptions());

        $wanted = array_unique(array_map(
            static fn (mixed $permission): string => is_scalar($permission) ? (string) $permission : '',
            $permissions,
        ));

        return array_values(array_filter(
            $wanted,
            static fn (string $name): bool => isset($known[$name]),
        ));
    }

    protected function assertEditable(Model $model, array $data): void
    {
        $current = (string) ($model->name ?? '');

        if (! $this->repository->isProtected($current)) {
            return;
        }

        if (isset($data['name']) && (string) $data['name'] !== $current) {
            throw new RuntimeException("The record [{$current}] is protected and cannot be renamed.");
        }
    }

    protected function assertDeletable(Model $model): void
    {
        $name = (string) ($model->name ?? '');

        if ($this->repository->isProtected($name)) {
            throw new RuntimeException("The record [{$name}] is protected and cannot be deleted.");
        }
    }
}
