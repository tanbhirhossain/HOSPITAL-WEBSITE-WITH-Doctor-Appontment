<?php

namespace Modules\DOCTOR\Services;

use Modules\CORE\Support\Services\BaseService;
use Modules\DOCTOR\Interfaces\SymptomRepositoryInterface;

class SymptomService extends BaseService
{
    public function __construct(SymptomRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function orderedList(bool $activeOnly = false): array
    {
        return $this->repository->orderedList($activeOnly);
    }

    /**
     * Rewrite the whole ordered list in one go after a drag-and-drop reorder.
     *
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $position => $id) {
            $this->update((int) $id, ['sort_order' => $position]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        foreach (['title', 'icon', 'link_url'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        return $data;
    }

    /**
     * Every active symptom, for the department form's selection section.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function symptomOptions(): array
    {
        return $this->repository->symptomOptions();
    }
}
