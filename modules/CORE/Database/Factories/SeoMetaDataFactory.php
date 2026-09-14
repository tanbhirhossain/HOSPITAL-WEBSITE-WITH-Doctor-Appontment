<?php

namespace Modules\CORE\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CORE\Models\SeoMetaData;

/**
 * @extends Factory<SeoMetaData>
 */
class SeoMetaDataFactory extends Factory
{
    protected $model = SeoMetaData::class;

    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'meta_title' => $title,
            'meta_description' => fake()->sentence(18),
            'meta_keywords' => implode(', ', fake()->words(5)),
            'canonical_url' => fake()->url(),
            'robots' => 'index, follow',
            'og_title' => $title,
            'og_description' => fake()->sentence(12),
            'og_image' => null,
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $title,
            'twitter_description' => fake()->sentence(12),
            'twitter_image' => null,
            'schema_json' => null,
        ];
    }
}
