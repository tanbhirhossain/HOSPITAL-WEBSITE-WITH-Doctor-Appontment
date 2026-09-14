<?php

namespace Modules\CORE\Support\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

/**
 * The `seo { ... }` block that any owner (department, doctor, …) can submit
 * alongside its own fields.
 *
 * Kept in one place so every form that embeds the SEO section validates it
 * identically.
 */
trait SeoRules
{
    public const ROBOTS = ['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'];

    public const OG_TYPES = ['website', 'article', 'product', 'profile'];

    public const TWITTER_CARDS = ['summary', 'summary_large_image', 'app', 'player'];

    /**
     * @return array<string, mixed>
     */
    protected function seoRules(): array
    {
        return [
            'seo' => ['nullable', 'array'],
            'seo.meta_title' => ['nullable', 'string', 'max:120'],
            'seo.meta_description' => ['nullable', 'string', 'max:320'],
            'seo.meta_keywords' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'url', 'max:500'],
            'seo.robots' => ['nullable', 'string', Rule::in(self::ROBOTS)],
            'seo.og_title' => ['nullable', 'string', 'max:120'],
            'seo.og_description' => ['nullable', 'string', 'max:320'],
            'seo.og_image' => ['nullable', 'string', 'max:500'],
            'seo.og_type' => ['nullable', 'string', Rule::in(self::OG_TYPES)],
            'seo.twitter_card' => ['nullable', 'string', Rule::in(self::TWITTER_CARDS)],
            'seo.twitter_title' => ['nullable', 'string', 'max:120'],
            'seo.twitter_description' => ['nullable', 'string', 'max:320'],
            'seo.twitter_image' => ['nullable', 'string', 'max:500'],
            'seo.schema_json' => ['nullable', 'json'],
        ];
    }
}
