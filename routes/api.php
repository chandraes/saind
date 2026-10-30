<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DatabaseController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Middleware\EnsureMobileToken;
use App\Http\Middleware\EnsureMobileWorkspace;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('workspace', [WorkspaceController::class, 'show'])->name('workspace');
    Route::middleware(EnsureMobileWorkspace::class)->group(function (): void {
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:mobile-login')->name('login');
        Route::middleware(['auth:sanctum', EnsureMobileToken::class])->group(function (): void {
            Route::get('me', [AuthController::class, 'show'])->name('me');
            Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
            Route::prefix('database')->name('database.')->middleware('role:'.implode(',', config('mobile.database_roles')))->group(function (): void {
                Route::get('vendors', [DatabaseController::class, 'vendors'])->name('vendors.index');
                Route::get('vendors/{vendor}', [DatabaseController::class, 'vendor'])->whereNumber('vendor')->name('vendors.show');
                Route::get('vehicles', [DatabaseController::class, 'vehicles'])->name('vehicles.index');
                Route::get('vehicles/{vehicle}', [DatabaseController::class, 'vehicle'])->whereNumber('vehicle')->name('vehicles.show');
            });
            Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
            Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->whereNumber('vehicle')->name('vehicles.show');
        });
    });
});
