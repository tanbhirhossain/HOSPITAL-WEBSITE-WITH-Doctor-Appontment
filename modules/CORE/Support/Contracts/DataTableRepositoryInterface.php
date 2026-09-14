<?php

namespace Modules\CORE\Support\Contracts;

use Modules\CORE\Support\Data\DataTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Implemented by repositories whose records are listed in a datatable.
 *
 * Segregating this from {@see RepositoryInterface} means a repository that is
 * never rendered in a table does not have to implement pagination.
 */
interface DataTableRepositoryInterface extends RepositoryInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function paginate(DataTableQuery $query): LengthAwarePaginator;

    /**
     * Columns matched by the datatable's global search input.
     *
     * Dot notation (`department.title`) searches a relation.
     *
     * @return array<int, string>
     */
    public function searchableColumns(): array;

    /**
     * Columns the datatable is allowed to sort on. Anything outside this
     * whitelist is ignored, which prevents arbitrary `ORDER BY` input.
     *
     * @return array<int, string>
     */
    public function sortableColumns(): array;
}
