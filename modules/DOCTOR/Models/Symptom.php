<?php

namespace Modules\DOCTOR\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\DOCTOR\Database\Factories\SymptomFactory;

/**
 * A "what brings you here" tile on the public homepage.
 */
class Symptom extends Model
{
    /** @use HasFactory<SymptomFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'icon',
        'link_url',
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
     * Departments that advertise this symptom.
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    protected static function newFactory(): Factory
    {
        return SymptomFactory::new();
    }
}
