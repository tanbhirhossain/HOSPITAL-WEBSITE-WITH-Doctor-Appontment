<?php

namespace Modules\CORE\Services\AccessControl;

use Illuminate\Support\Facades\DB;
use Modules\CORE\Interfaces\PermissionRepositoryInterface;

class PermissionService extends AccessControlService
{
    public function __construct(PermissionRepositoryInterface $repository)
    {
        parent::__construct($repository, $repository);
    }

    /**
     * Replace the whole permission catalogue with `resource.action` pairs.
     *
     * @param  array<int, string>  $names
     * @return array<int, string>  Names that were newly created.
     */
    public function syncCatalogue(array $names): array
    {
        $names = collect($names)
            ->map(fn (string $name): string => str($name)->trim()->lower()->toString())
            ->filter()
            ->unique()
            ->values()
            ->all();

        $existing = $this->repository->query()->pluck('name')->all();

        $created = array_values(array_diff($names, $existing));

        DB::transaction(function () use ($created): void {
            foreach ($created as $name) {
                $this->repository->create(['name' => $name, 'guard_name' => 'web']);
            }
        });

        return $created;
    }
}
