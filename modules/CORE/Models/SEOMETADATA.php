<?php

namespace Modules\CORE\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\CORE\Database\Factories\SeoMetaDataFactory;

/**
 * Polymorphic SEO record. Any module model can own one via the `seoable` morph.
 */
class SeoMetaData extends Model
{
    /** @use HasFactory<SeoMetaDataFactory> */
    use HasFactory;

    protected $table = 'seo_meta_data';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_json',
    ];

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
        ];
    }

    protected $attributes = [
        'robots' => 'index, follow',
        'og_type' => 'website',
        'twitter_card' => 'summary_large_image',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Human label for the owning record, used by the datatable.
     */
    public function getOwnerLabelAttribute(): string
    {
        if ($this->seoable_type === null) {
            return 'Global';
        }

        return class_basename($this->seoable_type).' #'.$this->seoable_id;
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $query) use ($term): void {
            $query
                ->where('meta_title', 'like', "%{$term}%")
                ->orWhere('meta_description', 'like', "%{$term}%")
                ->orWhere('canonical_url', 'like', "%{$term}%");
        });
    }

    protected static function newFactory(): Factory
    {
        return SeoMetaDataFactory::new();
    }
}
