<?php

namespace Modules\DOCTOR\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\DOCTOR\Models\Department;
use Modules\DOCTOR\Models\DepartmentCategory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(2, true).' Care';

        return [
            'department_category_id' => DepartmentCategory::factory(),
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(14),
            'icon' => null,
            'is_popular_search' => fake()->boolean(30),
            'is_featured' => fake()->boolean(25),
            'featured_image' => null,
            'sort_order' => fake()->numberBetween(0, 30),
            'is_active' => fake()->boolean(90),
        ];
    }
}
