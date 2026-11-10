<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DatabaseController;
use App\Http\Controllers\Api\LoadNoteDraftController;
use App\Http\Controllers\Api\MobileTireMonitoringController;
use App\Http\Controllers\Api\MobileUnitPerformanceController;
use App\Http\Controllers\Api\PaymentNoteController;
use App\Http\Controllers\Api\UnloadingNoteController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VendorInvoiceController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Middleware\EnsureMobileToken;
use App\Http\Middleware\EnsureMobileVendorContext;
use App\Http\Middleware\EnsureMobileWorkspace;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('workspace', [WorkspaceController::class, 'show'])->name('workspace');
    Route::middleware(EnsureMobileWorkspace::class)->group(function (): void {
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:mobile-login')->name('login');
        Route::middleware(['auth:sanctum', EnsureMobileToken::class])->group(function (): void {
            Route::get('me', [AuthController::class, 'show'])->name('me');
            Route::patch('me', [AuthController::class, 'update'])->middleware('throttle:6,1')->name('me.update');
            Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
            Route::middleware(EnsureMobileVendorContext::class.':billing')->group(function (): void {
                Route::get('vehicles/{vehicle}/tire-monitoring', [MobileTireMonitoringController::class, 'show'])->whereNumber('vehicle')->name('tire-monitoring.show');
                Route::get('unit-performance', [MobileUnitPerformanceController::class, 'index'])->name('unit-performance.index');
                Route::get('vendor-invoices', [VendorInvoiceController::class, 'index'])->name('vendor-invoices.index');
                Route::get('vendor-invoices/{kind}/{record}', [VendorInvoiceController::class, 'show'])->whereNumber('record')->name('vendor-invoices.show');
                Route::get('payment-notes', [PaymentNoteController::class, 'index'])->name('payment-notes.index');
                Route::get('payment-notes/{kind}/{record}', [PaymentNoteController::class, 'show'])->whereNumber('record')->name('payment-notes.show');
            });
            Route::middleware(EnsureMobileVendorContext::class)->group(function (): void {
                Route::post('load-notes/scanned', [LoadNoteDraftController::class, 'storeScanned'])->middleware('throttle:30,1')->name('load-notes.scanned');
                Route::post('load-notes/inspect', [LoadNoteDraftController::class, 'inspect'])->middleware('throttle:30,1')->name('load-notes.inspect');
                Route::get('load-notes/vehicles', [LoadNoteDraftController::class, 'vehicles'])->name('load-notes.vehicles');
                Route::get('unloading-notes', [UnloadingNoteController::class, 'index'])->name('unloading-notes.index');
                Route::get('unloading-notes/{transaction}', [UnloadingNoteController::class, 'show'])->whereNumber('transaction')->name('unloading-notes.show');
                Route::get('load-notes', [LoadNoteDraftController::class, 'index'])->name('load-notes.index');
                Route::post('load-notes', [LoadNoteDraftController::class, 'store'])->middleware('throttle:30,1')->name('load-notes.store');
                Route::get('load-notes/{note}', [LoadNoteDraftController::class, 'show'])->whereNumber('note')->name('load-notes.show');
                Route::get('load-notes/{note}/photo', [LoadNoteDraftController::class, 'photo'])->whereNumber('note')->name('load-notes.photo');
            });
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
