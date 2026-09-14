<?php

namespace Modules\CORE\Services\AccessControl;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\CORE\Interfaces\PermissionRepositoryInterface;
use Modules\CORE\Interfaces\UserRepositoryInterface;

/**
 * Managing *access* for users — never general profile editing, which belongs to
 * the application's own settings area.
 */
class UserAccessService extends AccessControlService
{
    public function __construct(
        UserRepositoryInterface $repository,
        PermissionRepositoryInterface $permissions,
    ) {
        parent::__construct($repository, $permissions);
    }

    /**
     * Replace a user's roles wholesale and optionally set a new password.
     *
     * @param  array<int, string>  $roles
     * @param  array<int, string>  $permissions
     */
    public function syncAccess(User $user, array $roles, array $permissions = [], ?string $password = null): User
    {
        return DB::transaction(function () use ($user, $roles, $permissions, $password): User {
            $user->syncRoles($roles);
            $user->syncPermissions($this->validPermissions($permissions));

            if ($password !== null && $password !== '') {
                $user->forceFill(['password' => Hash::make($password)]);
            }

            $user->save();

            return $user->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $roles
     * @param  array<int, string>  $permissions
     */
    public function createWithAccess(array $data, array $roles, array $permissions = []): User
    {
        return DB::transaction(function () use ($data, $roles, $permissions): User {
            /** @var User $user */
            $user = $this->repository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->syncRoles($roles);
            $user->syncPermissions($this->validPermissions($permissions));

            return $user->refresh();
        });
    }

    public function findOrFail(int|string $id): User
    {
        $model = parent::findOrFail($id);

        abort_unless($model instanceof User, 500, 'Expected a user model.');

        return $model;
    }
}
