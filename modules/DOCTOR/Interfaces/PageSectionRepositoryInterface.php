<?php

namespace Modules\DOCTOR\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;

interface PageSectionRepositoryInterface extends DataTableRepositoryInterface
{
    public function findByKey(string $key): ?\Modules\DOCTOR\Models\PageSection;
}
