<?php

namespace Modules\DOCTOR\Services;

use Modules\CORE\Support\Services\BaseService;
use Illuminate\Support\Str;
use Modules\DOCTOR\Interfaces\DepartmentCategoryRepositoryInterface;

class DepartmentCategoryService extends BaseService
{
    public function __construct(DepartmentCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return array<int, string>
     */
    public function categoryOptions(bool $activeOnly = false): array
    {
        return $this->repository->categoryOptions($activeOnly);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        if (isset($data['name']) && blank($data['slug'] ?? null)) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }
}
