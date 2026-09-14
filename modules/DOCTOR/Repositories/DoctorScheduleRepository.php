<?php

namespace Modules\DOCTOR\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Modules\DOCTOR\Interfaces\DoctorScheduleRepositoryInterface;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorSchedule;

class DoctorScheduleRepository extends BaseRepository implements DoctorScheduleRepositoryInterface
{
    /** @var class-string<DoctorSchedule> */
    protected string $modelClass = DoctorSchedule::class;

    protected array $searchable = ['doctor.name', 'day_of_week'];

    protected array $sortable = [
        'id',
        'day_of_week',
        'start_time',
        'end_time',
        'max_patients',
        'consultation_type',
        'availability_status',
        'is_active',
        'created_at',
        'doctor.name',
    ];

    protected array $filterable = [
        'doctor_id' => 'doctor_id',
        'day_of_week' => 'day_of_week',
        'consultation_type' => 'consultation_type',
        'availability_status' => 'availability_status',
        'is_active' => 'is_active',
    ];

    protected array $with = ['doctor'];

    public function doctorOptions(): array
    {
        return Doctor::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    public function dayOptions(): array
    {
        return array_combine(DoctorSchedule::DAYS, DoctorSchedule::DAYS);
    }

    public function consultationTypeOptions(): array
    {
        return [
            'in_person' => 'In person',
            'online' => 'Online',
            'both' => 'Both',
        ];
    }

    public function availabilityOptions(): array
    {
        return [
            'available' => 'Available',
            'limited' => 'Limited',
            'booked_out' => 'Booked out',
        ];
    }

    /**
     * Weekdays are an enum, so alphabetical ordering would list Friday first.
     * Order by their position in the working week instead.
     */
    protected function applySort(Builder $query, string $column, string $direction): void
    {
        if ($column !== 'day_of_week') {
            parent::applySort($query, $column, $direction);

            return;
        }

        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $cases = collect(DoctorSchedule::DAYS)
            ->map(fn (string $day, int $index): string => "WHEN '{$day}' THEN {$index}")
            ->implode(' ');

        $query->orderByRaw("CASE day_of_week {$cases} ELSE 99 END {$direction}");
        $query->orderBy('start_time', $direction);
    }
}
