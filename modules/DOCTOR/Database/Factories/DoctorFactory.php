<?php

namespace Modules\DOCTOR\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\DOCTOR\Models\Department;
use Modules\DOCTOR\Models\Doctor;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        $name = 'Dr. '.fake()->firstName().' '.fake()->lastName();
        $specialties = ['Cardiology', 'Neurology', 'Paediatrics', 'Orthopaedics', 'Dermatology', 'Gynaecology'];

        return [
            'department_id' => Department::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'designation' => fake()->randomElement(['Consultant', 'Senior Consultant', 'Associate Professor', 'Specialist']),
            'specialty' => fake()->randomElement($specialties),
            'qualification' => fake()->randomElement(['MBBS, MD', 'MBBS, FCPS', 'MBBS, FRCP', 'MBBS, MS']),
            'experience' => fake()->numberBetween(5, 30).' years',
            'experience_years' => fake()->numberBetween(5, 30),
            'hospital_name' => 'AMZ Hospital Ltd.',
            'location' => 'AMZ Hospital, Dhaka',
            'profile_photo' => null,
            'bio' => fake()->paragraph(3),
            'rating' => fake()->randomFloat(2, 3.8, 5),
            'reviews_count' => fake()->numberBetween(0, 480),
            'social_links' => null,
            'is_featured' => fake()->boolean(20),
            'is_active' => fake()->boolean(90),
        ];
    }
}
