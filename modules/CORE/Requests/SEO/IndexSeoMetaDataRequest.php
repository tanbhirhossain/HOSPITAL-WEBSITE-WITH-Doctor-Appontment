<?php

namespace Modules\CORE\Requests\SEO;

use Modules\CORE\Support\Http\Requests\DataTableRequest;
use Illuminate\Validation\Rule;
use Modules\CORE\Interfaces\SEOMetaDataRepositoryInterface;

class IndexSeoMetaDataRequest extends DataTableRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function filterRules(): array
    {
        $types = array_keys(app(SEOMetaDataRepositoryInterface::class)->seoableTypes());

        return [
            'filters.seoable_type' => ['nullable', 'array'],
            'filters.seoable_type.*' => ['string', Rule::in($types)],
        ];
    }
}
