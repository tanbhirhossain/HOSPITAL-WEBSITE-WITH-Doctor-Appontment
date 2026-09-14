<?php

namespace Modules\DOCTOR\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DOCTOR\Models\Symptom;

/**
 * @extends Factory<Symptom>
 */
class SymptomFactory extends Factory
{
    protected $model = Symptom::class;

    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(2, true),
            'icon' => null,
            'link_url' => '/find-doctor',
            'sort_order' => fake()->numberBetween(0, 20),
            'is_active' => fake()->boolean(90),
        ];
    }
}
