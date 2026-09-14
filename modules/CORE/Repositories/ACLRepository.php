<?php

namespace Modules\CORE\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Modules\CORE\Interfaces\ACLRepositoryInterface;

/**
 * Shared behaviour for the three access-control repositories.
 *
 * Spatie's role, permission and user models all expose `syncPermissions()`,
 * so the guarded-role bookkeeping lives once here instead of three times.
 */
abstract class ACLRepository extends BaseRepository implements ACLRepositoryInterface
{
    /**
     * Names that must never be renamed or deleted from the UI.
     *
     * @var array<int, string>
     */
    protected array $protected = ['super-admin'];

    public function saveWithPermissions(Model $model, array $data, array $permissions): Model
    {
        $model->fill($data);
        $model->save();

        $this->syncPermissions($model, $permissions);

        return $model->refresh();
    }

    public function syncPermissions(Model $model, array $permissions): array
    {
        if (! method_exists($model, 'syncPermissions')) {
            return [];
        }

        $model->syncPermissions($permissions);

        return $model->permissions()->pluck('name')->all();
    }

    public function isProtected(string $name): bool
    {
        return in_array(strtolower(trim($name)), array_map('strtolower', $this->protectedNames()), true);
    }

    /**
     * @return array<int, string>
     */
    public function protectedNames(): array
    {
        return $this->protected;
    }

    /**
     * Refuse to delete a protected record — enforced in the repository so it
     * applies to commands and queue jobs too, not just the controller.
     */
    public function delete(int|string|Model $model): bool
    {
        $resolved = $this->resolveModel($model);

        if ($this->isProtected($resolved->name ?? '')) {
            return false;
        }

        return parent::delete($resolved);
    }
}
