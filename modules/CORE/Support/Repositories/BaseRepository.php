<?php

namespace Modules\CORE\Support\Repositories;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;
use Modules\CORE\Support\Data\DataTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Eloquent implementation of the datatable repository contract.
 *
 * Subclasses only declare *what* makes them different (the model, the
 * searchable/sortable/filterable columns) — every shared concern lives here.
 * The `applyFilter` hook keeps it open for extension without modification.
 */
abstract class BaseRepository implements DataTableRepositoryInterface
{
    /** @var class-string<Model> */
    protected string $modelClass;

    /**
     * Columns matched by the global search box. Dot notation searches a relation.
     *
     * @var array<int, string>
     */
    protected array $searchable = ['id'];

    /**
     * Columns the datatable may sort by.
     *
     * @var array<int, string>
     */
    protected array $sortable = ['id', 'created_at'];

    /**
     * Filter key => table column.
     *
     * @var array<string, string>
     */
    protected array $filterable = [];

    /**
     * Relations eager loaded on every read.
     *
     * @var array<int, string>
     */
    protected array $with = [];

    /**
     * Filter keys holding an ISO date range (`from` / `to`).
     *
     * @var array<int, string>
     */
    protected array $dateFilters = ['created_at'];

    protected function newQuery(): Builder
    {
        $query = $this->modelClass::query();

        if ($this->with !== []) {
            $query->with($this->with);
        }

        return $query;
    }

    public function query(): Builder
    {
        return $this->newQuery();
    }

    /**
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->newQuery()->get($columns);
    }

    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->newQuery()->find($id, $columns);
    }

    public function findOrFail(int|string $id, ?string $expected = null): Model
    {
        $model = $this->newQuery()->findOrFail($id);

        if ($expected !== null && ! $model instanceof $expected) {
            abort(500, sprintf('Repository returned %s, expected %s.', $model::class, $expected));
        }

        return $model;
    }

    public function create(array $data): Model
    {
        return $this->modelClass::query()->create($data)->refresh();
    }

    public function update(int|string|Model $model, array $data): Model
    {
        $model = $this->resolveModel($model);
        $model->fill($data);
        $model->save();

        return $model->refresh();
    }

    public function delete(int|string|Model $model): bool
    {
        return (bool) $this->resolveModel($model)->delete();
    }

    public function count(): int
    {
        return $this->modelClass::query()->count();
    }

    public function paginate(DataTableQuery $query): LengthAwarePaginator
    {
        $builder = $this->newQuery();

        $this->applySearch($builder, $query);
        $this->applyFilters($builder, $query->filters);
        $this->applySort($builder, $query->sortBy, $query->sortDirection);

        return $builder
            ->paginate(perPage: $query->perPage, page: $query->page)
            ->withQueryString();
    }

    /**
     * @return array<int, string>
     */
    public function searchableColumns(): array
    {
        return $this->searchable;
    }

    /**
     * @return array<int, string>
     */
    public function sortableColumns(): array
    {
        return $this->sortable;
    }

    /* ------------------------------------------------------------------ */
    /*  Extension points                                                   */
    /* ------------------------------------------------------------------ */

    /**
     * Hook for repository specific filtering. Called for every filter key that
     * is not covered by {@see $filterable}.
     */
    protected function applyFilter(Builder $query, string $key, mixed $value): void
    {
        // Intentionally empty: subclasses opt in to the filters they need.
    }

    /* ------------------------------------------------------------------ */
    /*  Internals                                                          */
    /* ------------------------------------------------------------------ */

    protected function applySearch(Builder $query, DataTableQuery $dataTableQuery): void
    {
        if (! $dataTableQuery->hasSearch()) {
            return;
        }

        $term = $this->escapeLike($dataTableQuery->search);
        $columns = array_intersect($dataTableQuery->searchable ?: $this->searchable, $this->searchable);

        if ($columns === []) {
            return;
        }

        $query->where(function (Builder $query) use ($columns, $term): void {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $relatedColumn] = explode('.', $column, 2);

                    $query->orWhereHas($relation, fn (Builder $relationQuery) => $relationQuery
                        ->where($relatedColumn, 'like', "%{$term}%"));

                    continue;
                }

                $query->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            if (in_array($key, $this->dateFilters, true) && is_array($value)) {
                $this->applyDateRange($query, $this->filterable[$key] ?? $key, $value);

                continue;
            }

            if (! array_key_exists($key, $this->filterable)) {
                $this->applyFilter($query, $key, $value);

                continue;
            }

            $column = $this->filterable[$key];

            if (str_contains($column, '.')) {
                [$relation, $relatedColumn] = explode('.', $column, 2);

                $query->whereHas($relation, function (Builder $relationQuery) use ($relatedColumn, $value): void {
                    is_array($value)
                        ? $relationQuery->whereIn($relatedColumn, $value)
                        : $relationQuery->where($relatedColumn, $value);
                });

                continue;
            }

            is_array($value)
                ? $query->whereIn($column, $value)
                : $query->where($column, $this->castFilterValue($value));
        }
    }

    /**
     * @param  array<string, mixed>  $range
     */
    protected function applyDateRange(Builder $query, string $column, array $range): void
    {
        if (! empty($range['from'])) {
            $query->whereDate($column, '>=', $range['from']);
        }

        if (! empty($range['to'])) {
            $query->whereDate($column, '<=', $range['to']);
        }
    }

    /**
     * SQLite/MySQL return integers for booleans, but the filter bar sends the
     * strings "0"/"1" — normalise so `where` still matches.
     */
    protected function castFilterValue(mixed $value): mixed
    {
        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        return $value;
    }

    protected function applySort(Builder $query, string $column, string $direction): void
    {
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        if (! in_array($column, $this->sortable, true)) {
            $column = in_array('created_at', $this->sortable, true) ? 'created_at' : $this->sortable[0];
            $direction = 'desc';
        }

        if (! str_contains($column, '.')) {
            $query->orderBy($query->getModel()->qualifyColumn($column), $direction);

            return;
        }

        [$relation, $relatedColumn] = explode('.', $column, 2);
        $this->sortByRelation($query, $relation, $relatedColumn, $direction);
    }

    /**
     * Sort by a column on a related table using an aliased left join so the
     * parent model's attributes (notably `id`) are never shadowed.
     */
    protected function sortByRelation(Builder $query, string $relation, string $relatedColumn, string $direction): void
    {
        $model = $query->getModel();

        if (! method_exists($model, $relation)) {
            return;
        }

        $relationInstance = $model->{$relation}();

        if (! $relationInstance instanceof BelongsTo) {
            return;
        }

        $related = $relationInstance->getRelated();
        $table = $related->getTable();
        $alias = 'sort_'.Str::snake($relation);

        $alreadyJoined = collect($query->getQuery()->joins ?? [])
            ->contains(fn (mixed $join): bool => is_object($join) && ($join->table ?? null) === "{$table} as {$alias}");

        if (! $alreadyJoined) {
            $query->select($model->getTable().'.*');
            $query->leftJoin(
                "{$table} as {$alias}",
                "{$alias}.{$related->getKeyName()}",
                '=',
                $model->getTable().'.'.$relationInstance->getForeignKeyName(),
            );
        }

        $query->orderBy("{$alias}.{$relatedColumn}", $direction);
    }

    protected function resolveModel(int|string|Model $model): Model
    {
        if ($model instanceof Model) {
            return $model;
        }

        return $this->findOrFail($model);
    }

    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
