<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Services\StudentsRouteService;

class StudentsRouteServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
      
          $this->routes(function () {
            Route::middleware(['api'])
                ->prefix('api/admin/students')
                ->name('admin.students.')
                ->group(function () {
                    StudentsRouteService::registerRoutes();
                });
        });
    }

   

  
} 