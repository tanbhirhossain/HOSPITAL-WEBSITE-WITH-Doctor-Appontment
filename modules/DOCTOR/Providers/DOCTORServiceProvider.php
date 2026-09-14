<?php

namespace Modules\DOCTOR\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\DOCTOR\Interfaces\DepartmentCategoryRepositoryInterface;
use Modules\DOCTOR\Interfaces\DepartmentRepositoryInterface;
use Modules\DOCTOR\Interfaces\DoctorExpertiesRepositoryInterface;
use Modules\DOCTOR\Interfaces\DoctorRepositoryInterface;
use Modules\DOCTOR\Interfaces\DoctorScheduleRepositoryInterface;
use Modules\DOCTOR\Interfaces\PageSectionRepositoryInterface;
use Modules\DOCTOR\Interfaces\SymptomRepositoryInterface;
use Modules\DOCTOR\Repositories\DepartmentCategoryRepository;
use Modules\DOCTOR\Repositories\DepartmentRepository;
use Modules\DOCTOR\Repositories\DoctorExpertiseRepository;
use Modules\DOCTOR\Repositories\DoctorRepository;
use Modules\DOCTOR\Repositories\DoctorScheduleRepository;
use Modules\DOCTOR\Repositories\PageSectionRepository;
use Modules\DOCTOR\Repositories\SymptomRepository;

class DOCTORServiceProvider extends ServiceProvider
{
    /**
     * Every repository is resolved through its interface, so a controller or
     * service never depends on Eloquent directly.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        DepartmentCategoryRepositoryInterface::class => DepartmentCategoryRepository::class,
        DepartmentRepositoryInterface::class => DepartmentRepository::class,
        DoctorRepositoryInterface::class => DoctorRepository::class,
        DoctorExpertiesRepositoryInterface::class => DoctorExpertiseRepository::class,
        DoctorScheduleRepositoryInterface::class => DoctorScheduleRepository::class,
        SymptomRepositoryInterface::class => SymptomRepository::class,
        PageSectionRepositoryInterface::class => PageSectionRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $interface => $concrete) {
            $this->app->singleton($interface, $concrete);
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        if (file_exists(__DIR__.'/../Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
