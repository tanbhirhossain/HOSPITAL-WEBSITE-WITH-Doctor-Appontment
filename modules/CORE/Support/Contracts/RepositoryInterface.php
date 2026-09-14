<?php

namespace Modules\CORE\Support\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Read/write contract every module repository honours.
 *
 * Keeping this surface small means a repository can be swapped (Eloquent,
 * an external API, an in-memory test double) without touching services.
 */
interface RepositoryInterface
{
    /**
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection;

    public function find(int|string $id, array $columns = ['*']): ?Model;

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>|null  $expected
     * @return ($expected is null ? Model : TModel)
     */
    public function findOrFail(int|string $id, ?string $expected = null): Model;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int|string|Model $model, array $data): Model;

    public function delete(int|string|Model $model): bool;

    public function count(): int;

    public function query(): Builder;
}
