<?php

namespace Modules\DOCTOR\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Modules\DOCTOR\Interfaces\PageSectionRepositoryInterface;
use Modules\DOCTOR\Models\PageSection;

class PageSectionRepository extends BaseRepository implements PageSectionRepositoryInterface
{
    /** @var class-string<PageSection> */
    protected string $modelClass = PageSection::class;

    protected array $searchable = ['section_key', 'title', 'badge', 'subtitle'];

    protected array $sortable = ['id', 'section_key', 'title', 'is_active', 'created_at'];

    protected array $filterable = [
        'is_active' => 'is_active',
    ];

    public function findByKey(string $key): ?PageSection
    {
        return PageSection::findByKey($key);
    }
}
