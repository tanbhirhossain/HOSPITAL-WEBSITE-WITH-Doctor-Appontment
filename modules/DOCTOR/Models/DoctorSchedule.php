<?php

namespace Modules\DOCTOR\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\DOCTOR\Database\Factories\DoctorScheduleFactory;

/**
 * One recurring consulting slot for a doctor.
 */
class DoctorSchedule extends Model
{
    /** @use HasFactory<DoctorScheduleFactory> */
    use HasFactory;

    public const DAYS = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

    public const CONSULTATION_TYPES = ['in_person', 'online', 'both'];

    public const AVAILABILITY_STATUSES = ['available', 'limited', 'booked_out'];

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'consultation_type',
        'availability_status',
        'max_patients',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_patients' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected $attributes = [
        'consultation_type' => 'in_person',
        'availability_status' => 'available',
        'max_patients' => 20,
        'is_active' => true,
    ];

    /**
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Human-friendly time range, e.g. "10:00 AM – 02:00 PM".
     */
    public function getTimeRangeAttribute(): string
    {
        return sprintf(
            '%s – %s',
            $this->start_time ? date('h:i A', strtotime((string) $this->start_time)) : '—',
            $this->end_time ? date('h:i A', strtotime((string) $this->end_time)) : '—',
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDoctor(Builder $query, ?int $doctorId): Builder
    {
        return $doctorId ? $query->where('doctor_id', $doctorId) : $query;
    }

    public function scopeOnDay(Builder $query, ?string $day): Builder
    {
        return $day ? $query->where('day_of_week', $day) : $query;
    }

    protected static function newFactory(): Factory
    {
        return DoctorScheduleFactory::new();
    }
}
