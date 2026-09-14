<?php

namespace Modules\CORE\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Modules\CORE\Interfaces\SEOMetaDataRepositoryInterface;
use Modules\CORE\Models\SeoMetaData;

class SeoMetaDataRepository extends BaseRepository implements SEOMetaDataRepositoryInterface
{
    /** @var class-string<SeoMetaData> */
    protected string $modelClass = SeoMetaData::class;

    protected array $searchable = ['meta_title', 'meta_description', 'canonical_url'];

    protected array $sortable = ['id', 'meta_title', 'seoable_type', 'created_at', 'updated_at'];

    protected array $filterable = [
        'seoable_type' => 'seoable_type',
    ];

    protected array $with = ['seoable'];

    public function firstOrNewFor(Model $owner): SeoMetaData
    {
        return SeoMetaData::query()->firstOrNew([
            'seoable_type' => $owner->getMorphClass(),
            'seoable_id' => $owner->getKey(),
        ]);
    }

    public function syncFor(Model $owner, array $data): SeoMetaData
    {
        return SeoMetaData::query()->updateOrCreate(
            [
                'seoable_type' => $owner->getMorphClass(),
                'seoable_id' => $owner->getKey(),
            ],
            $data,
        );
    }

    public function seoableTypes(): array
    {
        return [
            \Modules\DOCTOR\Models\Department::class => 'Department',
            \Modules\DOCTOR\Models\Doctor::class => 'Doctor',
        ];
    }
}
