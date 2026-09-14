<?php

namespace Modules\DOCTOR\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorSchedule;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $rotas = [
            ['days' => ['Sat', 'Sun', 'Mon', 'Tue', 'Wed'], 'start' => '09:00', 'end' => '14:00', 'type' => 'in_person'],
            ['days' => ['Thu', 'Fri'], 'start' => '16:00', 'end' => '21:00', 'type' => 'both'],
            ['days' => ['Sat', 'Mon', 'Wed'], 'start' => '18:00', 'end' => '22:00', 'type' => 'online'],
        ];

        foreach (Doctor::query()->get() as $doctor) {
            foreach ($rotas as $rota) {
                // Give every doctor a slightly different pattern.
                $days = fake()->boolean(70) ? $rota['days'] : [$rota['days'][0]];

                foreach ($days as $day) {
                    DoctorSchedule::query()->updateOrCreate(
                        [
                            'doctor_id' => $doctor->id,
                            'day_of_week' => $day,
                            'start_time' => $rota['start'],
                        ],
                        [
                            'end_time' => $rota['end'],
                            'consultation_type' => $rota['type'],
                            'availability_status' => fake()->randomElement(['available', 'available', 'limited']),
                            'max_patients' => fake()->numberBetween(15, 35),
                            'is_active' => true,
                        ],
                    );
                }
            }
        }
    }
}
