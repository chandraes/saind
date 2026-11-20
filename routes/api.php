<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DatabaseController;
use App\Http\Controllers\Api\LoadNoteDraftController;
use App\Http\Controllers\Api\MobileAttendanceDeductionController;
use App\Http\Controllers\Api\MobileBatteryController;
use App\Http\Controllers\Api\MobileBillingInvoiceController;
use App\Http\Controllers\Api\MobileCustomerBillController;
use App\Http\Controllers\Api\MobileCustomerBillingController;
use App\Http\Controllers\Api\MobileDriverHeldCashController;
use App\Http\Controllers\Api\MobileFilterOilController;
use App\Http\Controllers\Api\MobileHeldCashActionController;
use App\Http\Controllers\Api\MobileMaintenanceAuthorizationController;
use App\Http\Controllers\Api\MobileMaintenanceHistoryController;
use App\Http\Controllers\Api\MobileMaintenanceVehicleController;
use App\Http\Controllers\Api\MobilePayrollController;
use App\Http\Controllers\Api\MobileReminderController;
use App\Http\Controllers\Api\MobileTireHistoryController;
use App\Http\Controllers\Api\MobileTireMonitoringController;
use App\Http\Controllers\Api\MobileTonnageReportController;
use App\Http\Controllers\Api\MobileUnitPerformanceController;
use App\Http\Controllers\Api\MobileUpahGendongController;
use App\Http\Controllers\Api\MobileUpahGendongSettingController;
use App\Http\Controllers\Api\MobileVendorBillingActionController;
use App\Http\Controllers\Api\MobileVendorCashController;
use App\Http\Controllers\Api\MobileVendorOptionController;
use App\Http\Controllers\Api\MobileVendorPaymentCartController;
use App\Http\Controllers\Api\PaymentNoteController;
use App\Http\Controllers\Api\TrustController;
use App\Http\Controllers\Api\UnloadingNoteController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VendorInvoiceController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\FormKasUangJalanController;
use App\Http\Controllers\LoadNoteInboxController;
use App\Http\Middleware\EnsureMobileToken;
use App\Http\Middleware\EnsureMobileVendorContext;
use App\Http\Middleware\EnsureMobileWorkspace;
use App\Services\MaintenanceApproval;
use App\Support\ReminderRegistry;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('workspace', [WorkspaceController::class, 'show'])->name('workspace');
    Route::get('trust', [TrustController::class, 'show'])->middleware('throttle:60,1')->name('trust');
    Route::middleware(EnsureMobileWorkspace::class)->group(function (): void {
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:mobile-login')->name('login');
        Route::middleware(['auth:sanctum', EnsureMobileToken::class])->group(function (): void {
            Route::prefix('vendors/{vendor}/payment-cart')->whereNumber('vendor')
                ->middleware(['role:admin,su', 'throttle:30,1'])
                ->controller(MobileVendorPaymentCartController::class)->group(function (): void {
                    Route::get('/', 'show')->name('payment-cart.show');
                    Route::post('/', 'store')->name('payment-cart.store');
                    Route::delete('/', 'destroy')->name('payment-cart.destroy');
                    Route::post('checkout', 'checkout')->name('payment-cart.checkout');
                    Route::get('additional/{kind}', 'showAdditional')->name('payment-cart.additional.show');
                    Route::post('additional/{kind}', 'storeAdditional')->name('payment-cart.additional.store');
                    Route::delete('additional/{kind}/{invoice}', 'returnAdditional')->whereNumber('invoice')->name('payment-cart.additional.return');
                    Route::post('additional/{kind}/{invoice}/checkout', 'checkoutAdditional')->whereNumber('invoice')->name('payment-cart.additional.checkout');
                });
            Route::get('me', [AuthController::class, 'show'])->name('me');
            Route::patch('me', [AuthController::class, 'update'])->middleware('throttle:6,1')->name('me.update');
            Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
            Route::middleware(EnsureMobileVendorContext::class.':billing')->group(function (): void {
                Route::get('vehicles/{vehicle}/tire-positions/{position}/history', [MobileTireHistoryController::class, 'history'])->whereNumber(['vehicle', 'position'])->name('tire-history.index');
                Route::get('vehicles/{vehicle}/tire-logs/{banLog}/trips', [MobileTireHistoryController::class, 'trips'])->whereNumber(['vehicle', 'banLog'])->name('tire-trips.index');
                Route::get('vehicles/{vehicle}/battery-monitoring', [MobileBatteryController::class, 'show'])->whereNumber('vehicle')->name('battery-monitoring.show');
                Route::get('vehicles/{vehicle}/battery-positions/{position}/history', [MobileBatteryController::class, 'history'])->whereNumber(['vehicle', 'position'])->name('battery-history.index');
                Route::get('vendor-options', MobileVendorOptionController::class)->name('vendor-options');
                Route::get('maintenance/vehicles', MobileMaintenanceVehicleController::class)->name('maintenance.vehicles');
                Route::get('held-cash/vehicles', [MobileDriverHeldCashController::class, 'vehicles'])->name('held-cash.vehicles');
                Route::get('vehicles/{vehicle}/held-cash/{fund}', [MobileDriverHeldCashController::class, 'vehicle'])->whereNumber('vehicle')->whereIn('fund', ['sparepart', 'ban'])->name('vehicle-held-cash.index');
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
                Route::get('driver/held-cash/{fund}', MobileDriverHeldCashController::class)->whereIn('fund', ['sparepart', 'ban'])->name('driver-held-cash.index');
                Route::get('unloading-notes', [UnloadingNoteController::class, 'index'])->name('unloading-notes.index');
                Route::get('unloading-notes/{transaction}', [UnloadingNoteController::class, 'show'])->whereNumber('transaction')->name('unloading-notes.show');
                Route::get('load-notes', [LoadNoteDraftController::class, 'index'])->name('load-notes.index');
                // Kirim nota muat manual (isian + foto) DITUTUP: dari mobile nota hanya boleh lewat scan
                // (load-notes/inspect -> load-notes/scanned). Disimpan sebagai komentar bila kelak ada
                // pengembangan input manual; aktifkan lagi beserta tesnya (lihat MobileManualLoadNoteClosedTest).
                // Route::post('load-notes', [LoadNoteDraftController::class, 'store'])->middleware('throttle:30,1')->name('load-notes.store');
                Route::get('load-notes/{note}', [LoadNoteDraftController::class, 'show'])->whereNumber('note')->name('load-notes.show');
                Route::get('load-notes/{note}/photo', [LoadNoteDraftController::class, 'photo'])->whereNumber('note')->name('load-notes.photo');
            });
            // Admin & SU: peninjauan Nota Muat sama dengan web (koreksi, nominal, void, proses ke Uang Jalan).
            // Controller, form request, policy dan service yang sama dengan billing.nota-muat.
            Route::prefix('load-notes/{note}')->whereNumber('note')->name('load-notes.')->middleware('role:admin,su')->group(function (): void {
                Route::get('review', [LoadNoteInboxController::class, 'review'])->name('review');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::patch('/', [LoadNoteInboxController::class, 'update'])->name('update');
                    Route::patch('payment', [LoadNoteInboxController::class, 'updatePayment'])->name('payment.update');
                    Route::post('decision', [LoadNoteInboxController::class, 'decide'])->name('decide');
                    Route::post('process', [FormKasUangJalanController::class, 'processNote'])->name('process');
                });
            });
            // Admin & SU: Otorisasi Maintenance (ban luar, aki, filter & oli mesin), sama dengan web.
            Route::prefix('maintenance-authorizations/{type}')->whereIn('type', MaintenanceApproval::TYPES)->name('maintenance-authorizations.')
                ->middleware('role:admin,su')->controller(MobileMaintenanceAuthorizationController::class)->group(function (): void {
                    Route::get('/', 'index')->name('index');
                    Route::get('{id}', 'show')->whereNumber('id')->name('show');
                    Route::middleware('throttle:30,1')->group(function (): void {
                        Route::post('{id}/approve', 'approve')->whereNumber('id')->name('approve');
                        Route::post('{id}/reject', 'reject')->whereNumber('id')->name('reject');
                    });
                });
            // Admin & SU: isi Nota Bongkar, lunasi & batalkan Invoice Vendor (sama dengan web).
            Route::middleware(['role:admin,su', 'throttle:30,1'])->controller(MobileVendorBillingActionController::class)->group(function (): void {
                Route::post('unloading-notes/{transaction}/complete', 'completeUnloading')->whereNumber('transaction')->name('unloading-notes.complete');
                Route::post('vendor-invoices/{kind}/{record}/settle', 'settleInvoice')->whereNumber('record')->name('vendor-invoices.settle');
                Route::post('vendor-invoices/utama/{record}/cancel', 'cancelInvoice')->whereNumber('record')->name('vendor-invoices.cancel');
            });
            // Admin & SU: Nota Tagihan → Invoice Tagihan (centang nota fisik, ubah nota, keranjang, buat invoice).
            Route::prefix('customers/{customer}')->whereNumber('customer')->middleware(['role:admin,su', 'throttle:60,1'])
                ->controller(MobileCustomerBillingController::class)->name('customer-billing.')->group(function (): void {
                    Route::post('bills/{transaction}/physical-note', 'physicalNote')->whereNumber('transaction')->name('physical-note');
                    Route::patch('bills/{transaction}', 'update')->whereNumber('transaction')->name('update');
                    Route::get('bill-cart', 'cart')->name('cart');
                    Route::post('bill-cart', 'add')->name('cart.add');
                    Route::delete('bill-cart/{transaction}', 'remove')->whereNumber('transaction')->name('cart.remove');
                    Route::post('bill-cart/checkout', 'checkout')->name('cart.checkout');
                });
            // Admin & SU: cutoff UJ Ditahan per periode dan batalkan pencairan manual (sama dengan web).
            Route::middleware(['role:admin,su', 'throttle:30,1'])->controller(MobileHeldCashActionController::class)->group(function (): void {
                Route::post('vehicles/{vehicle}/held-cash/{fund}/cutoff', 'cutoff')->whereNumber('vehicle')->whereIn('fund', ['sparepart', 'ban'])->name('held-cash.cutoff');
                Route::post('vehicles/{vehicle}/held-cash/{fund}/withdraw', 'withdraw')->whereNumber('vehicle')->whereIn('fund', ['sparepart', 'ban'])->name('held-cash.withdraw');
                Route::post('held-cash/{fund}/details/{detail}/cancel', 'cancel')->whereIn('fund', ['sparepart', 'ban'])->whereNumber('detail')->name('held-cash.cancel');
            });
            // Eksekutif (GM & Direksi): Nota Tagihan per customer & Tagihan & Invoice (baca saja).
            Route::middleware('role:executive')->group(function (): void {
                Route::get('customer-options', [MobileCustomerBillController::class, 'customers'])->name('customer-options');
                Route::get('billing-invoices', MobileBillingInvoiceController::class)->name('billing-invoices.index');
                Route::post('billing-invoices/{invoice}/pay', [MobileBillingInvoiceController::class, 'pay'])->whereNumber('invoice')
                    ->middleware(['role:admin,su', 'throttle:30,1'])->name('billing-invoices.pay');
                Route::get('billing-invoices/{invoice}/transactions', [MobileBillingInvoiceController::class, 'transactions'])->whereNumber('invoice')->name('billing-invoices.transactions');
                Route::get('customers/{customer}/bills', [MobileCustomerBillController::class, 'index'])->whereNumber('customer')->name('customer-bills.index');
            });
            Route::get('attendance-deductions', [MobileAttendanceDeductionController::class, 'index'])->middleware('role:hrd')->name('attendance-deductions.index');
            // Rincian potongan absensi: HRD dan eksekutif (dari Gaji Karyawan).
            Route::get('attendance-deductions/{key}', [MobileAttendanceDeductionController::class, 'show'])->whereNumber('key')->middleware('role:hrd,executive')->name('attendance-deductions.show');
            Route::get('payroll', [MobilePayrollController::class, 'index'])->middleware('role:executive')->name('payroll.index');
            // Admin & SU: simpan gaji periode dan perbarui absensinya (sama dengan Form Gaji web).
            Route::middleware(['role:admin,su', 'throttle:10,1'])->group(function (): void {
                Route::post('payroll/refresh-attendance', [MobilePayrollController::class, 'refresh'])->name('payroll.refresh-attendance');
                Route::post('payroll', [MobilePayrollController::class, 'store'])->name('payroll.store');
            });
            // Dashboard Pengingat: akses per tabel & cakupan vendor diatur ReminderRegistry (sama dengan web).
            Route::prefix('tonnage-report/customers')->name('tonnage-report.')->middleware('role:admin,su,operasional')
                ->controller(MobileTonnageReportController::class)->group(function (): void {
                    Route::get('/', 'customers')->name('customers');
                    Route::get('{customer}', 'show')->whereNumber('customer')->name('show');
                });

            Route::get('reminders', [MobileReminderController::class, 'index'])->name('reminders.index');
            Route::post('reminders/settings', [MobileReminderController::class, 'settings'])->middleware('throttle:30,1')->name('reminders.settings');
            Route::get('reminders/{type}', [MobileReminderController::class, 'show'])->whereIn('type', ReminderRegistry::keys())->name('reminders.show');
            Route::prefix('database')->name('database.')->middleware('role:'.implode(',', config('mobile.database_roles')))->group(function (): void {
                Route::get('vendors', [DatabaseController::class, 'vendors'])->name('vendors.index');
                Route::get('vendors/{vendor}', [DatabaseController::class, 'vendor'])->whereNumber('vendor')->name('vendors.show');
                Route::get('vehicles', [DatabaseController::class, 'vehicles'])->name('vehicles.index');
                Route::get('vehicles/{vehicle}', [DatabaseController::class, 'vehicle'])->whereNumber('vehicle')->name('vehicles.show');
                Route::get('drivers', [DatabaseController::class, 'drivers'])->name('drivers.index');
                Route::get('drivers/{driver}', [DatabaseController::class, 'driver'])->whereNumber('driver')->name('drivers.show');
                Route::get('drivers/{driver}/sim-photo', [DatabaseController::class, 'driverSimPhoto'])->whereNumber('driver')->name('drivers.sim-photo');
                Route::get('employees', [DatabaseController::class, 'employees'])->name('employees.index');
                Route::get('employees/{employee}', [DatabaseController::class, 'employee'])->whereNumber('employee')->name('employees.show');
                Route::get('employees/{employee}/photos/{document}', [DatabaseController::class, 'employeePhoto'])
                    ->whereNumber('employee')->whereIn('document', DatabaseController::EMPLOYEE_PHOTOS)->name('employees.photo');
            });
            Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
            Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->whereNumber('vehicle')->name('vehicles.show');
        });
    });
});
