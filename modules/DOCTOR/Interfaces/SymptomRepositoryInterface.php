<?php

namespace Modules\DOCTOR\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;

interface SymptomRepositoryInterface extends DataTableRepositoryInterface
{
    /**
     * Symptoms in display order, optionally active only.
     *
     * @return array<int, array<string, mixed>>
     */
    public function orderedList(bool $activeOnly = false): array;

    /**
     * Active symptoms for a select box.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function symptomOptions(): array;
}
