<?php

namespace Modules\DOCTOR\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;
use Modules\DOCTOR\Models\Department;

interface DepartmentRepositoryInterface extends DataTableRepositoryInterface
{
    /**
     * Departments ready for a select box, grouped by their category.
     *
     * @return array<int, array{id: int, title: string, category: ?string}>
     */
    public function departmentOptions(bool $activeOnly = false): array;

    /**
     * Replace the department's page content blocks (keyed by `section_key`).
     *
     * @param  array<int, array<string, mixed>>  $sections
     */
    public function syncPageSections(Department $department, array $sections): void;

    /**
     * Attach the chosen symptoms, preserving the editor's ordering.
     *
     * @param  array<int, mixed>  $symptomIds
     */
    public function syncSymptoms(Department $department, array $symptomIds): void;

    /**
     * Toggle the "featured on the homepage" flag.
     */
    public function toggleFeatured(int|string $id): bool;
}
