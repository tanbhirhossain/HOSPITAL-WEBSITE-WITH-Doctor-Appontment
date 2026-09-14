<?php

namespace Modules\DOCTOR\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Modules\DOCTOR\Interfaces\DepartmentCategoryRepositoryInterface;
use Modules\DOCTOR\Models\DepartmentCategory;

class DepartmentCategoryRepository extends BaseRepository implements DepartmentCategoryRepositoryInterface
{
    /** @var class-string<DepartmentCategory> */
    protected string $modelClass = DepartmentCategory::class;

    protected array $searchable = ['name', 'slug'];

    protected array $sortable = ['id', 'name', 'sort_order', 'is_active', 'created_at'];

    protected array $filterable = [
        'is_active' => 'is_active',
    ];

    protected array $with = ['departments'];

    public function categoryOptions(bool $activeOnly = false): array
    {
        return DepartmentCategory::query()
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (DepartmentCategory $category): array => [
                'id' => $category->id,
                'title' => $category->name,
            ])
            ->all();
    }
}
