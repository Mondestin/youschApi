<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserRoleController;

class RoleRouteService
{
    /**
     * Register all role management routes.
     */
    public static function registerRoutes(): void
    {
        self::registerRoleRoutes();
        self::registerUserRoleRoutes();
    }

    /**
     * Register role management routes.
     */
    private static function registerRoleRoutes(): void
    {
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('/active', [RoleController::class, 'active'])->name('active');
            Route::get('/statistics', [RoleController::class, 'statistics'])->name('statistics');
            Route::get('/search', [RoleController::class, 'search'])->name('search');
            Route::get('/{id}', [RoleController::class, 'show'])->name('show');
            Route::put('/{id}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
        });
    }

    /**
     * Register user-role assignment routes.
     */
    private static function registerUserRoleRoutes(): void
    {
        Route::prefix('user-roles')->name('user-roles.')->group(function () {
            Route::post('/assign', [UserRoleController::class, 'assignRole'])->name('assign');
            Route::post('/remove', [UserRoleController::class, 'removeRole'])->name('remove');
            Route::post('/sync', [UserRoleController::class, 'syncRoles'])->name('sync');
            Route::get('/user/{userId}', [UserRoleController::class, 'getUserRoles'])->name('user-roles');
            Route::post('/has-role', [UserRoleController::class, 'hasRole'])->name('has-role');
            Route::post('/has-any-role', [UserRoleController::class, 'hasAnyRole'])->name('has-any-role');
        });
    }
}