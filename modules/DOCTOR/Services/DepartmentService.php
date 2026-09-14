<?php

namespace Modules\DOCTOR\Services;

use Modules\CORE\Support\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CORE\Interfaces\SEOMetaDataRepositoryInterface;
use Modules\DOCTOR\Interfaces\DepartmentRepositoryInterface;
use Modules\DOCTOR\Models\Department;
use Modules\DOCTOR\Models\PageSection;

class DepartmentService extends BaseService
{
    public function __construct(
        DepartmentRepositoryInterface $repository,
        private readonly SEOMetaDataRepositoryInterface $seo,
    ) {
        parent::__construct($repository);
    }

    /**
     * @return array<int, array{id: int, title: string, category: ?string}>
     */
    public function departmentOptions(bool $activeOnly = false): array
    {
        return $this->repository->departmentOptions($activeOnly);
    }

    /**
     * Preset content blocks offered by the form's section dropdown.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function sectionKeyOptions(): array
    {
        return collect(PageSection::SECTION_KEYS)
            ->map(fn (string $label, string $key): array => ['value' => $key, 'label' => $label])
            ->values()
            ->all();
    }

    public function toggleFeatured(int|string|Model $department): bool
    {
        $resolved = $department instanceof Model ? $department->getKey() : $department;

        return $this->repository->toggleFeatured($resolved);
    }

    /**
     * Persist a department together with every section of its form:
     * page content blocks, symptom selection and the SEO row.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $pageSections
     * @param  array<int, mixed>  $symptomIds
     * @param  array<string, mixed>  $seoData
     */
    public function saveWithRelations(
        ?int $id,
        array $data,
        array $pageSections = [],
        array $symptomIds = [],
        array $seoData = [],
    ): Department {
        /** @var Department $department */
        $department = $id === null ? $this->create($data) : $this->update($id, $data);

        $department = $department->refresh();

        return DB::transaction(function () use ($department, $pageSections, $symptomIds, $seoData): Department {
            $this->repository->syncPageSections($department, $pageSections);
            $this->repository->syncSymptoms($department, $symptomIds);

            if ($seoData !== []) {
                $this->seo->syncFor($department, $seoData);
            }

            return $department->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        if (isset($data['title']) && blank($data['slug'] ?? null)) {
            $data['slug'] = Str::slug($data['title']);
        }

        foreach (['is_popular_search', 'is_featured', 'is_active'] as $flag) {
            if (array_key_exists($flag, $data)) {
                $data[$flag] = filter_var($data[$flag], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $data;
    }
}
