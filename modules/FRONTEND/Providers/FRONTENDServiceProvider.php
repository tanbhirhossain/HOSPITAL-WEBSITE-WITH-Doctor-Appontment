<?php

namespace Modules\FRONTEND\Providers;

use Illuminate\Support\ServiceProvider;

class FRONTENDServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'frontend');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        if (file_exists(__DIR__.'/../Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}