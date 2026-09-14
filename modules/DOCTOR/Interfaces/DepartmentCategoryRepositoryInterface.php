<?php

namespace Modules\DOCTOR\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;

interface DepartmentCategoryRepositoryInterface extends DataTableRepositoryInterface
{
    /**
     * Categories ready for a select box, optionally restricted to active ones.
     *
     * @return array<int, string>
     */
    public function categoryOptions(bool $activeOnly = false): array;
}
