<?php

namespace Modules\Dashboard\App\Providers;

use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'dashboard');
    }

    public function register(): void
    {
        //
    }
}
