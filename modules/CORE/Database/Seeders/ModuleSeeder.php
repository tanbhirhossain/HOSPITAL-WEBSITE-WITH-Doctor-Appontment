<?php

namespace Modules\CORE\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DOCTOR\Database\Seeders\DepartmentCategorySeeder;
use Modules\DOCTOR\Database\Seeders\DepartmentSeeder;
use Modules\DOCTOR\Database\Seeders\DoctorExpertiseSeeder;
use Modules\DOCTOR\Database\Seeders\DoctorScheduleSeeder;
use Modules\DOCTOR\Database\Seeders\DoctorSeeder;
use Modules\DOCTOR\Database\Seeders\PageSectionSeeder;
use Modules\DOCTOR\Database\Seeders\SymptomSeeder;

/**
 * Single entry point for every module seeder.
 *
 * `database/seeders/DatabaseSeeder.php` is left exactly as the framework
 * ships it, so seeding is started explicitly:
 *
 *      php artisan db:seed --class="Modules\CORE\Database\Seeders\ModuleSeeder"
 *
 * Order matters: access control first (so audited writes have a user), then
 * the department hierarchy, then doctors and their related rows.
 */
class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AccessControlSeeder::class,

            DepartmentCategorySeeder::class,
            DepartmentSeeder::class,
            DoctorSeeder::class,
            DoctorExpertiseSeeder::class,
            DoctorScheduleSeeder::class,
            SymptomSeeder::class,
            PageSectionSeeder::class,

            SeoMetaDataSeeder::class,
        ]);
    }
}
