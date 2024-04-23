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


    Route::group(['middleware' => 'role:user'], function() {

    });

    Route::group(['middleware' => 'role:su'], function() {
        Route::prefix('bypass')->group(function(){
            Route::get('/', [App\Http\Controllers\ByPassVendorController::class, 'index'])->name('bypass.index');
            Route::get('/kas-direksi', [App\Http\Controllers\ByPassVendorController::class, 'kas_direksi'])->name('bypass-kas-direksi.index');
            Route::post('/kas-direksi', [App\Http\Controllers\ByPassVendorController::class, 'kas_direksi_store'])->name('bypass-kas-direksi.store');

            Route::get('/kas-besar', [App\Http\Controllers\ByPassVendorController::class, 'by_pass_kas_besar'])->name('bypass-kas-besar.index');
            Route::post('/kas-besar', [App\Http\Controllers\ByPassVendorController::class, 'by_pass_kas_besar_store'])->name('bypass-kas-besar.store');
        });
    });


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

    Route::group(['middleware' => 'role:admin,su'], function() {

        Route::get('/invoice-tagihan-back/{invoice}', [App\Http\Controllers\InvoiceController::class, 'invoice_tagihan_back'])->name('invoice.tagihan-back.execute');

        Route::get('/bypass-kas-vendor', [App\Http\Controllers\ByPassVendorController::class, 'kas_vendor'])->name('bypass-kas-vendor.index');
        Route::post('/bypass-kas-vendor', [App\Http\Controllers\ByPassVendorController::class, 'kas_vendor_store'])->name('bypass-kas-vendor.store');

        Route::get('/dokumen', [App\Http\Controllers\DokumenController::class, 'index'])->name('dokumen');


        Route::prefix('database')->group(function(){
            Route::get('/', [App\Http\Controllers\DatabaseController::class, 'index'])->name('database');

             Route::prefix('upah-gendong')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'upah_gendong'])->name('database.upah-gendong');
                Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'upah_gendong_store'])->name('database.upah-gendong.store');
                Route::patch('/update/{ug}', [App\Http\Controllers\DatabaseController::class, 'upah_gendong_update'])->name('database.upah-gendong.update');
                Route::delete('/destroy/{ug}', [App\Http\Controllers\DatabaseController::class, 'upah_gendong_destroy'])->name('database.upah-gendong.destroy');
             });
        });

        Route::resource('vendor', App\Http\Controllers\VendorController::class);
        Route::get('/vendor/{id}/pembayaran', [App\Http\Controllers\VendorController::class, 'pembayaran'])->name('vendor.pembayaran');
        Route::post('/vendor/pembayaran', [App\Http\Controllers\VendorController::class, 'pembayaran_store'])->name('vendor.pembayaran.store');
        Route::get('/vendor/pembayaran/{id}/edit', [App\Http\Controllers\VendorController::class, 'pembayaran_edit'])->name('vendor.pembayaran.edit');
        Route::post('/vendor/pembayaran/{id}/update', [App\Http\Controllers\VendorController::class, 'pembayaran_update'])->name('vendor.pembayaran.update');


        Route::get('/vendor/{id}/uang-jalan', [App\Http\Controllers\VendorController::class, 'uang_jalan'])->name('uj.vendor.uang-jalan');
        Route::post('/vendor/uang-jalan', [App\Http\Controllers\VendorController::class, 'uang_jalan_store'])->name('uj.vendor.uang-jalan.store');
        Route::get('uj/vendor/uang-jalan/{vendor}/edit', [App\Http\Controllers\VendorController::class, 'uang_jalan_edit'])->name('uj.vendor.uang-jalan.edit');
        Route::post('/vendor/uang-jalan/{id}/update', [App\Http\Controllers\VendorController::class, 'uang_jalan_update'])->name('uj.vendor.uang-jalan.update');
        Route::get('/preview-vendor', [App\Http\Controllers\VendorController::class, 'preview_vendor'])->name('uj.vendor.preview-vendor');

        Route::get('/vendor/biodata-vendor/{id}', [App\Http\Controllers\VendorController::class, 'biodata_vendor'])->name('uj.vendor.biodata-vendor');

        Route::resource('rute', App\Http\Controllers\RuteController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);

        Route::post('database/persentase-awal-store', [App\Http\Controllers\PersentaseAwalController::class, 'store'])->name('database.persentase-awal-store');
        Route::patch('database/persentase-awal-update/{awal}', [App\Http\Controllers\PersentaseAwalController::class, 'update'])->name('database.persentase-awal-update');
        Route::delete('database/persentase-awal-destroy/{awal}', [App\Http\Controllers\PersentaseAwalController::class, 'destroy'])->name('database.persentase-awal-destroy');

        Route::resource('pemegang-saham', App\Http\Controllers\PemegangSahamController::class);

        Route::view('pengaturan', 'pengaturan.index')->name('pengaturan');
        Route::post('password-konfirmasi', [App\Http\Controllers\PasswordKonfirmasiController::class, 'store'])->name('password-konfirmasi.store');


        Route::prefix('pengaturan')->group(function(){
            Route::get('/wa', [App\Http\Controllers\WaController::class, 'wa'])->name('pengaturan.wa');
            Route::get('/wa/edit/{id}', [App\Http\Controllers\WaController::class, 'edit'])->name('pengaturan.wa.edit');
            Route::patch('/wa/update/{id}', [App\Http\Controllers\WaController::class, 'update'])->name('pengaturan.wa.update');
            Route::get('/nota-transaksi', [App\Http\Controllers\KonfigurasiController::class, 'index'])->name('pengaturan.nota-transaksi');
            Route::patch('/nota-transaksi/update/{konfigurasi}', [App\Http\Controllers\KonfigurasiController::class, 'update'])->name('pengaturan.konfigurasi-transaksi.update');

            Route::patch('/nota-transaksi/update-jam/{konfigurasi}', [App\Http\Controllers\KonfigurasiController::class, 'update_jam'])->name('pengaturan.konfigurasi-transaksi.update-jam');
        });

        Route::resource('direksi', App\Http\Controllers\DireksiController::class);

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
        Route::post('customer/{customer}/ubah-status', [App\Http\Controllers\CustomerController::class, 'ubah_status'])->name('customer.ubah-status');
        Route::get('database/customer/preview-customer', [App\Http\Controllers\CustomerController::class, 'preview_customer'])->name('database.customer.preview-customer');

        Route::resource('pengguna', App\Http\Controllers\UserController::class);

        Route::resource('sponsor', App\Http\Controllers\SponsorController::class)->only([
            'index','store','update','destroy'
        ]);

        Route::resource('kategori-barang', App\Http\Controllers\KategoriBarangController::class);
        Route::post('database/kategori-barang-store', [App\Http\Controllers\KategoriBarangController::class, 'kategori_store'])->name('database.kategori-barang-store');
        Route::delete('database/kategori-barang-destroy/{kategori}', [App\Http\Controllers\KategoriBarangController::class, 'kategori_destroy'])->name('database.kategori-barang-destroy');
        Route::patch('database/kategori-barang-update/{kategori}', [App\Http\Controllers\KategoriBarangController::class, 'kategori_update'])->name('database.kategori-barang-update');

        Route::prefix('database')->group(function(){
            Route::prefix('aktivasi-maintenance')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'aktivasi_maintenance'])->name('database.aktivasi-maintenance');
                Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'aktivasi_maintenance_store'])->name('database.aktivasi-maintenance.store');
                Route::patch('/update/{am}', [App\Http\Controllers\DatabaseController::class, 'aktivasi_maintenance_update'])->name('database.aktivasi-maintenance.update');
                Route::delete('/destroy/{am}', [App\Http\Controllers\DatabaseController::class, 'aktivasi_maintenance_destroy'])->name('database.aktivasi-maintenance.destroy');
            });
            Route::prefix('barang-maintenance')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'barang_maintenance'])->name('database.barang-maintenance');
                Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'barang_maintenance_store'])->name('database.barang-maintenance.store');
                Route::patch('/update/{bm}', [App\Http\Controllers\DatabaseController::class, 'barang_maintenance_update'])->name('database.barang-maintenance.update');
                Route::delete('/destroy/{bm}', [App\Http\Controllers\DatabaseController::class, 'barang_maintenance_destroy'])->name('database.barang-maintenance.destroy');

                Route::post('/store-kategori', [App\Http\Controllers\DatabaseController::class, 'kategori_store'])->name('database.barang-maintenance.kategori.store');
                Route::patch('/update-kategori/{kategori}', [App\Http\Controllers\DatabaseController::class, 'kategori_update'])->name('database.barang-maintenance.kategori.update');
                Route::delete('/destroy-kategori/{kategori}', [App\Http\Controllers\DatabaseController::class, 'kategori_destroy'])->name('database.barang-maintenance.kategori.destroy');
            });
        });

        Route::resource('barang', App\Http\Controllers\BarangController::class)->only([
            'store','update','destroy'
        ]);

        Route::resource('vehicle', App\Http\Controllers\VehicleController::class);
        Route::get('print-preview-vehicle', [App\Http\Controllers\VehicleController::class, 'print_preview_vehicle'])->name('print-preview-vehicle');

        Route::view('template', 'dokumen.template.index')->name('template');
        Route::resource('template-spk', App\Http\Controllers\TemplateSpkController::class);
        Route::get('spk-template/preview', [App\Http\Controllers\TemplateSpkController::class, 'preview'])->name('spk-template.preview');

        Route::resource('template-kontrak', App\Http\Controllers\TemplateKontrakController::class);

        Route::get('kontrak-template/preview', [App\Http\Controllers\TemplateKontrakController::class, 'preview'])->name('kontrak-template.preview');

        Route::resource('rekening', App\Http\Controllers\RekeningController::class)->only([
            'index','edit','update'
        ]);

        Route::get('form-lain-lain/masuk', [App\Http\Controllers\FormLainController::class, 'masuk'])->name('form-lain-lain.masuk');
        Route::post('form-lain-lain/masuk', [App\Http\Controllers\FormLainController::class, 'masuk_store'])->name('form-lain-lain.masuk.store');
        Route::get('form-lain-lain/keluar', [App\Http\Controllers\FormLainController::class, 'keluar'])->name('form-lain-lain.keluar');
        Route::post('form-lain-lain/keluar', [App\Http\Controllers\FormLainController::class, 'keluar_store'])->name('form-lain-lain.keluar.store');

        Route::get('statisik/profit-bulanan', [App\Http\Controllers\StatistikController::class, 'profit_bulanan'])->name('statisik.profit-bulanan');
        Route::get('statistik/profit-bulanan/print', [App\Http\Controllers\StatistikController::class, 'profit_bulanan_print'])->name('statistik.profit-bulanan.print');
        Route::get('statistik/profit-tahunan', [App\Http\Controllers\StatistikController::class, 'profit_tahunan'])->name('statistik.profit-tahunan');
        Route::get('statistik/profit-tahunan/print', [App\Http\Controllers\StatistikController::class, 'profit_tahunan_print'])->name('statistik.profit-tahunan.print');


        Route::get('statistik/perform-vendor/print', [App\Http\Controllers\StatistikController::class, 'perform_vendor_print'])->name('statistik.perform-vendor.print');
    });

    Route::get('billing', [App\Http\Controllers\BillingController::class, 'index'])->name('billing.index')->middleware('role:admin,user,su');

    Route::group(['middleware' => 'role:admin,user,su'], function() {
        Route::get('statisik', [App\Http\Controllers\StatistikController::class, 'index'])->name('statisik.index');

        // Route::resource('kas-besar', App\Http\Controllers\KasBesarController::class);

        Route::prefix('kas-besar')->group(function(){
            Route::get('/masuk', [App\Http\Controllers\FormKasBesarController::class, 'masuk'])->name('kas-besar.masuk');
            Route::post('/masuk', [App\Http\Controllers\FormKasBesarController::class, 'masuk_store'])->name('kas-besar.masuk.store');
            Route::get('/keluar', [App\Http\Controllers\FormKasBesarController::class, 'keluar'])->name('kas-besar.keluar');
            Route::post('/keluar', [App\Http\Controllers\FormKasBesarController::class, 'keluar_store'])->name('kas-besar.keluar.store');
        });

        Route::prefix('kas-kecil')->group(function(){
            Route::get('/masuk', [App\Http\Controllers\FormKasKecilController::class, 'masuk'])->name('kas-kecil.masuk');
            Route::post('/masuk', [App\Http\Controllers\FormKasKecilController::class, 'masuk_store'])->name('kas-kecil.masuk.store');
            Route::get('/keluar', [App\Http\Controllers\FormKasKecilController::class, 'keluar'])->name('kas-kecil.keluar');
            Route::post('/keluar', [App\Http\Controllers\FormKasKecilController::class, 'keluar_store'])->name('kas-kecil.keluar.store');
            Route::get('/void', [App\Http\Controllers\FormKasKecilController::class, 'void'])->name('kas-kecil.void');
            Route::post('/void', [App\Http\Controllers\FormKasKecilController::class, 'void_store'])->name('kas-kecil.void.store');
            Route::get('/get-void', [App\Http\Controllers\FormKasKecilController::class, 'get_void'])->name('kas-kecil.get-void');
        });

        Route::prefix('kas-uang-jalan')->group(function(){
            Route::get('/masuk', [App\Http\Controllers\FormKasUangJalanController::class, 'masuk'])->name('kas-uang-jalan.masuk');
            Route::post('/masuk', [App\Http\Controllers\FormKasUangJalanController::class, 'masuk_store'])->name('kas-uang-jalan.masuk.store');
            Route::get('/keluar', [App\Http\Controllers\FormKasUangJalanController::class, 'keluar'])->name('kas-uang-jalan.keluar');
            Route::post('/keluar', [App\Http\Controllers\FormKasUangJalanController::class, 'keluar_store'])->name('kas-uang-jalan.keluar.store');
            Route::get('/get-vendor', [App\Http\Controllers\FormKasUangJalanController::class, 'get_vendor'])->name('kas-uang-jalan.get-vendor');
            Route::get('/get-rute', [App\Http\Controllers\FormKasUangJalanController::class, 'get_rute'])->name('kas-uang-jalan.get-rute');
            Route::get('/get-uang-jalan', [App\Http\Controllers\FormKasUangJalanController::class, 'get_uang_jalan'])->name('kas-uang-jalan.get-uang-jalan');
        });

        //Form maintenance
        Route::prefix('billing')->group(function(){

            Route::get('/nota-csr', [App\Http\Controllers\TransaksiController::class, 'nota_csr'])->name('billing.nota-csr');
            Route::post('/nota-csr/lanjut', [App\Http\Controllers\TransaksiController::class, 'nota_csr_lanjut'])->name('billing.nota-csr.lanjut');
            Route::get('/invoice-csr', [App\Http\Controllers\InvoiceController::class, 'invoice_csr'])->name('billing.invoice-csr');
            Route::get('/invoice-csr/{invoiceCsr}/detail', [App\Http\Controllers\InvoiceController::class, 'invoice_csr_detail'])->name('billing.invoice-csr.detail');
            Route::post('/invoice-csr/{invoiceCsr}/lunas', [App\Http\Controllers\InvoiceController::class, 'invoice_csr_lunas'])->name('invoice.csr.lunas');

            Route::prefix('form-maintenance')->group(function(){
                Route::get('/beli', [App\Http\Controllers\FormMaintenanceController::class, 'beli'])->name('billing.form-maintenance.beli');
                Route::post('/barang-store', [App\Http\Controllers\FormMaintenanceController::class, 'beli_store'])->name('billing.form-maintenance.barang-store');
                Route::post('/keranjang-store', [App\Http\Controllers\FormMaintenanceController::class, 'keranjang_store'])->name('billing.form-maintenance.keranjang-store');
                Route::delete('/keranjang-destroy/{keranjang}', [App\Http\Controllers\FormMaintenanceController::class, 'keranjang_destroy'])->name('billing.form-maintenance.keranjang-destroy');
                Route::get('/keranjang-empty', [App\Http\Controllers\FormMaintenanceController::class, 'keranjang_empty'])->name('billing.form-maintenance.keranjang-empty');

                Route::get('/get-harga-jual', [App\Http\Controllers\FormMaintenanceController::class, 'get_harga_jual'])->name('billing.form-maintenance.get-harga-jual');
                Route::get('/get-barang', [App\Http\Controllers\FormMaintenanceController::class, 'get_barang'])->name('billing.form-maintenance.get-barang');

                Route::get('/jual-vendor', [App\Http\Controllers\FormMaintenanceController::class, 'jual_vendor'])->name('billing.form-maintenance.jual-vendor');
                Route::post('/jual-vendor-store', [App\Http\Controllers\FormMaintenanceController::class, 'jual_vendor_store'])->name('billing.form-maintenance.jual-vendor-store');
                Route::get('/jual-umum', [App\Http\Controllers\FormMaintenanceController::class, 'jual_umum'])->name('billing.form-maintenance.jual-umum');

            });

            Route::prefix('form-barang')->group(function(){
                Route::get('/beli', [App\Http\Controllers\FormBarangController::class, 'beli'])->name('billing.form-barang.beli');
                Route::get('/get-barang', [App\Http\Controllers\FormBarangController::class, 'get_barang'])->name('billing.form-barang.get-barang');
                Route::post('/keranjang-store', [App\Http\Controllers\FormBarangController::class, 'keranjang_store'])->name('billing.form-barang.keranjang-store');
                Route::delete('/keranjang-destroy/{keranjang}', [App\Http\Controllers\FormBarangController::class, 'keranjang_destroy'])->name('billing.form-barang.keranjang-destroy');
                Route::get('/keranjang-empty', [App\Http\Controllers\FormBarangController::class, 'keranjang_empty'])->name('billing.form-barang.keranjang-empty');
                Route::get('/barang-store', [App\Http\Controllers\FormBarangController::class, 'beli_store'])->name('billing.form-barang.barang-store');
                Route::get('/jual', [App\Http\Controllers\FormBarangController::class, 'jual'])->name('billing.form-barang.jual');
                Route::post('/jual-store', [App\Http\Controllers\FormBarangController::class, 'jual_store'])->name('billing.form-barang.jual-store');
                Route::get('/get-harga-jual', [App\Http\Controllers\FormBarangController::class, 'get_harga_jual'])->name('billing.form-barang.get-harga-jual');
            });

            // form vendor
            Route::prefix('vendor')->group(function(){
                Route::get('/titipan', [App\Http\Controllers\FormVendorController::class, 'titipan'])->name('billing.vendor.titipan');
                Route::post('/titipan-store', [App\Http\Controllers\FormVendorController::class, 'titipan_store'])->name('billing.vendor.titipan-store');
                Route::get('/pelunasan', [App\Http\Controllers\FormVendorController::class, 'pelunasan'])->name('billing.vendor.pelunasan');
                Route::post('/pelunasan-store', [App\Http\Controllers\FormVendorController::class, 'pelunasan_store'])->name('billing.vendor.pelunasan-store');
                Route::get('/get-kas-vendor', [App\Http\Controllers\FormVendorController::class, 'get_kas_vendor'])->name('billing.vendor.get-kas-vendor');
                Route::get('/bayar', [App\Http\Controllers\FormVendorController::class, 'bayar'])->name('billing.vendor.bayar');
                Route::post('/bayar-store', [App\Http\Controllers\FormVendorController::class, 'bayar_store'])->name('billing.vendor.bayar-store');
                Route::get('/get-vehicle', [App\Http\Controllers\FormVendorController::class, 'get_vehicle'])->name('billing.vendor.get-vehicle');
                Route::get('/get-plafon-titipan', [App\Http\Controllers\FormVendorController::class, 'get_plafon_titipan'])->name('billing.vendor.get-plafon-titipan');
            });


            // form kasbon
            Route::prefix('kasbon')->group(function(){
                Route::get('/', [App\Http\Controllers\FormKasbonController::class, 'index'])->name('billing.kasbon.index');

                Route::prefix('direksi')->group(function(){
                    Route::view('/', 'billing.kasbon.direksi.index')->name('billing.kasbon.direksi.index');
                    Route::get('/kasbon', [App\Http\Controllers\FormKasbonController::class, 'direksi_kas'])->name('billing.kasbon.direksi.kasbon');
                    Route::get('/bayar', [App\Http\Controllers\FormKasbonController::class, 'direksi_bayar'])->name('billing.kasbon.direksi.bayar');
                    Route::get('/bayar/list', [App\Http\Controllers\FormKasbonController::class, 'direksi_bayar_list'])->name('billing.kasbon.direksi.bayar.list');
                    Route::post('/bayar-store/{direksi}', [App\Http\Controllers\FormKasbonController::class, 'direksi_bayar_store'])->name('billing.kasbon.direksi.bayar-store');
                    Route::post('/kasbon-store', [App\Http\Controllers\FormKasbonController::class, 'direksi_kas_store'])->name('billing.kasbon.direksi.kasbon-store');
                });

                Route::post('/store', [App\Http\Controllers\FormKasbonController::class, 'store'])->name('billing.kasbon.store');
                Route::view('/kas-bon-staff', 'billing.kasbon.kas-bon-staff')->name('billing.kasbon.kas-bon-staff');
                Route::get('/kas-bon-cicil', [App\Http\Controllers\FormKasbonController::class, 'kas_bon_cicil'])->name('billing.kasbon.kas-bon-cicil');
                Route::post('/kas-bon-cicil-store', [App\Http\Controllers\FormKasbonController::class, 'kas_bon_cicil_store'])->name('billing.kasbon.kas-bon-cicil-store');
            });

            Route::prefix('storing')->group(function(){
                Route::get('/index', [App\Http\Controllers\FormStoringConroller::class, 'index'])->name('billing.storing.index');
                Route::post('/store', [App\Http\Controllers\FormStoringConroller::class, 'store'])->name('billing.storing.store');
                Route::get('/void', [App\Http\Controllers\FormStoringConroller::class, 'void'])->name('billing.storing.void');
                Route::get('/get-storing', [App\Http\Controllers\FormStoringConroller::class, 'get_storing'])->name('billing.storing.get-storing');
                Route::get('/get-status-so', [App\Http\Controllers\FormStoringConroller::class, 'get_status_so'])->name('billing.storing.get-status-so');
                Route::get('/get-vendor', [App\Http\Controllers\FormStoringConroller::class, 'get_vendor'])->name('billing.storing.get-vendor');
                Route::get('/storing-latest', [App\Http\Controllers\FormStoringConroller::class, 'storing_latest'])->name('billing.storing.storing-latest');
            });

        });


        Route::get('transaksi/nota-muat', [App\Http\Controllers\TransaksiController::class, 'nota_muat'])->name('transaksi.nota-muat');
        Route::patch('transaksi/nota-muat/update/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'nota_muat_update'])->name('transaksi.nota-muat.update');
        Route::get('transaksi/nota-bongkar', [App\Http\Controllers\TransaksiController::class, 'nota_bongkar'])->name('transaksi.nota-bongkar');
        Route::patch('transaksi/nota-bongkar/update/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'nota_bongkar_update'])->name('transaksi.nota-bongkar.update');
        Route::get('transaksi/nota-tagihan/{customer}', [App\Http\Controllers\TransaksiController::class, 'nota_tagihan'])->name('transaksi.nota-tagihan');

        //sales order
        Route::get('transaksi/sales-order', [App\Http\Controllers\TransaksiController::class, 'sales_order'])->name('transaksi.sales-order');

        Route::get('transaksi/nota-tagihan/{customer}/export', [App\Http\Controllers\TransaksiController::class, 'tagihan_export'])->name('transaksi.nota-tagihan.export');
        Route::get('transaksi/nota-tagihan/{transaksi}/check', [App\Http\Controllers\TransaksiController::class, 'nota_tagihan_checked'])->name('transaksi.nota-tagihan.check');
        Route::post('transaksi/nota-tagihan/{transaksi}/uncheck', [App\Http\Controllers\TransaksiController::class, 'nota_tagihan_unchecked'])->name('transaksi.nota-tagihan.uncheck');

        Route::get('transaksi/nota-bayar', [App\Http\Controllers\TransaksiController::class, 'nota_bayar'])->name('transaksi.nota-bayar');
        Route::post('transaksi/nota-bayar/{vendor}/lanjut', [App\Http\Controllers\TransaksiController::class, 'nota_bayar_lanjut'])->name('transaksi.nota-bayar.lanjut');

        Route::get('transaksi/nota-bonus', [App\Http\Controllers\TransaksiController::class, 'nota_bonus'])->name('transaksi.nota-bonus');
        Route::post('transaksi/nota-bonus/{sponsor}/lanjut', [App\Http\Controllers\TransaksiController::class, 'nota_bonus_lanjut'])->name('transaksi.nota-bonus.lanjut');

        Route::post('transaksi/tagihan/void/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'void_tagihan'])->name('transaksi.tagihan.void');
        Route::post('transaksi/tagihan/void/{transaksi}/store', [App\Http\Controllers\TransaksiController::class, 'void_tagihan_store'])->name('transaksi.tagihan.void.store');

        Route::post('transaksi/void-masuk/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'void'])->name('transaksi.void-masuk');
        Route::post('transaksi/void/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'void_store'])->name('transaksi.void.store');
        Route::post('transaksi/back/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'back'])->name('transaksi.back');
        Route::post('transaksi/back-tagihan/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'back_tagihan'])->name('transaksi.back-tagihan');
        Route::post('transaksi/nota-tagihan/edit/{transaksi}', [App\Http\Controllers\TransaksiController::class, 'nota_tagihan_edit'])->name('transaksi.nota-tagihan.edit');
        Route::post('transaksi/nota-tagihan/{transaksi}/update', [App\Http\Controllers\TransaksiController::class, 'nota_tagihan_update'])->name('transaksi.nota-tagihan.update');
        // Route::post('transaksi/nota-tagihan/{customer}/lanjut', [App\Http\Controllers\TransaksiController::class, 'nota_tagihan_lanjut'])->name('transaksi.nota-tagihan.lanjut');
        Route::post('transaksi/nota-tagihan-lanjut-pilih/{customer}', [App\Http\Controllers\TransaksiController::class, 'nota_tagihan_lanjut_pilih'])->name('transaksi.nota-tagihan.lanjut-pilih');

        Route::resource('bbm-storing', App\Http\Controllers\BbmStoringController::class);

        // Form Deviden
        Route::get('billing/deviden', [App\Http\Controllers\FormDevidenController::class, 'index'])->name('billing.deviden.index');
        Route::post('billing/deviden/store', [App\Http\Controllers\FormDevidenController::class, 'store'])->name('billing.deviden.store');

        // Form Gaji
        Route::get('billing/gaji', [App\Http\Controllers\FormGajiController::class, 'index'])->name('billing.gaji.index');
        Route::post('billing/gaji/store', [App\Http\Controllers\FormGajiController::class, 'store'])->name('billing.gaji.store');

        Route::get('billing/transaksi', [App\Http\Controllers\TransaksiController::class, 'index'])->name('billing.transaksi.index');

        Route::get('billing/transaksi/invoice', [App\Http\Controllers\InvoiceController::class, 'index'])->name('billing.transaksi.invoice.index');

        Route::get('billing/transaksi/invoice/tagihan', [App\Http\Controllers\InvoiceController::class, 'tagihan'])->name('invoice.tagihan.index');
        Route::get('billing/transaksi/invoice/tagihan/{invoice}/detail', [App\Http\Controllers\InvoiceController::class, 'invoice_tagihan_detail'])->name('invoice.tagihan.detail');
        Route::get('billing/transaksi/invoice/tagihan-export/{invoice}', [App\Http\Controllers\InvoiceController::class, 'invoice_tagihan_detail_export'])->name('invoice.tagihan-detail.export');
        Route::post('billing/transaksi/invoice/tagihan/{invoice}/lunas', [App\Http\Controllers\InvoiceController::class, 'tagihan_lunas'])->name('invoice.tagihan.lunas');
        Route::post('billing/transaksi/invoice/tagihan/{invoice}/cicil', [App\Http\Controllers\InvoiceController::class, 'tagihan_cicil'])->name('invoice.tagihan.cicil');

        Route::get('billing/transaksi/invoice/bayar', [App\Http\Controllers\InvoiceController::class, 'invoice_bayar'])->name('invoice.bayar.index');
        Route::get('billing/transaksi/invoice/bayar/{invoiceBayar}/detail', [App\Http\Controllers\InvoiceController::class, 'invoice_bayar_detail'])->name('invoice.bayar.detail');
        Route::post('billing/transaksi/invoice/bayar/{invoice}/lunas', [App\Http\Controllers\InvoiceController::class, 'invoice_bayar_lunas'])->name('invoice.bayar.lunas');

        Route::get('billing/transaksi/invoice/bonus', [App\Http\Controllers\InvoiceController::class, 'invoice_bonus'])->name('invoice.bonus.index');
        Route::get('billing/transaksi/invoice/bonus/{invoiceBonus}/detail', [App\Http\Controllers\InvoiceController::class, 'invoice_bonus_detail'])->name('invoice.bonus.detail');
        Route::post('billing/transaksi/invoice/bonus/{invoice}/lunas', [App\Http\Controllers\InvoiceController::class, 'invoice_bonus_lunas'])->name('invoice.bonus.lunas');

        Route::view('rekap-gaji', 'rekap.gaji')->name('rekap-gaji');
        Route::get('rekap-gaji-detail', [App\Http\Controllers\RekapController::class, 'rekap_gaji_detail'])->name('rekap-gaji-detail');
        Route::get('print-rekap-gaji', [App\Http\Controllers\RekapController::class, 'print_rekap_gaji'])->name('print-rekap-gaji');

        Route::prefix('rekap')->group(function(){
            Route::get('/', [App\Http\Controllers\RekapController::class, 'index'])->name('rekap.index');

            Route::get('/kas-besar', [App\Http\Controllers\RekapController::class, 'kas_besar'])->name('rekap.kas-besar');
            Route::get('/kas-besar/preview/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'preview_kas_besar'])->name('rekap.kas-besar.preview');
            Route::get('/kas-kecil', [App\Http\Controllers\RekapController::class, 'kas_kecil'])->name('rekap.kas-kecil');
            Route::get('/kas-kecil/preview/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'preview_kas_kecil'])->name('rekap.kas-kecil.preview');
            Route::get('/kas-uang-jalan', [App\Http\Controllers\RekapController::class, 'kas_uang_jalan'])->name('rekap.kas-uang-jalan');
            Route::get('/kas-uang-jalan/preview/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'preview_kas_uang_jalan'])->name('rekap.kas-uang-jalan.preview');

            Route::get('/kas-vendor', [App\Http\Controllers\RekapController::class, 'kas_vendor'])->name('rekap.kas-vendor');
            Route::get('/kas-vendor/{invoiceBayar}/detail', [App\Http\Controllers\RekapController::class, 'kas_vendor_detail'])->name('rekap.kas-vendor.detail');
            Route::post('/kas-vendor/void/{kas_vendor}', [App\Http\Controllers\RekapController::class, 'kas_vendor_void'])->name('rekap.kas-vendor.void');
            Route::get('/kas-vendor/preview/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'preview_kas_vendor'])->name('rekap.kas-vendor.preview');

            Route::get('/csr', [App\Http\Controllers\RekapController::class, 'rekap_csr'])->name('rekap.csr');
            Route::get('/csr/{invoiceCsr}/detail', [App\Http\Controllers\RekapController::class, 'rekap_csr_detail'])->name('rekap.csr.detail');

            Route::get('/nota-void', [App\Http\Controllers\RekapController::class, 'nota_void'])->name('rekap.nota-void');
            Route::get('/nota-void/preview/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'preview_nota_void'])->name('rekap.nota-void.preview');

            Route::get('/stock-barang', [App\Http\Controllers\RekapController::class, 'stock_barang'])->name('rekap.stock-barang');

            Route::get('/kas-bon', [App\Http\Controllers\RekapController::class, 'kas_bon'])->name('rekap.kas-bon');
            Route::get('/kas-bon/preview/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'preview_kas_bon'])->name('rekap.kas-bon.preview');
            Route::post('/kas-bon/void/{kas}', [App\Http\Controllers\RekapController::class, 'kas_bon_void'])->name('rekap.kas-bon.void');

            Route::get('/kas-bon/direksi', [App\Http\Controllers\RekapController::class, 'kas_bon_direksi'])->name('rekap.kas-bon.direksi');
            Route::get('/direksi', [App\Http\Controllers\RekapController::class, 'direksi'])->name('rekap.direksi');

            Route::get('/bonus', [App\Http\Controllers\RekapController::class, 'rekap_bonus'])->name('rekap.bonus');
            Route::get('/bonus/{invoiceBonus}/detail', [App\Http\Controllers\RekapController::class, 'rekap_bonus_detail'])->name('rekap.bonus.detail');
            Route::get('/nota-lunas', [App\Http\Controllers\RekapController::class, 'nota_lunas'])->name('rekap.nota-lunas');
            Route::get('/nota-lunas-detail/{invoice}', [App\Http\Controllers\RekapController::class, 'nota_lunas_detail'])->name('rekap.nota-lunas-detail');

            Route::get('/maintenance-vehicle', [App\Http\Controllers\RekapController::class, 'maintenance_vehicle'])->name('rekap.maintenance-vehicle');
            Route::get('/maintenance-vehicle/print', [App\Http\Controllers\RekapController::class, 'maintenance_vehicle_print'])->name('rekap.maintenance-vehicle.print');
            Route::post('/maintenance-vehicle/store-odometer', [App\Http\Controllers\RekapController::class, 'store_odo'])->name('rekap.maintenance-vehicle.store-odometer');
        });

        Route::prefix('statistik')->group(function(){
            Route::get('/perform-unit', [App\Http\Controllers\StatistikController::class, 'perform_unit'])->name('statistik.perform-unit');
            Route::get('/perform-unit/print', [App\Http\Controllers\StatistikController::class, 'perform_unit_print'])->name('statistik.perform-unit.print');
            Route::get('/perform-unit-tahunan', [App\Http\Controllers\StatistikController::class, 'perform_unit_tahunan'])->name('statistik.perform-unit-tahunan');
            Route::get('/perform-unit-tahunan/print', [App\Http\Controllers\StatistikController::class, 'perform_unit_tahunan_print'])->name('statistik.perform-unit-tahunan.print');

            Route::get('/customer', [App\Http\Controllers\StatistikController::class, 'statistik_customer'])->name('statistik.customer');

            Route::get('/perform-vendor', [App\Http\Controllers\StatistikController::class, 'perform_vendor'])->name('statistik.perform-vendor');
            Route::get('/vendor', [App\Http\Controllers\StatistikController::class, 'statistik_vendor'])->name('statistik.vendor');

            Route::get('/upah-gendong', [App\Http\Controllers\StatistikController::class, 'upah_gendong'])->name('statistik.upah-gendong');
        });

        Route::get('dokumen/template-new', [App\Http\Controllers\DokumenNewController::class, 'index'])->name('template-new');
        Route::get('dokumen/template-new/kontrak', [App\Http\Controllers\DokumenNewController::class, 'kontrak_new'])->name('template-new.kontrak');
        Route::post('dokumen/template-new/kontrak/create', [App\Http\Controllers\DokumenNewController::class, 'create_template_kontrak'])->name('template-new.kontrak.create');


    });

    Route::group(['middleware' => 'role:vendor'], function() {
        Route::get('kas-per-vendor/{vendor}', [App\Http\Controllers\RekapController::class, 'kas_per_vendor'])->name('kas-per-vendor.index');
        Route::get('kas-per-vendor/{invoiceBayar}/detail', [App\Http\Controllers\RekapController::class, 'kas_per_vendor_detail'])->name('kas-per-vendor.detail');
        Route::get('pritn-kas-per-vendor/{vendor}/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'print_kas_per_vendor'])->name('print-kas-per-vendor.index');
        Route::get('perform-unit-pervendor', [App\Http\Controllers\StatistikController::class, 'perform_unit_pervendor'])->name('perform-unit-pervendor.index');
        Route::get('statistik-pervendor', [App\Http\Controllers\StatistikController::class, 'statistik_pervendor'])->name('statistik-pervendor.index');

        Route::prefix('per-vendor')->group(function(){
            Route::get('/upah-gendong', [App\Http\Controllers\PerVendorController::class, 'upah_gendong'])->name('per-vendor.upah-gendong');

            Route::prefix('maintenance-vehicle')->group(function(){
                Route::get('/', [App\Http\Controllers\PerVendorController::class, 'maintenance_vehicle'])->name('per-vendor.maintenance-vehicle');
                Route::get('/print', [App\Http\Controllers\PerVendorController::class, 'maintenance_vehicle_print'])->name('per-vendor.maintenance-vehicle.print');
                Route::post('/store-odo', [App\Http\Controllers\PerVendorController::class, 'store_odo'])->name('per-vendor.maintenance-vehicle.store-odo');
            });

        });

    });

    Route::group(['middleware' => 'role:customer'], function() {
        Route::prefix('per-customer')->group(function() {
            Route::get('nota-tagihan', [App\Http\Controllers\PerCustomerController::class, 'nota_tagihan'])->name('per-customer.nota-tagihan');
            Route::get('nota-tagihan/print', [App\Http\Controllers\PerCustomerController::class, 'nota_tagihan_print'])->name('per-customer.nota-tagihan.print');

            Route::get('invoice-tagihan', [App\Http\Controllers\PerCustomerController::class, 'invoice'])->name('per-customer.invoice-tagihan');
            Route::get('invoice-tagihan/{invoice}/detail', [App\Http\Controllers\PerCustomerController::class, 'invoice_detail'])->name('per-customer.invoice-tagihan.detail');
            Route::get('invoice-tagihan/{invoice}/export', [App\Http\Controllers\PerCustomerController::class, 'invoice_export'])->name('per-customer.invoice-tagihan.export');

            Route::get('nota-lunas', [App\Http\Controllers\PerCustomerController::class, 'nota_lunas'])->name('per-customer.nota-lunas');
            Route::get('nota-lunas/data', [App\Http\Controllers\PerCustomerController::class, 'nota_lunas_data'])->name('per-customer.nota-lunas.data');
            Route::get('nota-lunas/{invoice}/detail', [App\Http\Controllers\PerCustomerController::class, 'nota_lunas_detail'])->name('per-customer.nota-lunas.detail');

        });
    });

    Route::group(['middleware' => 'role:operasional'], function() {
        Route::prefix('operasional')->group(function() {
            // Route::get('kas-vendor', [App\Http\Controllers\OperasionalController::class, 'kas_vendor'])->name('operasional.kas-vendor');
            // Route::get('kas-vendor/{invoiceBayar}/detail', [App\Http\Controllers\OperasionalController::class, 'kas_vendor_detail'])->name('operasional.kas-vendor.detail');
            // Route::get('kas-vendor/preview/{bulan}/{tahun}', [App\Http\Controllers\OperasionalController::class, 'kas_vendor_print'])->name('operasional.kas-vendor.print');

            Route::get('perform-unit', [App\Http\Controllers\OperasionalController::class, 'perform_unit'])->name('operasional.perform-unit');
            Route::get('perform-unit/print', [App\Http\Controllers\OperasionalController::class, 'perform_unit_print'])->name('operasional.perform-unit.print');

            Route::get('upah-gendong', [App\Http\Controllers\OperasionalController::class, 'upah_gendong'])->name('operasional.upah-gendong');

            // Route::get('statistik-vendor', [App\Http\Controllers\OperasionalController::class, 'statistik_vendor'])->name('operasional.statistik-vendor');
        });
    });

});


