<?php

namespace Modules\CORE\Services\SEO;

use Modules\CORE\Support\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Modules\CORE\Interfaces\SEOMetaDataRepositoryInterface;
use Modules\CORE\Models\SeoMetaData;

class SeoMetaDataService extends BaseService
{
    public function __construct(SEOMetaDataRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function syncFor(Model $owner, array $data): SeoMetaData
    {
        return $this->repository->syncFor($owner, $this->prepare($data));
    }

    /**
     * Types that may own SEO metadata, for the "attach to" dropdown.
     *
     * @return array<string, string>
     */
    public function seoableTypes(): array
    {
        return $this->repository->seoableTypes();
    }

    /**
     * Keep empty strings out of the database and normalise keyword lists so
     * the meta tag output stays predictable.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        $data = array_map(
            static fn (mixed $value): mixed => is_string($value) ? trim($value) : $value,
            $data,
        );

        if (isset($data['meta_keywords']) && is_string($data['meta_keywords'])) {
            $data['meta_keywords'] = collect(explode(',', $data['meta_keywords']))
                ->map(fn (string $keyword): string => trim($keyword))
                ->filter()
                ->implode(', ');
        }

        if (array_key_exists('schema_json', $data)) {
            $data['schema_json'] = $this->normaliseSchema($data['schema_json']);
        }

        return array_filter(
            $data,
            static fn (mixed $value): bool => ! ($value === '' || $value === null),
        );
    }

    private function normaliseSchema(mixed $schema): ?array
    {
        if ($schema === null || $schema === '') {
            return null;
        }

        if (is_array($schema)) {
            return $schema;
        }

        $decoded = json_decode((string) $schema, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
