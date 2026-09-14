<?php

namespace Modules\DOCTOR\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Modules\DOCTOR\Database\Factories\PageSectionFactory;

/**
 * Editable marketing copy for a named section of the public site
 * (`hero`, `what_brings_you_here`, `urgent_care`, `bottom_cta`, ...).
 */
class PageSection extends Model
{
    /**
     * Blocks every department page is expected to carry. The key stays free
     * text so an editor can add a one-off block, these are just the presets.
     */
    public const SECTION_KEYS = [
        'hero' => 'Hero banner',
        'what_brings_you_here' => 'What brings you here',
        'urgent_care' => 'Urgent care',
        'bottom_cta' => 'Bottom call to action',
    ];

    /** @use HasFactory<PageSectionFactory> */
    use HasFactory;

    protected $fillable = [
        'department_id',
        'section_key',
        'badge',
        'title',
        'subtitle',
        'primary_button_text',
        'primary_button_url',
        'secondary_button_text',
        'secondary_button_url',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The department page this block appears on.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Look up a section by key, cached so the public site never hits the
     * database on every render.
     */
    public static function findByKey(string $key): ?self
    {
        return Cache::remember(
            "doctor.page_section.{$key}",
            now()->addHour(),
            fn (): ?self => static::query()->where('section_key', $key)->first(),
        );
    }

    protected static function booted(): void
    {
        $forget = static function (self $section): void {
            Cache::forget("doctor.page_section.{$section->section_key}");
        };

        static::saved($forget);
        static::deleted($forget);
    }

    protected static function newFactory(): Factory
    {
        return PageSectionFactory::new();
    }
}
