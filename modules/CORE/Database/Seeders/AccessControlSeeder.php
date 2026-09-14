<?php

namespace Modules\CORE\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Bootstraps the permission catalogue, the default roles and the first
 * administrator account. Idempotent — safe to re-run on every deploy.
 */
class AccessControlSeeder extends Seeder
{
    /**
     * Single source of truth for the whole permission catalogue.
     *
     * @var array<string, array<int, string>>
     */
    public const RESOURCES = [
        'dashboard' => ['view'],
        'role' => ['view', 'create', 'update', 'delete'],
        'permission' => ['view', 'create', 'update', 'delete'],
        'user' => ['view', 'create', 'update', 'delete'],
        'seo' => ['view', 'create', 'update', 'delete'],
        'audit' => ['view', 'delete'],
        'department' => ['view', 'create', 'update', 'delete'],
        'doctor' => ['view', 'create', 'update', 'delete'],
        'schedule' => ['view', 'create', 'update', 'delete'],
        'symptom' => ['view', 'create', 'update', 'delete'],
        'page-section' => ['view', 'create', 'update', 'delete'],
    ];

    /**
     * Role => permission patterns. `*` means "everything".
     *
     * @var array<string, array<int, string>>
     */
    public const ROLES = [
        'super-admin' => ['*'],

        'admin' => [
            'dashboard.view',
            'department.*', 'doctor.*', 'schedule.*', 'symptom.*', 'page-section.*',
            'seo.*', 'audit.view', 'user.view', 'user.update', 'role.view',
        ],

        'editor' => [
            'dashboard.view',
            'department.view', 'department.create', 'department.update',
            'doctor.view', 'doctor.create', 'doctor.update',
            'schedule.view', 'schedule.create', 'schedule.update',
            'symptom.view', 'symptom.create', 'symptom.update',
            'page-section.*', 'seo.*',
        ],

        'viewer' => [
            'dashboard.view',
            'department.view', 'doctor.view', 'schedule.view',
            'symptom.view', 'page-section.view', 'seo.view', 'audit.view',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = $this->syncPermissions();
        $this->syncRoles($permissions);
        $this->syncAdministrator();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<int, string>  $permissions
     * @return array<string, array<int, string>>
     */
    public static function expand(array $permissions): array
    {
        $all = [];

        foreach (self::RESOURCES as $resource => $actions) {
            foreach ($actions as $action) {
                $all[] = "{$resource}.{$action}";
            }
        }

        if (in_array('*', $permissions, true)) {
            return $all;
        }

        $granted = [];

        foreach ($permissions as $pattern) {
            if (str_ends_with($pattern, '.*')) {
                $resource = str($pattern)->before('.*')->toString();

                foreach (self::RESOURCES[$resource] ?? [] as $action) {
                    $granted[] = "{$resource}.{$action}";
                }

                continue;
            }

            $granted[] = $pattern;
        }

        return array_values(array_intersect($granted, $all));
    }

    /**
     * @return array<int, string>
     */
    private function syncPermissions(): array
    {
        $names = self::expand(['*']);

        DB::transaction(function () use ($names): void {
            foreach ($names as $name) {
                Permission::query()->firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                );
            }
        });

        // Permissions removed from the catalogue should disappear too.
        Permission::query()->whereNotIn('name', $names)->delete();

        return $names;
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function syncRoles(array $permissions): void
    {
        foreach (self::ROLES as $role => $patterns) {
            /** @var Role $model */
            $model = Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web']);

            $model->syncPermissions(self::expand($patterns));
        }

        // Roles not declared above are left alone — local custom roles are
        // legitimate and must not be destroyed by a re-seed.
        unset($permissions);
    }

    private function syncAdministrator(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@amzhospital.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }
    }
}
