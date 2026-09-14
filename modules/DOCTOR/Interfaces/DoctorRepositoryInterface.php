<?php

namespace Modules\DOCTOR\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Modules\DOCTOR\Models\Doctor;

interface DoctorRepositoryInterface extends DataTableRepositoryInterface
{
    /**
     * Departments ready for a select box.
     *
     * @return array<int, string>
     */
    public function departmentOptions(): array;

    /**
     * Replace the doctor's expertise rows in a single write.
     *
     * @param  array<int, array<string, mixed>>  $expertises
     */
    public function syncExpertises(Model $doctor, array $expertises): void;

    /**
     * Replace the doctor's weekly timetable from the form repeater.
     *
     * @param  array<int, array<string, mixed>>  $schedules
     */
    public function syncSchedules(Doctor $doctor, array $schedules): void;

    public function toggleFeatured(int|string $id): bool;
}
