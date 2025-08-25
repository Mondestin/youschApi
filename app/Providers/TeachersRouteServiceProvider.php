<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Services\TeachersRouteService;

class TeachersRouteServiceProvider extends RouteServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware(['api', 'teachers.api.rate.limit'])
                ->prefix('api/teachers')
                ->name('teachers.')
                ->group(function () {
                    TeachersRouteService::registerRoutes();
                });
        });
    }

    /**
     * Configure the rate limiters for the teachers API.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('teachers.api.rate.limit', function ($request) {
            return Limit::perMinute(config('teachers.rate_limit.per_minute', 60))
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests. Please try again later.',
                    ], 429);
                });
        });
    }
} 