<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Services\ExamsGradingsRouteService;

class ExamsGradingsRouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware(['api'])
                ->prefix('api/exams-gradings')
                ->name('exams-gradings.')
                ->group(function () {
                    ExamsGradingsRouteService::registerRoutes();
                });
        });
    }
} 