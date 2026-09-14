<?php

namespace Modules\DOCTOR\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Modules\CORE\Models\SeoMetaData;
use Modules\DOCTOR\Database\Factories\DepartmentFactory;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;

    protected $fillable = [
        'department_category_id',
        'title',
        'slug',
        'short_description',
        'icon',
        'is_popular_search',
        'is_featured',
        'featured_image',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_popular_search' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $department): void {
            if (blank($department->slug)) {
                $department->slug = Str::slug($department->title);
            }
        });
    }

    /**
     * @return BelongsTo<DepartmentCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DepartmentCategory::class, 'department_category_id');
    }

    /**
     * @return HasMany<Doctor, $this>
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    /**
     * Content blocks rendered on this department's public page.
     */
    public function pageSections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('section_key');
    }

    /**
     * Symptoms this department advertises, in the order chosen by the editor.
     */
    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('department_symptom.sort_order');
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMetaData::class, 'seoable');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->where('is_popular_search', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function getDoctorsCountAttribute(): int
    {
        return $this->doctors_count ?? $this->doctors()->count();
    }

    protected static function newFactory(): Factory
    {
        return DepartmentFactory::new();
    }
}
