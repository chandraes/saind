<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route("login");
});

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);

Route::group(['middleware' => ['auth']], function() {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/dokumen', [App\Http\Controllers\DokumenController::class, 'index'])->name('dokumen');
    Route::view('/dokumen/sph', 'dokumen.template.index')->name('dokumen.template');

    Route::get('/database', [App\Http\Controllers\DatabaseController::class, 'index'])->name('database');
    Route::resource('vendor', App\Http\Controllers\VendorController::class)->middleware('role:admin,user');
    Route::get('/vendor/{id}/pembayaran', [App\Http\Controllers\VendorController::class, 'pembayaran'])->name('vendor.pembayaran')->middleware('role:admin,user');
    Route::post('/vendor/pembayaran', [App\Http\Controllers\VendorController::class, 'pembayaran_store'])->name('vendor.pembayaran.store')->middleware('role:admin,user');
    Route::get('/vendor/pembayaran/{id}/edit', [App\Http\Controllers\VendorController::class, 'pembayaran_edit'])->name('vendor.pembayaran.edit')->middleware('role:admin,user');
    Route::post('/vendor/pembayaran/{id}/update', [App\Http\Controllers\VendorController::class, 'pembayaran_update'])->name('vendor.pembayaran.update')->middleware('role:admin,user');


    Route::get('/vendor/{id}/uang-jalan', [App\Http\Controllers\VendorController::class, 'uang_jalan'])->name('vendor.uang-jalan')->middleware('role:admin,user');
    Route::post('/vendor/uang-jalan', [App\Http\Controllers\VendorController::class, 'uang_jalan_store'])->name('vendor.uang-jalan.store')->middleware('role:admin,user');
    Route::get('/vendor/uang-jalan/{id}/edit', [App\Http\Controllers\VendorController::class, 'uang_jalan_edit'])->name('vendor.uang-jalan.edit')->middleware('role:admin,user');
    Route::post('/vendor/uang-jalan/{id}/update', [App\Http\Controllers\VendorController::class, 'uang_jalan_update'])->name('vendor.uang-jalan.update')->middleware('role:admin,user');

    Route::get('/vendor/biodata-vendor/{id}', [App\Http\Controllers\VendorController::class, 'biodata_vendor'])->name('vendor.biodata-vendor')->middleware('role:admin,user');


    Route::resource('rute', App\Http\Controllers\RuteController::class)->only([
        'index', 'store', 'update', 'destroy'
    ])->middleware('role:admin');



    Route::resource('kontrak', App\Http\Controllers\KontrakController::class)->middleware('role:admin,user');
    Route::get('kontrak-doc/{kontrak}', [App\Http\Controllers\KontrakController::class, 'kontrak_doc'])->name('kontrak.doc')->middleware('role:admin,user');
    Route::post('kontrak/upload/{kontrak}', [App\Http\Controllers\KontrakController::class, 'upload'])->name('kontrak.upload')->middleware('role:admin,user');
    Route::get('kontrak/view/{kontrak}', [App\Http\Controllers\KontrakController::class, 'view_file'])->name('kontrak.view')->middleware('role:admin,user');
    Route::get('kontrak/hapus-file/{kontrak}', [App\Http\Controllers\KontrakController::class, 'delete_file'])->name('kontrak.hapus-file')->middleware('role:admin,user');

    Route::resource('spk', App\Http\Controllers\SpkController::class)->middleware('role:admin,user');
    Route::get('spk-doc/{spk}', [App\Http\Controllers\SpkController::class, 'spk_doc'])->name('spk.doc')->middleware('role:admin,user');
    Route::post('spk/upload/{spk}', [App\Http\Controllers\SpkController::class, 'upload'])->name('spk.upload')->middleware('role:admin,user');
    Route::get('spk/view/{spk}', [App\Http\Controllers\SpkController::class, 'view_file'])->name('spk.view')->middleware('role:admin,user');
    Route::get('spk/hapus-file/{spk}', [App\Http\Controllers\SpkController::class, 'delete_file'])->name('spk.hapus-file')->middleware('role:admin,user');

    Route::group(['middleware' => 'role:admin'], function() {
        Route::resource('karyawan', App\Http\Controllers\KaryawanController::class);
        Route::post('karyawan/jabatan-store', [App\Http\Controllers\KaryawanController::class, 'jabatan_store'])->name('karyawan.jabatan-store');
        Route::patch('karyawan/jabatan-update/{jabatan}', [App\Http\Controllers\KaryawanController::class, 'jabatan_update'])->name('karyawan.jabatan-update');
        Route::delete('karyawan/jabatan-delete/{jabatan}', [App\Http\Controllers\KaryawanController::class, 'jabatan_destroy'])->name('karyawan.jabatan-destroy');

        Route::resource('customer', App\Http\Controllers\CustomerController::class);
        Route::post('customer/{customer}/document-store', [App\Http\Controllers\CustomerController::class, 'document_store'])->name('customer.document-store');
        Route::delete('customer/document-delete/{document}', [App\Http\Controllers\CustomerController::class, 'document_destroy'])->name('customer.document-destroy');
        Route::get('customer/document-download/{document}', [App\Http\Controllers\CustomerController::class, 'document_download'])->name('customer.document-download');

        Route::resource('pengguna', App\Http\Controllers\UserController::class);

        Route::resource('vehicle', App\Http\Controllers\VehicleController::class);
    });


});


