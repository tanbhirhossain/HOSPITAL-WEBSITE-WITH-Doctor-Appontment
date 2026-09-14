<?php

namespace Modules\DOCTOR\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorExpertise;

/**
 * @extends Factory<DoctorExpertise>
 */
class DoctorExpertiseFactory extends Factory
{
    protected $model = DoctorExpertise::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(10),
            'icon' => null,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
