<?php

namespace Modules\DOCTOR\Services;

use Modules\CORE\Support\Services\BaseService;
use Illuminate\Support\Str;
use Modules\DOCTOR\Interfaces\PageSectionRepositoryInterface;
use Modules\DOCTOR\Models\PageSection;

class PageSectionService extends BaseService
{
    public function __construct(PageSectionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function findByKey(string $key): ?PageSection
    {
        return $this->repository->findByKey($key);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        if (isset($data['section_key'])) {
            $data['section_key'] = Str::slug($data['section_key'], '_');
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        return $data;
    }
}
