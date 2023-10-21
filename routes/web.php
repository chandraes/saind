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
    // Route::view('/dokumen/sph', 'dokumen.template.index')->name('dokumen.template');

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

        Route::view('pengaturan', 'pengaturan.index')->name('pengaturan');
        Route::post('password-konfirmasi', [App\Http\Controllers\PasswordKonfirmasiController::class, 'store'])->name('password-konfirmasi.store');
        Route::get('pengaturan/wa', [App\Http\Controllers\WaController::class, 'wa'])->name('pengaturan.wa');
        Route::get('pengaturan/wa/edit/{id}', [App\Http\Controllers\WaController::class, 'edit'])->name('pengaturan.wa.edit');
        Route::patch('pengaturan/wa/update/{id}', [App\Http\Controllers\WaController::class, 'update'])->name('pengaturan.wa.update');

        Route::resource('karyawan', App\Http\Controllers\KaryawanController::class);
        Route::post('karyawan/jabatan-store', [App\Http\Controllers\KaryawanController::class, 'jabatan_store'])->name('karyawan.jabatan-store');
        Route::patch('karyawan/jabatan-update/{jabatan}', [App\Http\Controllers\KaryawanController::class, 'jabatan_update'])->name('karyawan.jabatan-update');
        Route::delete('karyawan/jabatan-delete/{jabatan}', [App\Http\Controllers\KaryawanController::class, 'jabatan_destroy'])->name('karyawan.jabatan-destroy');

        Route::resource('customer', App\Http\Controllers\CustomerController::class);
        Route::post('customer/{customer}/document-store', [App\Http\Controllers\CustomerController::class, 'document_store'])->name('customer.document-store');
        Route::delete('customer/document-delete/{document}', [App\Http\Controllers\CustomerController::class, 'document_destroy'])->name('customer.document-destroy');
        Route::get('customer/document-download/{document}', [App\Http\Controllers\CustomerController::class, 'document_download'])->name('customer.document-download');

        Route::get('customer/{customer}/tagihan', [App\Http\Controllers\CustomerController::class, 'tagihan'])->name('customer.tagihan');
        Route::post('customer/{customer}/tagihan-store', [App\Http\Controllers\CustomerController::class, 'tagihan_store'])->name('customer.tagihan-store');
        Route::get('customer/{customer}/tagihan-edit', [App\Http\Controllers\CustomerController::class, 'tagihan_edit'])->name('customer.tagihan-edit');
        Route::patch('customer/{customer}/tagihan-update', [App\Http\Controllers\CustomerController::class, 'tagihan_update'])->name('customer.tagihan-update');

        Route::resource('pengguna', App\Http\Controllers\UserController::class);

        Route::resource('sponsor', App\Http\Controllers\SponsorController::class)->only([
            'index','store','update','destroy'
        ]);

        Route::resource('kategori-barang', App\Http\Controllers\KategoriBarangController::class);
        Route::post('database/kategori-barang-store', [App\Http\Controllers\KategoriBarangController::class, 'kategori_store'])->name('database.kategori-barang-store');
        Route::delete('database/kategori-barang-destroy/{kategori}', [App\Http\Controllers\KategoriBarangController::class, 'kategori_destroy'])->name('database.kategori-barang-destroy');
        Route::patch('database/kategori-barang-update/{kategori}', [App\Http\Controllers\KategoriBarangController::class, 'kategori_update'])->name('database.kategori-barang-update');

        Route::resource('vehicle', App\Http\Controllers\VehicleController::class);

        Route::view('template', 'dokumen.template.index')->name('template');
        Route::resource('template-spk', App\Http\Controllers\TemplateSpkController::class);
        Route::get('spk-template/preview', [App\Http\Controllers\TemplateSpkController::class, 'preview'])->name('spk-template.preview');

        Route::resource('template-kontrak', App\Http\Controllers\TemplateKontrakController::class);

        Route::get('kontrak-template/preview', [App\Http\Controllers\TemplateKontrakController::class, 'preview'])->name('kontrak-template.preview');

        Route::get('kas-besar/masuk', [App\Http\Controllers\FormKasBesarController::class, 'masuk'])->name('kas-besar.masuk');
        Route::post('kas-besar/masuk', [App\Http\Controllers\FormKasBesarController::class, 'masuk_store'])->name('kas-besar.masuk.store');
        Route::get('kas-besar/keluar', [App\Http\Controllers\FormKasBesarController::class, 'keluar'])->name('kas-besar.keluar');
        Route::post('kas-besar/keluar', [App\Http\Controllers\FormKasBesarController::class, 'keluar_store'])->name('kas-besar.keluar.store');

        Route::resource('kas-besar', App\Http\Controllers\KasBesarController::class);

        Route::resource('rekening', App\Http\Controllers\RekeningController::class)->only([
            'index','edit','update'
        ]);

        Route::get('form-lain-lain/masuk', [App\Http\Controllers\FormLainController::class, 'masuk'])->name('form-lain-lain.masuk');
        Route::post('form-lain-lain/masuk', [App\Http\Controllers\FormLainController::class, 'masuk_store'])->name('form-lain-lain.masuk.store');
        Route::get('form-lain-lain/keluar', [App\Http\Controllers\FormLainController::class, 'keluar'])->name('form-lain-lain.keluar');
        Route::post('form-lain-lain/keluar', [App\Http\Controllers\FormLainController::class, 'keluar_store'])->name('form-lain-lain.keluar.store');
    });

    Route::get('kas-kecil/masuk', [App\Http\Controllers\FormKasKecilController::class, 'masuk'])->name('kas-kecil.masuk');
    Route::post('kas-kecil/masuk', [App\Http\Controllers\FormKasKecilController::class, 'masuk_store'])->name('kas-kecil.masuk.store');
    Route::get('kas-kecil/keluar', [App\Http\Controllers\FormKasKecilController::class, 'keluar'])->name('kas-kecil.keluar');
    Route::post('kas-kecil/keluar', [App\Http\Controllers\FormKasKecilController::class, 'keluar_store'])->name('kas-kecil.keluar.store');
    Route::get('kas-kecil/void', [App\Http\Controllers\FormKasKecilController::class, 'void'])->name('kas-kecil.void');
    Route::post('kas-kecil/void', [App\Http\Controllers\FormKasKecilController::class, 'void_store'])->name('kas-kecil.void.store');
    Route::get('kas-kecil/get-void', [App\Http\Controllers\FormKasKecilController::class, 'get_void'])->name('kas-kecil.get-void');

    Route::get('kas-uang-jalan/masuk', [App\Http\Controllers\FormKasUangJalanController::class, 'masuk'])->name('kas-uang-jalan.masuk');
    Route::post('kas-uang-jalan/masuk', [App\Http\Controllers\FormKasUangJalanController::class, 'masuk_store'])->name('kas-uang-jalan.masuk.store');
    Route::get('kas-uang-jalan/keluar', [App\Http\Controllers\FormKasUangJalanController::class, 'keluar'])->name('kas-uang-jalan.keluar');
    Route::post('kas-uang-jalan/keluar', [App\Http\Controllers\FormKasUangJalanController::class, 'keluar_store'])->name('kas-uang-jalan.keluar.store');
    Route::get('kas-uang-jalan/get-vendor', [App\Http\Controllers\FormKasUangJalanController::class, 'get_vendor'])->name('kas-uang-jalan.get-vendor');
    Route::get('kas-uang-jalan/get-rute', [App\Http\Controllers\FormKasUangJalanController::class, 'get_rute'])->name('kas-uang-jalan.get-rute');
    Route::get('kas-uang-jalan/get-uang-jalan', [App\Http\Controllers\FormKasUangJalanController::class, 'get_uang_jalan'])->name('kas-uang-jalan.get-uang-jalan');

    Route::get('transaksi/nota-muat', [App\Http\Controllers\TransaksiController::class, 'nota_muat'])->name('transaksi.nota-muat');
    Route::patch('transaksi/nota-muat/update/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'nota_muat_update'])->name('transaksi.nota-muat.update');
    Route::get('transaksi/nota-bongkar', [App\Http\Controllers\TransaksiController::class, 'nota_bongkar'])->name('transaksi.nota-bongkar');
    Route::patch('transaksi/nota-bongkar/update/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'nota_bongkar_update'])->name('transaksi.nota-bongkar.update');
    Route::get('transaksi/nota-tagihan/{customer}', [App\Http\Controllers\TransaksiController::class, 'nota_tagihan'])->name('transaksi.nota-tagihan');

    Route::get('transaksi/nota-tagihan/{customer}/export', [App\Http\Controllers\TransaksiController::class, 'tagihan_export'])->name('transaksi.nota-tagihan.export');
    Route::post('transaksi/nota-bayar', [App\Http\Controllers\TransaksiController::class, 'nota_bayar'])->name('transaksi.nota-bayar');
    Route::post('transaksi/void-masuk/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'void'])->name('transaksi.void-masuk');
    Route::post('transaksi/void/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'void_store'])->name('transaksi.void.store');
    Route::post('transaksi/back/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'back'])->name('transaksi.back');
    Route::post('transaksi/back-tagihan/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'back_tagihan'])->name('transaksi.back-tagihan');

    Route::resource('bbm-storing', App\Http\Controllers\BbmStoringController::class);

    // billing route
    Route::get('billing', [App\Http\Controllers\BillingController::class, 'index'])->name('billing.index');

    Route::get('billing/transaksi', [App\Http\Controllers\TransaksiController::class, 'index'])->name('billing.transaksi.index');

    Route::get('kas-besar', [App\Http\Controllers\KasBesarController::class, 'index'])->name('kas-besar.index');

    Route::view('rekap', 'rekap.index')->name('rekap.index');
    Route::get('rekap/kas-besar', [App\Http\Controllers\RekapController::class, 'kas_besar'])->name('rekap.kas-besar');
    Route::get('rekap/kas-besar/preview/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'preview_kas_besar'])->name('rekap.kas-besar.preview');
    Route::get('rekap/kas-kecil', [App\Http\Controllers\RekapController::class, 'kas_kecil'])->name('rekap.kas-kecil');
    Route::get('rekap/kas-kecil/preview/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'preview_kas_kecil'])->name('rekap.kas-kecil.preview');
    Route::get('rekap/kas-uang-jalan', [App\Http\Controllers\RekapController::class, 'kas_uang_jalan'])->name('rekap.kas-uang-jalan');
    Route::get('rekap/kas-uang-jalan/preview/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'preview_kas_uang_jalan'])->name('rekap.kas-uang-jalan.preview');
});


