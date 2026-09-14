<?php

namespace Modules\DOCTOR\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\DOCTOR\Database\Factories\DepartmentCategoryFactory;

/**
 * A grouping ("Medicine", "Surgery", ...) shown above the department grid.
 */
class DepartmentCategory extends Model
{
    /** @use HasFactory<DepartmentCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Derive the slug from the name when one is not supplied.
     */
    protected static function booted(): void
    {
        static::saving(function (self $category): void {
            if (blank($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * @return HasMany<Department, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * @return Collection<int, Department>
     */
    public function activeDepartments(): Collection
    {
        return $this->departments()->where('is_active', true)->orderBy('sort_order')->get();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getDepartmentsCountAttribute(): int
    {
        return $this->departments_count ?? $this->departments()->count();
    }

    protected static function newFactory(): Factory
    {
        return DepartmentCategoryFactory::new();
    }
}
