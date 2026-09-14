<?php

namespace Modules\CORE\Support\Services;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;
use Modules\CORE\Support\Contracts\RepositoryInterface;
use Modules\CORE\Support\Data\DataTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Application layer sitting between controllers and repositories.
 *
 * Services own the business rules (transactions, side effects, defaults) so
 * controllers stay a thin HTTP adapter and repositories stay a thin
 * persistence adapter.
 */
abstract class BaseService
{
    public function __construct(
        protected readonly RepositoryInterface&DataTableRepositoryInterface $repository,
    ) {}

    public function paginate(DataTableQuery $query): LengthAwarePaginator
    {
        return $this->repository->paginate($query);
    }

    /**
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->repository->all($columns);
    }

    public function find(int|string $id): ?Model
    {
        return $this->repository->find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        return DB::transaction(fn (): Model => $this->repository->create($this->prepare($data)));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int|string|Model $model, array $data): Model
    {
        return DB::transaction(fn (): Model => $this->repository->update($model, $this->prepare($data)));
    }

    public function delete(int|string|Model $model): bool
    {
        return DB::transaction(fn (): bool => $this->repository->delete($model));
    }

    /**
     * Flip the `is_active` flag — the single most common admin action.
     */
    public function toggleStatus(int|string|Model $model): Model
    {
        $resolved = $model instanceof Model ? $model : $this->findOrFail($model);

        return $this->update($resolved, ['is_active' => ! $resolved->is_active]);
    }

    public function count(): int
    {
        return $this->repository->count();
    }

    /**
     * Build the datatable query object using the repository's own whitelists,
     * so controllers never hard-code column names.
     */
    public function dataTableQuery(
        \Illuminate\Http\Request $request,
        string $defaultSort = 'id',
        string $defaultDirection = 'desc',
    ): DataTableQuery {
        return DataTableQuery::fromRequest(
            $request,
            $this->repository->searchableColumns(),
            $defaultSort,
            $defaultDirection,
        );
    }

    /**
     * Shape a paginator into the array the <DataTable> component expects.
     *
     * @param  callable(\Illuminate\Database\Eloquent\Model): array<string, mixed>|null  $transform
     * @return array<string, mixed>
     */
    public function toDataTable(LengthAwarePaginator $paginator, ?callable $transform = null): array
    {
        if ($transform !== null) {
            $paginator = $paginator->through($transform);
        }

        return $paginator->toArray();
    }

    /**
     * id => label pairs for filter/select dropdowns.
     *
     * @param  callable(\Illuminate\Database\Eloquent\Builder): mixed|null  $constraint
     * @return array<int|string, string>
     */
    public function options(string $label = 'name', ?callable $constraint = null, string $key = 'id'): array
    {
        $query = $this->repository->query();

        if ($constraint !== null) {
            $constraint($query);
        }

        return $query->orderBy($label)->pluck($label, $key)->toArray();
    }

    /**
     * Normalise / derive values before they hit the persistence layer.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        return $data;
    }
}
