<?php

namespace Modules\DOCTOR\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\DOCTOR\Interfaces\DoctorRepositoryInterface;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorExpertise;

class DoctorRepository extends BaseRepository implements DoctorRepositoryInterface
{
    /** @var class-string<Doctor> */
    protected string $modelClass = Doctor::class;

    protected array $searchable = ['name', 'slug', 'specialty', 'qualification', 'designation', 'department.title'];

    protected array $sortable = [
        'id',
        'name',
        'specialty',
        'experience_years',
        'rating',
        'is_active',
        'is_featured',
        'created_at',
        'department.title',
    ];

    protected array $filterable = [
        'department_id' => 'department_id',
        'is_active' => 'is_active',
        'is_featured' => 'is_featured',
    ];

    protected array $with = ['department'];

    protected function newQuery(): Builder
    {
        return parent::newQuery()->withCount(['expertises', 'schedules']);
    }

    public function departmentOptions(): array
    {
        return Doctor::query()
            ->toBase()
            ->select('departments.id', 'departments.title')
            ->join('departments', 'departments.id', '=', 'doctors.department_id')
            ->distinct()
            ->orderBy('departments.title')
            ->pluck('title', 'id')
            ->all();
    }

    public function syncExpertises(Model $doctor, array $expertises): void
    {
        $valid = collect($expertises)
            ->filter(fn (mixed $row): bool => is_array($row) && filled($row['title'] ?? null))
            ->values()
            ->map(fn (array $row, int $index): array => [
                'title' => trim((string) $row['title']),
                'description' => trim((string) ($row['description'] ?? '')),
                'icon' => $row['icon'] ?? null,
                'sort_order' => $row['sort_order'] ?? $index,
            ])
            ->all();

        DB::transaction(function () use ($doctor, $valid): void {
            $doctor->expertises()->delete();

            foreach ($valid as $row) {
                $doctor->expertises()->create($row);
            }
        });
    }


    /**
     * Replace the doctor's weekly timetable from the form repeater.
     *
     * @param  array<int, array<string, mixed>>  $schedules
     */
    public function syncSchedules(Doctor $doctor, array $schedules): void
    {
        $valid = collect($schedules)
            ->filter(fn (mixed $row): bool => is_array($row)
                && filled($row['day_of_week'] ?? null)
                && filled($row['start_time'] ?? null)
                && filled($row['end_time'] ?? null))
            ->map(fn (array $row): array => [
                'day_of_week' => (string) $row['day_of_week'],
                'start_time' => (string) $row['start_time'],
                'end_time' => (string) $row['end_time'],
                'consultation_type' => $row['consultation_type'] ?? 'in_person',
                'availability_status' => $row['availability_status'] ?? 'available',
                'max_patients' => max(1, (int) ($row['max_patients'] ?? 20)),
                'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ])
            ->unique(fn (array $row): string => $row['day_of_week'].'|'.$row['start_time'])
            ->values()
            ->all();

        DB::transaction(function () use ($doctor, $valid): void {
            $doctor->schedules()->delete();

            foreach ($valid as $row) {
                $doctor->schedules()->create($row);
            }
        });
    }

    /**
     * Pull every department (not just ones that already have doctors) so the
     * filter stays useful on an empty table.
     *
     * @return array<int, string>
     */
    public function allDepartmentOptions(): array
    {
        return \Modules\DOCTOR\Models\Department::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    public function toggleFeatured(int|string $id): bool
    {
        $doctor = $this->findOrFail($id);

        return (bool) $doctor->update(['is_featured' => ! $doctor->is_featured]);
    }

    /**
     * Expertise rows are managed through the doctor form, so appointments of
     * a doctor should cascade — guard against orphaned expertise records.
     */
    public function delete(int|string|Model $model): bool
    {
        return DB::transaction(function () use ($model): bool {
            $doctor = $this->resolveModel($model);

            DoctorExpertise::query()->where('doctor_id', $doctor->getKey())->delete();
            $doctor->schedules()->delete();

            return (bool) $doctor->delete();
        });
    }
}
