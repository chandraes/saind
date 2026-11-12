<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DatabaseController;
use App\Http\Controllers\Api\LoadNoteDraftController;
use App\Http\Controllers\Api\MobileBatteryController;
use App\Http\Controllers\Api\MobileFilterOilController;
use App\Http\Controllers\Api\MobileMaintenanceHistoryController;
use App\Http\Controllers\Api\MobileMaintenanceVehicleController;
use App\Http\Controllers\Api\MobileTireHistoryController;
use App\Http\Controllers\Api\MobileTireMonitoringController;
use App\Http\Controllers\Api\MobileUnitPerformanceController;
use App\Http\Controllers\Api\MobileUpahGendongController;
use App\Http\Controllers\Api\MobileUpahGendongSettingController;
use App\Http\Controllers\Api\MobileVendorCashController;
use App\Http\Controllers\Api\PaymentNoteController;
use App\Http\Controllers\Api\TrustController;
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
    Route::get('trust', [TrustController::class, 'show'])->middleware('throttle:60,1')->name('trust');
    Route::middleware(EnsureMobileWorkspace::class)->group(function (): void {
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:mobile-login')->name('login');
        Route::middleware(['auth:sanctum', EnsureMobileToken::class])->group(function (): void {
            Route::get('me', [AuthController::class, 'show'])->name('me');
            Route::patch('me', [AuthController::class, 'update'])->middleware('throttle:6,1')->name('me.update');
            Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
            Route::middleware(EnsureMobileVendorContext::class.':billing')->group(function (): void {
                Route::get('vehicles/{vehicle}/tire-positions/{position}/history', [MobileTireHistoryController::class, 'history'])->whereNumber(['vehicle', 'position'])->name('tire-history.index');
                Route::get('vehicles/{vehicle}/tire-logs/{banLog}/trips', [MobileTireHistoryController::class, 'trips'])->whereNumber(['vehicle', 'banLog'])->name('tire-trips.index');
                Route::get('vehicles/{vehicle}/battery-monitoring', [MobileBatteryController::class, 'show'])->whereNumber('vehicle')->name('battery-monitoring.show');
                Route::get('vehicles/{vehicle}/battery-positions/{position}/history', [MobileBatteryController::class, 'history'])->whereNumber(['vehicle', 'position'])->name('battery-history.index');
                Route::get('maintenance/vehicles', MobileMaintenanceVehicleController::class)->name('maintenance.vehicles');
                Route::get('filter-oil/vehicles', [MobileFilterOilController::class, 'vehicles'])->name('filter-oil.vehicles');
                Route::get('vehicles/{vehicle}/filter-oil-monitoring', [MobileFilterOilController::class, 'show'])->whereNumber('vehicle')->name('filter-oil-monitoring.show');
                Route::get('vehicles/{vehicle}/filter-oil-categories/{category}/history', [MobileFilterOilController::class, 'history'])->whereNumber(['vehicle', 'category'])->name('filter-oil-history.index');
                Route::get('vehicles/{vehicle}/filter-oil-logs/{log}/trips', [MobileFilterOilController::class, 'trips'])->whereNumber(['vehicle', 'log'])->name('filter-oil-trips.index');
                Route::get('vehicles/{vehicle}/tire-monitoring', [MobileTireMonitoringController::class, 'show'])->whereNumber('vehicle')->name('tire-monitoring.show');
                Route::get('unit-performance', [MobileUnitPerformanceController::class, 'index'])->name('unit-performance.index');
                Route::get('unit-performance/all-vendor', [MobileUnitPerformanceController::class, 'allVendor'])->name('unit-performance.all-vendor');
                Route::get('upah-gendong/vehicles', [MobileUpahGendongController::class, 'vehicles'])->name('upah-gendong.vehicles');
                Route::get('vehicles/{vehicle}/upah-gendong', [MobileUpahGendongController::class, 'show'])->whereNumber('vehicle')->name('upah-gendong.show');
                Route::get('vendor-cash', [MobileVendorCashController::class, 'index'])->name('vendor-cash.index');
                Route::get('upah-gendong-settings', [MobileUpahGendongSettingController::class, 'index'])->name('upah-gendong-settings.index');
                Route::post('vehicles/{vehicle}/upah-gendong-setting', [MobileUpahGendongSettingController::class, 'store'])->whereNumber('vehicle')->middleware('throttle:30,1')->name('upah-gendong-settings.store');
                Route::patch('vehicles/{vehicle}/upah-gendong-setting', [MobileUpahGendongSettingController::class, 'update'])->whereNumber('vehicle')->middleware('throttle:30,1')->name('upah-gendong-settings.update');
                Route::delete('vehicles/{vehicle}/upah-gendong-setting', [MobileUpahGendongSettingController::class, 'destroy'])->whereNumber('vehicle')->middleware('throttle:10,1')->name('upah-gendong-settings.destroy');
                Route::middleware('throttle:10,1')->group(function (): void {
                    Route::patch('vehicles/{vehicle}/tire-logs/{banLog}', [MobileMaintenanceHistoryController::class, 'updateTire'])->whereNumber(['vehicle', 'banLog'])->name('tire-logs.update');
                    Route::delete('vehicles/{vehicle}/tire-logs/{banLog}', [MobileMaintenanceHistoryController::class, 'destroyTire'])->whereNumber(['vehicle', 'banLog'])->name('tire-logs.destroy');
                    Route::patch('vehicles/{vehicle}/battery-logs/{akiLog}', [MobileMaintenanceHistoryController::class, 'updateBattery'])->whereNumber(['vehicle', 'akiLog'])->name('battery-logs.update');
                    Route::delete('vehicles/{vehicle}/battery-logs/{akiLog}', [MobileMaintenanceHistoryController::class, 'destroyBattery'])->whereNumber(['vehicle', 'akiLog'])->name('battery-logs.destroy');
                    Route::patch('vehicles/{vehicle}/filter-oil-logs/{log}', [MobileMaintenanceHistoryController::class, 'updateFilterOil'])->whereNumber(['vehicle', 'log'])->name('filter-oil-logs.update');
                    Route::delete('vehicles/{vehicle}/filter-oil-logs/{log}', [MobileMaintenanceHistoryController::class, 'destroyFilterOil'])->whereNumber(['vehicle', 'log'])->name('filter-oil-logs.destroy');
                });
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('vehicles/{vehicle}/tire-logs', [MobileMaintenanceHistoryController::class, 'storeTire'])->whereNumber('vehicle')->name('tire-logs.store');
                    Route::post('vehicles/{vehicle}/battery-logs', [MobileMaintenanceHistoryController::class, 'storeBattery'])->whereNumber('vehicle')->name('battery-logs.store');
                    Route::post('vehicles/{vehicle}/filter-oil-logs', [MobileMaintenanceHistoryController::class, 'storeFilterOil'])->whereNumber('vehicle')->name('filter-oil-logs.store');
                });
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
