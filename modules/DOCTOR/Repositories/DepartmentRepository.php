<?php

namespace Modules\DOCTOR\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DOCTOR\Interfaces\DepartmentRepositoryInterface;
use Modules\DOCTOR\Models\Department;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    /** @var class-string<Department> */
    protected string $modelClass = Department::class;

    protected array $searchable = ['title', 'slug', 'short_description'];

    protected array $sortable = ['id', 'title', 'sort_order', 'is_active', 'is_featured', 'created_at', 'category.name'];

    protected array $filterable = [
        'department_category_id' => 'department_category_id',
        'is_active' => 'is_active',
        'is_featured' => 'is_featured',
    ];

    protected array $with = ['category'];

    protected function newQuery(): Builder
    {
        return parent::newQuery()->withCount('doctors');
    }

    public function departmentOptions(bool $activeOnly = false): array
    {
        return Department::query()
            ->with('category')
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('title')
            ->get()
            ->map(fn (Department $department): array => [
                'id' => $department->id,
                'title' => $department->title,
                'category' => $department->category?->name,
            ])
            ->all();
    }


    /**
     * Replace the department's content blocks. Sections are keyed by
     * `section_key`, so editing one keeps its primary key and audit history.
     *
     * @param  array<int, array<string, mixed>>  $sections
     */
    public function syncPageSections(Department $department, array $sections): void
    {
        $valid = collect($sections)
            ->filter(fn (mixed $row): bool => is_array($row) && filled($row['section_key'] ?? null))
            ->values()
            ->map(fn (array $row): array => [
                'section_key' => $this->normaliseSectionKey((string) $row['section_key']),
                'badge' => $row['badge'] ?? null,
                'title' => filled($row['title'] ?? null)
                    ? (string) $row['title']
                    : Str::headline((string) $row['section_key']),
                'subtitle' => $row['subtitle'] ?? null,
                'primary_button_text' => $row['primary_button_text'] ?? null,
                'primary_button_url' => $row['primary_button_url'] ?? null,
                'secondary_button_text' => $row['secondary_button_text'] ?? null,
                'secondary_button_url' => $row['secondary_button_url'] ?? null,
                'image' => $row['image'] ?? null,
                'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ])
            ->unique(fn (array $row): string => $row['section_key'])
            ->values()
            ->all();

        DB::transaction(function () use ($department, $valid): void {
            $keys = [];

            foreach ($valid as $row) {
                $department->pageSections()->updateOrCreate(
                    ['department_id' => $department->getKey(), 'section_key' => $row['section_key']],
                    $row,
                );

                $keys[] = $row['section_key'];
            }

            $department->pageSections()->whereNotIn('section_key', $keys === [] ? [''] : $keys)->delete();
        });
    }

    /**
     * Section keys stay snake_case (`bottom_cta`) because that is the shape
     * the public pages look them up by. Spaces and dashes are folded into
     * underscores so `Str::slug` never turns `bottom_cta` into `bottom-cta`.
     */
    private function normaliseSectionKey(string $key): string
    {
        $key = Str::lower(trim($key));
        $key = (string) preg_replace('/[\s-]+/', '_', $key);
        $key = (string) preg_replace('/[^a-z0-9_]/', '', $key);
        $key = (string) preg_replace('/_+/', '_', $key);

        return trim($key, '_');
    }

    /**
     * Attach the chosen symptoms, remembering the order the editor picked.
     *
     * @param  array<int, mixed>  $symptomIds
     */
    public function syncSymptoms(Department $department, array $symptomIds): void
    {
        $sync = collect($symptomIds)
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->mapWithKeys(fn (int $id, int $index): array => [$id => ['sort_order' => $index]])
            ->all();

        $department->symptoms()->sync($sync);
    }

    public function toggleFeatured(int|string $id): bool
    {
        $department = $this->findOrFail($id);

        return (bool) $department->update(['is_featured' => ! $department->is_featured]);
    }
}
