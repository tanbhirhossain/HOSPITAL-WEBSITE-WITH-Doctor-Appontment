<?php

namespace Modules\DOCTOR\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\DOCTOR\Models\DepartmentCategory;

/**
 * @extends Factory<DepartmentCategory>
 */
class DepartmentCategoryFactory extends Factory
{
    protected $model = DepartmentCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'sort_order' => fake()->numberBetween(0, 20),
            'is_active' => fake()->boolean(85),
        ];
    }
}
