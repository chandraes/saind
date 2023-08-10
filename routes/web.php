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

// Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'role:admin']], function () {

//     Route::resource('users', App\Http\Controllers\Admin\UserController::class);

// });

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dokumen', [App\Http\Controllers\DokumenController::class, 'index'])->name('dokumen');
Route::view('/dokumen/sph', 'dokumen.template.index')->name('dokumen.template');

Route::get('/database', [App\Http\Controllers\DatabaseController::class, 'index'])->name('database');
Route::resource('vendor', App\Http\Controllers\VendorController::class)->middleware('role:admin,user');
Route::get('/vendor/{id}/pembayaran', [App\Http\Controllers\VendorController::class, 'pembayaran'])->name('vendor.pembayaran')->middleware('role:admin,user');
Route::post('/vendor/pembayaran', [App\Http\Controllers\VendorController::class, 'pembayaran_update'])->name('vendor.pembayaran.store')->middleware('role:admin,user');


Route::get('/vendor/{id}/uang-jalan', [App\Http\Controllers\VendorController::class, 'uang_jalan'])->name('vendor.uang-jalan')->middleware('role:admin,user');
Route::post('/vendor/uang-jalan', [App\Http\Controllers\VendorController::class, 'uang_jalan_store'])->name('vendor.uang-jalan.store')->middleware('role:admin,user');
Route::get('/vendor/uang-jalan/{id}/edit', [App\Http\Controllers\VendorController::class, 'uang_jalan_edit'])->name('vendor.uang-jalan.edit')->middleware('role:admin,user');

Route::get('/vendor/biodata-vendor/{id}', [App\Http\Controllers\VendorController::class, 'biodata_vendor'])->name('vendor.biodata-vendor')->middleware('role:admin,user');


Route::resource('rute', App\Http\Controllers\RuteController::class)->only([
    'index', 'store', 'update', 'destroy'
])->middleware('role:admin');

Route::resource('customer', App\Http\Controllers\CustomerController::class)->middleware('role:admin');
Route::resource('pengguna', App\Http\Controllers\UserController::class)->middleware('role:admin');

Route::resource('kontrak', App\Http\Controllers\KontrakController::class)->middleware('role:admin,user');
Route::get('kontrak-doc/{kontrak}', [App\Http\Controllers\KontrakController::class, 'kontrak_doc'])->name('kontrak.doc')->middleware('role:admin,user');

Route::resource('spk', App\Http\Controllers\SpkController::class)->middleware('role:admin,user');
Route::get('spk-doc/{spk}', [App\Http\Controllers\SpkController::class, 'spk_doc'])->name('spk.doc')->middleware('role:admin,user');
