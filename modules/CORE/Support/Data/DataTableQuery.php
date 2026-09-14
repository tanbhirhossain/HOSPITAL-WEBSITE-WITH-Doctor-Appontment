<?php

namespace Modules\CORE\Support\Data;

use Illuminate\Http\Request;

/**
 * Immutable value object describing a single server-side datatable request.
 *
 * Carrying these parameters in a DTO (instead of passing the raw Request
 * around) keeps repositories free of any HTTP / transport concerns, which is
 * what allows them to be reused from commands, jobs and tests.
 */
final readonly class DataTableQuery
{
    /**
     * @param  array<int, string>  $searchable  Columns matched by the global search box.
     * @param  array<string, mixed>  $filters  Key/value pairs coming from the filter bar.
     */
    public function __construct(
        public string $search = '',
        public array $searchable = [],
        public array $filters = [],
        public string $sortBy = 'id',
        public string $sortDirection = 'desc',
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    /**
     * Build a query object from the current HTTP request.
     *
     * Sorting columns are whitelisted by the repository, so an unknown
     * `sort` value silently falls back to the default instead of reaching SQL.
     */
    public static function fromRequest(
        Request $request,
        array $searchable = [],
        string $defaultSort = 'id',
        string $defaultDirection = 'desc',
    ): self {
        $direction = strtolower((string) $request->input('direction', $defaultDirection));

        return new self(
            search: trim((string) $request->input('search', '')),
            searchable: $searchable,
            filters: self::normaliseFilters($request->input('filters', [])),
            sortBy: (string) ($request->input('sort') ?: $defaultSort),
            sortDirection: $direction === 'asc' ? 'asc' : 'desc',
            perPage: self::normalisePerPage($request->input('per_page')),
            page: max(1, (int) $request->input('page', 1)),
        );
    }

    public function hasSearch(): bool
    {
        return $this->search !== '' && $this->searchable !== [];
    }

    public function filter(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }

    public function hasFilter(string $key): bool
    {
        $value = $this->filters[$key] ?? null;

        if ($value === null || $value === '' || $value === []) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'filters' => $this->filters,
            'sort' => $this->sortBy,
            'direction' => $this->sortDirection,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }

    /**
     * @param  mixed  $filters
     * @return array<string, mixed>
     */
    private static function normaliseFilters(mixed $filters): array
    {
        if (! is_array($filters)) {
            return [];
        }

        return array_filter(
            $filters,
            static fn (mixed $value): bool => ! ($value === null || $value === '' || $value === []),
        );
    }

    private static function normalisePerPage(mixed $perPage): int
    {
        $perPage = (int) $perPage;

        if ($perPage <= 0) {
            return 15;
        }

        return min($perPage, 100);
    }
}
