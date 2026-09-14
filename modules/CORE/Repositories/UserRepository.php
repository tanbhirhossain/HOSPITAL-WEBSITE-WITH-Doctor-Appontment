<?php

namespace Modules\CORE\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\CORE\Interfaces\UserRepositoryInterface;

class UserRepository extends ACLRepository implements UserRepositoryInterface
{
    /** @var class-string<User> */
    protected string $modelClass = User::class;

    protected array $searchable = ['name', 'email'];

    protected array $sortable = ['id', 'name', 'email', 'created_at'];

    protected array $filterable = [
        'email_verified' => 'email_verified_at',
    ];

    protected array $with = ['roles', 'permissions'];

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * Roles are not a column on `users`, so this filter is expressed through
     * the `applyFilter` hook rather than the column map.
     */
    protected function applyFilter(Builder $query, string $key, mixed $value): void
    {
        if ($key !== 'role') {
            return;
        }

        $roles = is_array($value) ? $value : [$value];

        $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->whereIn('name', $roles));
    }
}
