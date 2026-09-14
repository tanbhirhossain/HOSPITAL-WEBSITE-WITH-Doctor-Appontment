<?php

namespace Modules\DOCTOR\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DOCTOR\Models\DepartmentCategory;

class DepartmentCategorySeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, sort_order: int}>
     */
    public const CATEGORIES = [
        ['name' => 'Medicine', 'sort_order' => 1],
        ['name' => 'Surgery', 'sort_order' => 2],
        ['name' => 'Mother & Child', 'sort_order' => 3],
        ['name' => 'Diagnostics', 'sort_order' => 4],
        ['name' => 'Dental & ENT', 'sort_order' => 5],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            DepartmentCategory::query()->updateOrCreate(
                ['slug' => str($category['name'])->slug()],
                [...$category, 'is_active' => true],
            );
        }
    }
}
