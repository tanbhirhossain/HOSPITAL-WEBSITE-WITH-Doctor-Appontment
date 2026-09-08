<?php

namespace Modules\DOCTOR\Database\Factories;

use Modules\DOCTOR\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            //
        ];
    }
}