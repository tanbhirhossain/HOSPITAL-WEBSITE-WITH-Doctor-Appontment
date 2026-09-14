<?php

namespace Modules\CORE\Requests\SEO;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeoMetaDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'meta_title' => ['required', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'robots' => ['nullable', 'string', Rule::in(['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'])],
            'og_title' => ['nullable', 'string', 'max:120'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'og_type' => ['nullable', 'string', 'in:website,article,product,profile'],
            'twitter_card' => ['nullable', 'string', 'in:summary,summary_large_image,app,player'],
            'twitter_title' => ['nullable', 'string', 'max:120'],
            'twitter_description' => ['nullable', 'string', 'max:320'],
            'twitter_image' => ['nullable', 'string', 'max:500'],
            'schema_json' => ['nullable', 'json'],

            // Polymorphic owner — optional so global/default records are possible.
            'seoable_type' => ['nullable', 'string', Rule::in(array_keys(app(\Modules\CORE\Interfaces\SEOMetaDataRepositoryInterface::class)->seoableTypes()))],
            'seoable_id' => ['required_with:seoable_type', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'meta_title' => 'meta title',
            'meta_description' => 'meta description',
            'canonical_url' => 'canonical URL',
            'schema_json' => 'schema markup',
        ];
    }
}
