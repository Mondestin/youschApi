<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Services\TeachersRouteService;

class TeachersRouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware(['api'])
                ->prefix('api/teachers')
                ->name('teachers.')
                ->group(function () {
                    TeachersRouteService::registerRoutes();
                });
        });
    }
} 