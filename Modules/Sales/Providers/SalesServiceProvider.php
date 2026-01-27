<?php

namespace Modules\Sales\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SalesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    protected function registerRoutes(): void
    {
        Route::prefix('api/sales')
            ->middleware('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
