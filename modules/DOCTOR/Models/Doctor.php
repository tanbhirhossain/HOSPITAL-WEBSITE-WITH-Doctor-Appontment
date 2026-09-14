<?php

namespace Modules\DOCTOR\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Modules\CORE\Models\SeoMetaData;
use Modules\DOCTOR\Database\Factories\DoctorFactory;

class Doctor extends Model
{
    /** @use HasFactory<DoctorFactory> */
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'slug',
        'designation',
        'specialty',
        'qualification',
        'experience',
        'hospital_name',
        'location',
        'profile_photo',
        'bio',
        'experience_years',
        'rating',
        'reviews_count',
        'social_links',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'experience_years' => 'integer',
            'reviews_count' => 'integer',
            'rating' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $attributes = [
        'hospital_name' => 'AMZ Hospital Ltd.',
        'location' => 'AMZ Hospital, Dhaka',
        'rating' => 5.00,
        'is_active' => true,
        'is_featured' => false,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $doctor): void {
            if (blank($doctor->slug)) {
                $doctor->slug = Str::slug($doctor->name);
            }
        });
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return HasMany<DoctorExpertise, $this>
     */
    public function expertises(): HasMany
    {
        return $this->hasMany(DoctorExpertise::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<DoctorSchedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMetaData::class, 'seoable');
    }

    /**
     * Always hand the UI a usable image, even when no photo was uploaded.
     *
     * @return Attribute<string, never>
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(
            fn (): string => filled($this->profile_photo)
                ? (string) $this->profile_photo
                : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0d9488&color=fff&size=256',
        );
    }

    public function getInitialsAttribute(): string
    {
        return (string) str($this->name)
            ->explode(' ')
            ->filter()
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInDepartment(Builder $query, ?int $departmentId): Builder
    {
        return $departmentId ? $query->where('department_id', $departmentId) : $query;
    }

    public function getExpertisesCountAttribute(): int
    {
        return $this->expertises_count ?? $this->expertises()->count();
    }

    public function getSchedulesCountAttribute(): int
    {
        return $this->schedules_count ?? $this->schedules()->count();
    }

    protected static function newFactory(): Factory
    {
        return DoctorFactory::new();
    }
}
