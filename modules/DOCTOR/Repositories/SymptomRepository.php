<?php

namespace Modules\DOCTOR\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Modules\DOCTOR\Interfaces\SymptomRepositoryInterface;
use Modules\DOCTOR\Models\Symptom;

class SymptomRepository extends BaseRepository implements SymptomRepositoryInterface
{
    /** @var class-string<Symptom> */
    protected string $modelClass = Symptom::class;

    protected array $searchable = ['title', 'link_url'];

    protected array $sortable = ['id', 'title', 'sort_order', 'is_active', 'created_at'];

    protected array $filterable = [
        'is_active' => 'is_active',
    ];

    public function orderedList(bool $activeOnly = false): array
    {
        return Symptom::query()
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (Symptom $symptom): array => [
                'id' => $symptom->id,
                'title' => $symptom->title,
                'icon' => $symptom->icon,
                'link_url' => $symptom->link_url,
            ])
            ->all();
    }

    /**
     * Active symptoms for a select box.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function symptomOptions(): array
    {
        return Symptom::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Symptom $symptom): array => [
                'value' => $symptom->id,
                'label' => $symptom->title,
            ])
            ->all();
    }
}
