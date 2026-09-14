<?php

namespace Modules\DOCTOR\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorSchedule;

/**
 * @extends Factory<DoctorSchedule>
 */
class DoctorScheduleFactory extends Factory
{
    protected $model = DoctorSchedule::class;

    public function definition(): array
    {
        $start = fake()->numberBetween(8, 18);

        return [
            'doctor_id' => Doctor::factory(),
            'day_of_week' => fake()->randomElement(DoctorSchedule::DAYS),
            'start_time' => sprintf('%02d:00', $start),
            'end_time' => sprintf('%02d:00', min($start + fake()->numberBetween(2, 5), 22)),
            'consultation_type' => fake()->randomElement(DoctorSchedule::CONSULTATION_TYPES),
            'availability_status' => fake()->randomElement(DoctorSchedule::AVAILABILITY_STATUSES),
            'max_patients' => fake()->numberBetween(10, 40),
            'is_active' => fake()->boolean(90),
        ];
    }

    /**
     * Produce a rota that cannot collide: one slot per weekday.
     */
    public function weeklyRota(Doctor $doctor, string $start = '09:00', string $end = '17:00'): static
    {
        return $this->state(fn (): array => [
            'doctor_id' => $doctor->id,
            'start_time' => $start,
            'end_time' => $end,
        ]);
    }
}
