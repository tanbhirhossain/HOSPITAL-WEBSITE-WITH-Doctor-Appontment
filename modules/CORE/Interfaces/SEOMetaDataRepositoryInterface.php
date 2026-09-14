<?php

namespace Modules\CORE\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

interface SEOMetaDataRepositoryInterface extends DataTableRepositoryInterface
{
    /**
     * Resolve (or scaffold) the SEO row for a given model without persisting
     * anything, so forms can render blank defaults.
     */
    public function firstOrNewFor(Model $owner): \Modules\CORE\Models\SeoMetaData;

    /**
     * Create or update the SEO row attached to a model.
     *
     * @param  array<string, mixed>  $data
     */
    public function syncFor(Model $owner, array $data): \Modules\CORE\Models\SeoMetaData;

    /**
     * Class names that are allowed to own SEO metadata.
     *
     * @return array<string, string>
     */
    public function seoableTypes(): array;
}
