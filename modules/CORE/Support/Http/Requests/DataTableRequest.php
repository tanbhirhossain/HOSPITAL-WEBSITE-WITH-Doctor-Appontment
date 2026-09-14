<?php

namespace Modules\CORE\Support\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the query string every datatable sends.
 *
 * Subclasses add their own facet rules through `filterRules()` so each listing
 * endpoint has explicit, testable validation instead of trusting user input.
 */
class DataTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50, 100])],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'filters' => ['nullable', 'array'],
            ...$this->filterRules(),
        ];
    }

    /**
     * Extra rules for the `filters.*` payload of this specific listing.
     *
     * @return array<string, mixed>
     */
    protected function filterRules(): array
    {
        return [];
    }

    /**
     * The full "view state" of the datatable, so the Vue component can
     * rehydrate its search box, sort indicator and page size after a reload.
     *
     * @return array{search: string, sort: string, direction: string, per_page: int}
     */
    public function queryState(): array
    {
        return [
            'search' => trim((string) $this->input('search', '')),
            'sort' => (string) ($this->input('sort', '') ?? ''),
            'direction' => $this->input('direction') === 'asc' ? 'asc' : 'desc',
            'per_page' => (int) ($this->input('per_page') ?: 15),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $keys
     * @return array<string, mixed>
     */
    public function filters(?array $keys = null): array
    {
        $filters = (array) $this->input('filters', []);

        if ($keys === null) {
            return array_filter(
                $filters,
                static fn (mixed $value): bool => ! ($value === null || $value === '' || $value === []),
            );
        }

        return collect($keys)->mapWithKeys(fn (string $key): array => [$key => $filters[$key] ?? null])->all();
    }
}
