<?php

use App\Http\Controllers\AkiGantiController;
use App\Http\Controllers\AsistenUserController;
use App\Http\Controllers\BanController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BbmStoringController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ByPassVendorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DireksiController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\FilterOliGantiController;
use App\Http\Controllers\FilterOliReportController;
use App\Http\Controllers\FormBarangController;
use App\Http\Controllers\FormDevidenController;
use App\Http\Controllers\FormGajiController;
use App\Http\Controllers\FormKasBesarController;
use App\Http\Controllers\FormKasbonController;
use App\Http\Controllers\FormKasKecilController;
use App\Http\Controllers\FormKasUangJalanController;
use App\Http\Controllers\FormLainController;
use App\Http\Controllers\FormMaintenanceController;
use App\Http\Controllers\FormStoringConroller;
use App\Http\Controllers\FormVendorController;
use App\Http\Controllers\HistoriController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KategoriBarangController;
use App\Http\Controllers\KategoriFilterOliMesinController;
use App\Http\Controllers\KonfigurasiController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\LegalitasController;
use App\Http\Controllers\OperasionalController;
use App\Http\Controllers\PajakController;
use App\Http\Controllers\PasswordKonfirmasiController;
use App\Http\Controllers\PemegangSahamController;
use App\Http\Controllers\PerCustomerAdminController;
use App\Http\Controllers\PerCustomerController;
use App\Http\Controllers\PerInvestorController;
use App\Http\Controllers\PersentaseAwalController;
use App\Http\Controllers\PerVendorController;
use App\Http\Controllers\PerVendorOperationalController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\RuteController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\TemplateKontrakController;
use App\Http\Controllers\TemplateSpkController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WaController;
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
    return redirect()->route('login');
});

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);

Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::group(['middleware' => 'role:user'], function () {});

    Route::group(['middleware' => 'role:su'], function () {
        Route::prefix('bypass')->group(function () {
            Route::get('/', [ByPassVendorController::class, 'index'])->name('bypass.index');
            Route::get('/kas-direksi', [ByPassVendorController::class, 'kas_direksi'])->name('bypass-kas-direksi.index');
            Route::post('/kas-direksi', [ByPassVendorController::class, 'kas_direksi_store'])->name('bypass-kas-direksi.store');

            Route::get('/kas-besar', [ByPassVendorController::class, 'by_pass_kas_besar'])->name('bypass-kas-besar.index');
            Route::post('/kas-besar', [ByPassVendorController::class, 'by_pass_kas_besar_store'])->name('bypass-kas-besar.store');
        });

    });

    Route::resource('kontrak', KontrakController::class)->middleware('role:admin,user');
    Route::get('kontrak-doc/{kontrak}', [KontrakController::class, 'kontrak_doc'])->name('kontrak.doc')->middleware('role:admin,user');
    Route::post('kontrak/upload/{kontrak}', [KontrakController::class, 'upload'])->name('kontrak.upload')->middleware('role:admin,user');
    Route::get('kontrak/view/{kontrak}', [KontrakController::class, 'view_file'])->name('kontrak.view')->middleware('role:admin,user');
    Route::get('kontrak/hapus-file/{kontrak}', [KontrakController::class, 'delete_file'])->name('kontrak.hapus-file')->middleware('role:admin,user');

    Route::resource('spk', SpkController::class)->middleware('role:admin,user');
    Route::get('spk-doc/{spk}', [SpkController::class, 'spk_doc'])->name('spk.doc')->middleware('role:admin,user');
    Route::post('spk/upload/{spk}', [SpkController::class, 'upload'])->name('spk.upload')->middleware('role:admin,user');
    Route::get('spk/view/{spk}', [SpkController::class, 'view_file'])->name('spk.view')->middleware('role:admin,user');
    Route::get('spk/hapus-file/{spk}', [SpkController::class, 'delete_file'])->name('spk.hapus-file')->middleware('role:admin,user');

    Route::group(['middleware' => 'role:investor'], function () {
        Route::prefix('per-investor')->group(function () {
            Route::get('/kas-besar', [PerInvestorController::class, 'kas_besar'])->name('per-investor.kas-besar');
            Route::get('/tagihan-invoice', [PerInvestorController::class, 'tagihan_invoice'])->name('per-investor.tagihan-invoice');
            Route::get('/profit-harian', [PerInvestorController::class, 'profit_harian'])->name('per-investor.profit-harian');
            Route::get('/profit-bulanan', [PerInvestorController::class, 'profit_bulanan'])->name('per-investor.profit-bulanan');
        });
    });

    Route::group(['middleware' => 'role:admin,su,user'], function () {
        Route::prefix('kas-besar')->group(function () {
            Route::get('/masuk', [FormKasBesarController::class, 'masuk'])->name('kas-besar.masuk');
            Route::post('/masuk', [FormKasBesarController::class, 'masuk_store'])->name('kas-besar.masuk.store');
        });
    });

    // Routing untuk admin dan superuser
    Route::group(['middleware' => 'role:admin,su'], function () {
        Route::prefix('admin')->group(function () {
            Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
            Route::post('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
        });

        Route::prefix('kas-besar')->group(function () {
            Route::get('/keluar', [FormKasBesarController::class, 'keluar'])->name('kas-besar.keluar');
            Route::post('/keluar', [FormKasBesarController::class, 'keluar_store'])->name('kas-besar.keluar.store');
        });

        Route::prefix('billing')->group(function () {
            // Form Deviden
            Route::get('/deviden', [FormDevidenController::class, 'index'])->name('billing.deviden.index');
            Route::post('/deviden/store', [FormDevidenController::class, 'store'])->name('billing.deviden.store');

            // Form Bunga Investor
            Route::prefix('bunga-investor')->group(function () {
                Route::get('/', [BillingController::class, 'bunga_investor'])->name('billing.bunga-investor');
                Route::post('/store', [BillingController::class, 'bunga_investor_store'])->name('billing.bunga-investor.store');
            });

            Route::prefix('storing')->group(function () {
                Route::get('/index', [FormStoringConroller::class, 'index'])->name('billing.storing.index');
                Route::post('/store', [FormStoringConroller::class, 'store'])->name('billing.storing.store');
                Route::get('/void', [FormStoringConroller::class, 'void'])->name('billing.storing.void');
                Route::get('/get-storing', [FormStoringConroller::class, 'get_storing'])->name('billing.storing.get-storing');
                Route::get('/get-status-so', [FormStoringConroller::class, 'get_status_so'])->name('billing.storing.get-status-so');
                Route::get('/get-vendor', [FormStoringConroller::class, 'get_vendor'])->name('billing.storing.get-vendor');
                Route::get('/storing-latest', [FormStoringConroller::class, 'storing_latest'])->name('billing.storing.storing-latest');
            });

            // form vendor
            Route::prefix('vendor')->group(function () {
                Route::get('/titipan', [FormVendorController::class, 'titipan'])->name('billing.vendor.titipan');
                Route::post('/titipan-store', [FormVendorController::class, 'titipan_store'])->name('billing.vendor.titipan-store');
                Route::get('/pelunasan', [FormVendorController::class, 'pelunasan'])->name('billing.vendor.pelunasan');
                Route::post('/pelunasan-store', [FormVendorController::class, 'pelunasan_store'])->name('billing.vendor.pelunasan-store');
                Route::get('/get-kas-vendor', [FormVendorController::class, 'get_kas_vendor'])->name('billing.vendor.get-kas-vendor');
                Route::get('/bayar', [FormVendorController::class, 'bayar'])->name('billing.vendor.bayar');
                Route::post('/bayar-store', [FormVendorController::class, 'bayar_store'])->name('billing.vendor.bayar-store');
                Route::get('/get-vehicle', [FormVendorController::class, 'get_vehicle'])->name('billing.vendor.get-vehicle');
                Route::get('/get-plafon-titipan', [FormVendorController::class, 'get_plafon_titipan'])->name('billing.vendor.get-plafon-titipan');
            });

        });

        Route::prefix('form-setor-pph')->group(function () {
            Route::get('/masuk', [BillingController::class, 'form_setor_pph_masuk'])->name('form-setor-pph.masuk');
            Route::post('/masuk', [BillingController::class, 'form_setor_pph_masuk_store'])->name('form-setor-pph.masuk.store');
            Route::get('/keluar', [BillingController::class, 'form_setor_pph_keluar'])->name('form-setor-pph.keluar');
            Route::post('/keluar', [BillingController::class, 'form_setor_pph_keluar_store'])->name('form-setor-pph.keluar.store');
        });

        Route::get('/invoice-tagihan-back/{invoice}', [InvoiceController::class, 'invoice_tagihan_back'])->name('invoice.tagihan-back.execute');
        Route::post('/invoice-bayar-back/{invoice}', [InvoiceController::class, 'invoice_bayar_back'])->name('invoice.bayar-back.execute');
        Route::post('/invoice-csr-back/{invoice}', [InvoiceController::class, 'invoice_csr_back'])->name('invoice.csr-back.execute');
        Route::post('/invoice-bonus-back/{invoice}', [InvoiceController::class, 'invoice_bonus_back'])->name('invoice.bonus-back.execute');

        Route::get('/bypass-kas-vendor', [ByPassVendorController::class, 'kas_vendor'])->name('bypass-kas-vendor.index');
        Route::post('/bypass-kas-vendor', [ByPassVendorController::class, 'kas_vendor_store'])->name('bypass-kas-vendor.store');

        Route::post('/statistik/ban-luar/store', [BanController::class, 'log_store'])->name('statistik.ban-luar.store');

        Route::prefix('dokumen')->group(function () {
            Route::get('/', [DokumenController::class, 'index'])->name('dokumen');

            Route::prefix('mutasi-rekening')->group(function () {
                Route::get('/', [DokumenController::class, 'mutasi_rekening'])->name('dokumen.mutasi-rekening');
                Route::post('/store', [DokumenController::class, 'mutasi_rekening_store'])->name('dokumen.mutasi-rekening.store');
                Route::delete('/destroy/{mutasi}', [DokumenController::class, 'mutasi_rekening_destroy'])->name('dokumen.mutasi-rekening.destroy');
                Route::post('/kirim-wa/{mutasi}', [DokumenController::class, 'kirim_wa'])->name('dokumen.mutasi-rekening.kirim-wa');
            });

            Route::prefix('kontrak-tambang')->group(function () {
                Route::get('/', [DokumenController::class, 'kontrak_tambang'])->name('dokumen.kontrak-tambang');
                Route::post('/store', [DokumenController::class, 'kontrak_tambang_store'])->name('dokumen.kontrak-tambang.store');
                Route::delete('/destroy/{kontrak_tambang}', [DokumenController::class, 'kontrak_tambang_destroy'])->name('dokumen.kontrak-tambang.destroy');
                Route::post('/kirim-wa/{kontrak_tambang}', [DokumenController::class, 'kirim_wa_tambang'])->name('dokumen.kontrak-tambang.kirim-wa');
            });

            Route::prefix('kontrak-vendor')->group(function () {
                Route::get('/', [DokumenController::class, 'kontrak_vendor'])->name('dokumen.kontrak-vendor');
                Route::post('/store', [DokumenController::class, 'kontrak_vendor_store'])->name('dokumen.kontrak-vendor.store');
                Route::delete('/destroy/{kontrak_vendor}', [DokumenController::class, 'kontrak_vendor_destroy'])->name('dokumen.kontrak-vendor.destroy');
                Route::post('/kirim-wa/{kontrak_vendor}', [DokumenController::class, 'kirim_wa_vendor'])->name('dokumen.kontrak-vendor.kirim-wa');
            });

            Route::prefix('sph')->group(function () {
                Route::get('/', [DokumenController::class, 'sph'])->name('dokumen.sph');
                Route::post('/store', [DokumenController::class, 'sph_store'])->name('dokumen.sph.store');
                Route::delete('/destroy/{sph}', [DokumenController::class, 'sph_destroy'])->name('dokumen.sph.destroy');
                Route::post('/kirim-wa/{sph}', [DokumenController::class, 'kirim_wa_sph'])->name('dokumen.sph.kirim-wa');
            });
        });

        Route::prefix('company-profile')->group(function () {
            Route::get('/', [DokumenController::class, 'company_profile'])->name('company-profile');
            Route::post('/store', [DokumenController::class, 'company_profile_store'])->name('company-profile.store');
            Route::delete('/destroy/{company_profile}', [DokumenController::class, 'company_profile_destroy'])->name('company-profile.destroy');
            Route::post('/kirim-wa/{company_profile}', [DokumenController::class, 'kirim_wa_cp'])->name('company-profile.kirim-wa');
        });

        Route::prefix('database')->group(function () {
            Route::get('/kategori-filter-oli-mesin', [KategoriFilterOliMesinController::class, 'index'])->name('database.kategori-filter-oli-mesin.index');
            Route::post('/kategori-filter-oli-mesin', [KategoriFilterOliMesinController::class, 'store'])->name('database.kategori-filter-oli-mesin.store');
            Route::get('/kategori-filter-oli-mesin/{kategori}/edit', [KategoriFilterOliMesinController::class, 'edit'])->name('database.kategori-filter-oli-mesin.edit');
            Route::patch('/kategori-filter-oli-mesin/{kategori}', [KategoriFilterOliMesinController::class, 'update'])->name('database.kategori-filter-oli-mesin.update');
            Route::get('/', [DatabaseController::class, 'index'])->name('database');

            Route::prefix('driver')->group(function () {
                Route::get('/', [DatabaseController::class, 'driver'])->name('database.driver');
                Route::post('/store', [DatabaseController::class, 'driver_store'])->name('database.driver.store');
                Route::get('/{id}/edit', [DatabaseController::class, 'driver_edit'])->name('database.driver.edit');
                Route::patch('/update/{id}', [DatabaseController::class, 'driver_update'])->name('database.driver.update');
                Route::delete('/destroy/{driver}', [DatabaseController::class, 'driver_destroy'])->name('database.driver.destroy');
            });

            Route::get('/customer/preview-customer', [CustomerController::class, 'preview_customer'])->name('database.customer.preview-customer');
            Route::post('/kategori-barang-store', [KategoriBarangController::class, 'kategori_store'])->name('database.kategori-barang-store');
            Route::delete('/kategori-barang-destroy/{kategori}', [KategoriBarangController::class, 'kategori_destroy'])->name('database.kategori-barang-destroy');
            Route::patch('/kategori-barang-update/{kategori}', [KategoriBarangController::class, 'kategori_update'])->name('database.kategori-barang-update');

            Route::prefix('aktivasi-maintenance')->group(function () {
                Route::get('/', [DatabaseController::class, 'aktivasi_maintenance'])->name('database.aktivasi-maintenance');
                Route::post('/store', [DatabaseController::class, 'aktivasi_maintenance_store'])->name('database.aktivasi-maintenance.store');
                Route::patch('/update/{am}', [DatabaseController::class, 'aktivasi_maintenance_update'])->name('database.aktivasi-maintenance.update');
                Route::delete('/destroy/{am}', [DatabaseController::class, 'aktivasi_maintenance_destroy'])->name('database.aktivasi-maintenance.destroy');
            });
            Route::prefix('barang-maintenance')->group(function () {
                Route::get('/', [DatabaseController::class, 'barang_maintenance'])->name('database.barang-maintenance');
                Route::post('/store', [DatabaseController::class, 'barang_maintenance_store'])->name('database.barang-maintenance.store');
                Route::patch('/update/{bm}', [DatabaseController::class, 'barang_maintenance_update'])->name('database.barang-maintenance.update');
                Route::delete('/destroy/{bm}', [DatabaseController::class, 'barang_maintenance_destroy'])->name('database.barang-maintenance.destroy');

                Route::post('/store-kategori', [DatabaseController::class, 'kategori_store'])->name('database.barang-maintenance.kategori.store');
                Route::patch('/update-kategori/{kategori}', [DatabaseController::class, 'kategori_update'])->name('database.barang-maintenance.kategori.update');
                Route::delete('/destroy-kategori/{kategori}', [DatabaseController::class, 'kategori_destroy'])->name('database.barang-maintenance.kategori.destroy');
            });

            Route::prefix('upah-gendong')->group(function () {
                Route::get('/', [DatabaseController::class, 'upah_gendong'])->name('database.upah-gendong');
                Route::post('/store', [DatabaseController::class, 'upah_gendong_store'])->name('database.upah-gendong.store');
                Route::patch('/update/{ug}', [DatabaseController::class, 'upah_gendong_update'])->name('database.upah-gendong.update');
                Route::delete('/destroy/{ug}', [DatabaseController::class, 'upah_gendong_destroy'])->name('database.upah-gendong.destroy');
            });

            Route::prefix('cost-operational')->group(function () {
                Route::get('/', [DatabaseController::class, 'cost_operational'])->name('database.cost-operational');
                Route::post('/store', [DatabaseController::class, 'cost_operational_store'])->name('database.cost-operational.store');
                Route::patch('/update/{cost}', [DatabaseController::class, 'cost_operational_update'])->name('database.cost-operational.update');
                Route::delete('/destroy/{cost}', [DatabaseController::class, 'cost_operational_delete'])->name('database.cost-operational.delete');
            });

            Route::prefix('kreditor')->group(function () {
                Route::get('/', [DatabaseController::class, 'kreditor'])->name('database.kreditor');
                Route::post('/store', [DatabaseController::class, 'kreditor_store'])->name('database.kreditor.store');
                Route::patch('/update/{kreditor}', [DatabaseController::class, 'kreditor_update'])->name('database.kreditor.update');
                Route::delete('/destroy/{kreditor}', [DatabaseController::class, 'kreditor_destroy'])->name('database.kreditor.destroy');
            });

            Route::post('/persentase-awal-store', [PersentaseAwalController::class, 'store'])->name('database.persentase-awal-store');
            Route::patch('/persentase-awal-update/{awal}', [PersentaseAwalController::class, 'update'])->name('database.persentase-awal-update');
            Route::delete('/persentase-awal-destroy/{awal}', [PersentaseAwalController::class, 'destroy'])->name('database.persentase-awal-destroy');
        });

        Route::resource('vendor', VendorController::class);
        Route::get('/vendor/{id}/pembayaran', [VendorController::class, 'pembayaran'])->name('vendor.pembayaran');
        Route::patch('/vendor/{id}/toggle-limit-tonase', [VendorController::class, 'toggleLimitTonase'])->name('vendor.toggle-limit-tonase');
        Route::post('/vendor/pembayaran', [VendorController::class, 'pembayaran_store'])->name('vendor.pembayaran.store');
        Route::get('/vendor/pembayaran/{id}/edit', [VendorController::class, 'pembayaran_edit'])->name('vendor.pembayaran.edit');
        Route::post('/vendor/pembayaran/{id}/update', [VendorController::class, 'pembayaran_update'])->name('vendor.pembayaran.update');

        Route::get('/vendor/{id}/uang-jalan', [VendorController::class, 'uang_jalan'])->name('uj.vendor.uang-jalan');
        Route::post('/vendor/uang-jalan', [VendorController::class, 'uang_jalan_store'])->name('uj.vendor.uang-jalan.store');
        Route::get('uj/vendor/uang-jalan/{vendor}/edit', [VendorController::class, 'uang_jalan_edit'])->name('uj.vendor.uang-jalan.edit');
        Route::post('/vendor/uang-jalan/{id}/update', [VendorController::class, 'uang_jalan_update'])->name('uj.vendor.uang-jalan.update');
        Route::get('/preview-vendor', [VendorController::class, 'preview_vendor'])->name('uj.vendor.preview-vendor');

        Route::get('/vendor/biodata-vendor/{id}', [VendorController::class, 'biodata_vendor'])->name('uj.vendor.biodata-vendor');

        Route::resource('rute', RuteController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);

        Route::resource('pemegang-saham', PemegangSahamController::class);

        Route::view('pengaturan', 'pengaturan.index')->name('pengaturan');
        Route::post('password-konfirmasi', [PasswordKonfirmasiController::class, 'store'])->name('password-konfirmasi.store');

        Route::prefix('pengaturan')->group(function () {
            Route::get('/wa', [WaController::class, 'wa'])->name('pengaturan.wa');
            Route::get('/wa/edit/{id}', [WaController::class, 'edit'])->name('pengaturan.wa.edit');
            Route::patch('/wa/update/{id}', [WaController::class, 'update'])->name('pengaturan.wa.update');
            Route::get('/nota-transaksi', [KonfigurasiController::class, 'index'])->name('pengaturan.nota-transaksi');
            Route::patch('/nota-transaksi/update/{konfigurasi}', [KonfigurasiController::class, 'update'])->name('pengaturan.konfigurasi-transaksi.update');
            Route::patch('/batasan/update/{id}', [KonfigurasiController::class, 'update_batasan'])->name('pengaturan.batasan.update');
            Route::patch('/nota-transaksi/update-jam/{konfigurasi}', [KonfigurasiController::class, 'update_jam'])->name('pengaturan.konfigurasi-transaksi.update-jam');

            Route::get('/histori-pesan', [HistoriController::class, 'index'])->name('pengaturan.histori-pesan');
            Route::post('/histori-pesan/resend/{pesanWa}', [HistoriController::class, 'resend'])->name('pengaturan.histori.resend');
            Route::delete('/histori-pesan/delete-sended', [HistoriController::class, 'delete_sended'])->name('pengaturan.histori.delete-sended');

            Route::get('/rekening-pajak', [SettingController::class, 'rekening_pajak'])->name('pengaturan.rekening-pajak');
            Route::post('/rekening-pajak', [SettingController::class, 'rekening_pajak_store'])->name('pengaturan.rekening-pajak.store');
        });

        Route::resource('direksi', DireksiController::class);

        Route::resource('karyawan', KaryawanController::class);
        Route::post('karyawan/jabatan-store', [KaryawanController::class, 'jabatan_store'])->name('karyawan.jabatan-store');
        Route::patch('karyawan/jabatan-update/{jabatan}', [KaryawanController::class, 'jabatan_update'])->name('karyawan.jabatan-update');
        Route::delete('karyawan/jabatan-delete/{jabatan}', [KaryawanController::class, 'jabatan_destroy'])->name('karyawan.jabatan-destroy');

        Route::resource('customer', CustomerController::class);

        Route::prefix('customer')->group(function () {

            Route::post('/{customer}/document-store', [CustomerController::class, 'document_store'])->name('customer.document-store');
            Route::delete('/document-delete/{document}', [CustomerController::class, 'document_destroy'])->name('customer.document-destroy');
            Route::get('/document-download/{document}', [CustomerController::class, 'document_download'])->name('customer.document-download');

            Route::get('/{customer}/tagihan', [CustomerController::class, 'tagihan'])->name('customer.tagihan');
            Route::post('/{customer}/tagihan-store', [CustomerController::class, 'tagihan_store'])->name('customer.tagihan-store');
            Route::get('/{customer}/tagihan-edit', [CustomerController::class, 'tagihan_edit'])->name('customer.tagihan-edit');
            Route::patch('/{customer}/tagihan-update', [CustomerController::class, 'tagihan_update'])->name('customer.tagihan-update');
            Route::post('/{customer}/ubah-status', [CustomerController::class, 'ubah_status'])->name('customer.ubah-status');
        });

        Route::resource('pengguna', UserController::class);

        Route::resource('sponsor', SponsorController::class)->only([
            'index', 'store', 'update', 'destroy',
        ]);

        Route::resource('kategori-barang', KategoriBarangController::class);

        Route::resource('barang', BarangController::class)->only([
            'store', 'update', 'destroy',
        ]);

        Route::resource('vehicle', VehicleController::class);
        // Rute untuk memanggil modal form rekening via AJAX
        Route::get('vehicle/{vehicle}/edit-rekening', [VehicleController::class, 'editRekening'])->name('vehicle.edit-rekening');

        // Rute untuk memproses update data rekening
        Route::patch('vehicle/{vehicle}/update-rekening', [VehicleController::class, 'updateRekening'])->name('vehicle.update-rekening');

        Route::get('print-preview-vehicle', [VehicleController::class, 'print_preview_vehicle'])->name('print-preview-vehicle');

        Route::view('template', 'dokumen.template.index')->name('template');
        Route::resource('template-spk', TemplateSpkController::class);
        Route::get('spk-template/preview', [TemplateSpkController::class, 'preview'])->name('spk-template.preview');

        Route::resource('template-kontrak', TemplateKontrakController::class);

        Route::get('kontrak-template/preview', [TemplateKontrakController::class, 'preview'])->name('kontrak-template.preview');

        Route::resource('rekening', RekeningController::class)->only([
            'index', 'edit', 'update',
        ]);

        Route::prefix('form-lain-lain')->group(function () {
            Route::get('/masuk', [FormLainController::class, 'masuk'])->name('form-lain-lain.masuk');
            Route::post('/masuk', [FormLainController::class, 'masuk_store'])->name('form-lain-lain.masuk.store');
            Route::get('/keluar', [FormLainController::class, 'keluar'])->name('form-lain-lain.keluar');
            Route::post('/keluar', [FormLainController::class, 'keluar_store'])->name('form-lain-lain.keluar.store');
        });

        Route::prefix('statistik')->group(function () {
            Route::prefix('profit-harian')->group(function () {
                Route::get('/', [StatistikController::class, 'profit_harian'])->name('statistik.profit-harian');
                Route::get('/pdf', [StatistikController::class, 'profit_harian_download'])->name('statistik.profit-harian.pdf');
            });

            Route::get('/achievement', [StatistikController::class, 'achievement'])->name('statistik.achievement');

            Route::get('/profit-tahunan-bersih', [StatistikController::class, 'profit_tahunan_bersih'])->name('statistik.profit-tahunan-bersih');
            Route::get('/profit-tahunan-bersih/{jenis}/{month}/{year}', [StatistikController::class, 'profit_tahunan_bersih_detail_jenis'])->name('statistik.profit-tahunan-bersih.detail-jenis');

            Route::prefix('profit')->group(function () {
                Route::get('/tahunan-bersih', [StatistikController::class, 'tahunan_bersih'])->name('statistik.profit.tahunan-bersih');
                Route::get('/tahunan-bersih/pdf', [StatistikController::class, 'tahunan_bersih_download'])->name('statistik.profit.tahunan-bersih.pdf');
            });

            Route::get('/profit-bulanan', [StatistikController::class, 'profit_bulanan'])->name('statisik.profit-bulanan');
            Route::get('/profit-bulanan/print', [StatistikController::class, 'profit_bulanan_print'])->name('statistik.profit-bulanan.print');
            Route::get('/profit-tahunan', [StatistikController::class, 'profit_tahunan'])->name('statistik.profit-tahunan');
            Route::get('/profit-tahunan/print', [StatistikController::class, 'profit_tahunan_print'])->name('statistik.profit-tahunan.print');

            Route::get('/perform-vendor/print', [StatistikController::class, 'perform_vendor_print'])->name('statistik.perform-vendor.print');

            Route::prefix('tonase-tambang')->group(function () {
                Route::get('/{customer}', [StatistikController::class, 'tonase_tambang'])->name('statistik.tonase-tambang');
                Route::get('/{customer}/pdf', [StatistikController::class, 'tonase_tambang_download'])->name('statistik.tonase-tambang.pdf');
            });
        });

        Route::prefix('billing')->group(function () {

            Route::prefix('form-achievement')->group(function () {
                Route::prefix('masuk')->group(function () {
                    Route::get('/', [BillingController::class, 'form_achievement_masuk'])->name('billing.form-achievement.masuk');
                    Route::post('/', [BillingController::class, 'form_achievement_masuk_store'])->name('billing.form-achievement.masuk.store');
                });

                Route::prefix('keluar')->group(function () {
                    Route::get('/', [BillingController::class, 'form_achievement_keluar'])->name('billing.form-achievement.keluar');
                    Route::post('/', [BillingController::class, 'form_achievement_keluar_store'])->name('billing.form-achievement.keluar.store');
                });
            });
        });

        Route::prefix('legalitas')->group(function () {

            Route::prefix('kategori')->group(function () {
                Route::post('/store', [LegalitasController::class, 'kategori_store'])->name('legalitas.kategori-store');
                Route::patch('/update/{id}', [LegalitasController::class, 'kategori_update'])->name('legalitas.kategori-update');
                Route::delete('/destroy/{id}', [LegalitasController::class, 'kategori_destroy'])->name('legalitas.kategori-destroy');
            });

            Route::get('/', [LegalitasController::class, 'index'])->name('legalitas');
            Route::post('/store', [LegalitasController::class, 'store'])->name('legalitas.store');
            Route::patch('/update/{legalitas}', [LegalitasController::class, 'update'])->name('legalitas.update');
            Route::delete('/destroy/{legalitas}', [LegalitasController::class, 'destroy'])->name('legalitas.destroy');

            Route::post('/kirim-wa/{legalitas}', [LegalitasController::class, 'kirim_wa'])->name('legalitas.kirim-wa');

        });

    });

    Route::get('billing', [BillingController::class, 'index'])->name('billing.index')->middleware('role:admin,user,su');

    // Routing bersama asisten user
    Route::group(['middleware' => 'role:admin,user,su,asisten-user'], function () {
        Route::prefix('kas-uang-jalan')->group(function () {
            Route::get('/masuk', [FormKasUangJalanController::class, 'masuk'])->name('kas-uang-jalan.masuk');
            Route::post('/masuk', [FormKasUangJalanController::class, 'masuk_store'])->name('kas-uang-jalan.masuk.store');
            Route::get('/keluar', [FormKasUangJalanController::class, 'keluar'])->name('kas-uang-jalan.keluar');
            Route::post('/keluar', [FormKasUangJalanController::class, 'keluar_store'])->name('kas-uang-jalan.keluar.store');
            Route::get('/get-vendor', [FormKasUangJalanController::class, 'get_vendor'])->name('kas-uang-jalan.get-vendor');
            Route::get('/get-rute', [FormKasUangJalanController::class, 'get_rute'])->name('kas-uang-jalan.get-rute');
            Route::get('/get-uang-jalan', [FormKasUangJalanController::class, 'get_uang_jalan'])->name('kas-uang-jalan.get-uang-jalan');

            Route::prefix('pengembalian')->group(function () {
                Route::get('/', [FormKasUangJalanController::class, 'pengembalian'])->name('kas-uang-jalan.pengembalian');
                Route::post('/store', [FormKasUangJalanController::class, 'pengembalian_store'])->name('kas-uang-jalan.pengembalian.store');
            });

            Route::prefix('penyesuaian')->group(function () {
                Route::get('/', [FormKasUangJalanController::class, 'penyesuaian'])->name('kas-uang-jalan.penyesuaian');
                Route::post('/store', [FormKasUangJalanController::class, 'penyesuaian_store'])->name('kas-uang-jalan.penyesuaian.store');
            });
        });

        Route::prefix('transaksi')->group(function () {
            Route::get('/nota-muat', [TransaksiController::class, 'nota_muat'])->name('transaksi.nota-muat');
            Route::patch('/nota-muat/update/{transaksi}', [TransaksiController::class, 'nota_muat_update'])->name('transaksi.nota-muat.update');
            Route::get('/nota-bongkar', [TransaksiController::class, 'nota_bongkar'])->name('transaksi.nota-bongkar');
            Route::patch('/nota-bongkar/update/{transaksi}', [TransaksiController::class, 'nota_bongkar_update'])->name('transaksi.nota-bongkar.update');

            Route::prefix('nota-tagihan')->group(function () {
                Route::get('/{customer}', [TransaksiController::class, 'nota_tagihan'])->name('transaksi.nota-tagihan');
                Route::get('/{customer}/export', [TransaksiController::class, 'tagihan_export'])->name('transaksi.nota-tagihan.export');
                Route::get('/{transaksi}/check', [TransaksiController::class, 'nota_tagihan_checked'])->name('transaksi.nota-tagihan.check');
                Route::post('/{transaksi}/uncheck', [TransaksiController::class, 'nota_tagihan_unchecked'])->name('transaksi.nota-tagihan.uncheck');
                Route::post('/edit/{transaksi}', [TransaksiController::class, 'nota_tagihan_edit'])->name('transaksi.nota-tagihan.edit');
                Route::post('/{transaksi}/update', [TransaksiController::class, 'nota_tagihan_update'])->name('transaksi.nota-tagihan.update');

                Route::prefix('keranjang')->group(function () {
                    Route::get('/{customer}', [TransaksiController::class, 'keranjang_tagihan'])->name('transaksi.nota-tagihan.keranjang');
                    Route::post('/{customer}/lanjut', [TransaksiController::class, 'keranjang_tagihan_lanjut'])->name('transaksi.nota-tagihan.keranjang.lanjut');
                    Route::get('/{customer}/export', [TransaksiController::class, 'keranjang_tagihan_export'])->name('transaksi.nota-tagihan.keranjang.export');
                    Route::post('/{customer}/{transaksi}/delete', [TransaksiController::class, 'keranjang_tagihan_delete'])->name('transaksi.nota-tagihan.keranjang.delete');
                    Route::get('/{customer}/{invoiceAdditional}/detail', [TransaksiController::class, 'keranjang_tagihan_detail'])->name('transaksi.nota-tagihan.keranjang.detail-jenis');
                    Route::post('/{customer}/{invoiceAdditional}/back', [TransaksiController::class, 'keranjang_tagihan_detail_back'])->name('transaksi.nota-tagihan.keranjang.detail-jenis.back');
                });

            });

        });

        Route::prefix('billing/nota-tagihan')->group(function () {
            Route::get('/{customer}', [BillingController::class, 'nota_tagihan'])->name('billing.nota-tagihan');
            Route::get('/{customer}/{jenis}', [BillingController::class, 'nota_tagihan_detail_by_jenis'])->name('billing.nota-tagihan.detail-jenis');
            Route::post('{customer}/{jenis}', [BillingController::class, 'nota_tagihan_detail_by_jenis_lanjut'])->name('billing.nota-tagihan.detail-jenis.lanjut');
            Route::get('/{customer}/{jenis}/keranjang', [BillingController::class, 'nota_tagihan_detail_by_jenis_keranjang'])->name('billing.nota-tagihan.detail-jenis.keranjang');
            Route::post('{customer}/{jenis}/keranjang/{invoice}/lanjut', [BillingController::class, 'nota_tagihan_detail_by_jenis_keranjang_lanjut'])->name('billing.nota-tagihan.detail-jenis.keranjang.lanjut');
            Route::post('{customer}/{jenis}/keranjang/{invoice}/back', [BillingController::class, 'nota_tagihan_detail_by_jenis_keranjang_back'])->name('billing.nota-tagihan.detail-jenis.keranjang.back');
        });

    });

    Route::group(['middleware' => 'role:admin,user,su,asisten-user,vendor,operasional,vendor-operational'], function () {
        Route::prefix('statistik/perform-unit')->group(function () {
            Route::get('/', [StatistikController::class, 'perform_unit'])->name('statistik.perform-unit');
            Route::get('/print', [StatistikController::class, 'perform_unit_print'])->name('statistik.perform-unit.print');
        });

        Route::prefix('statistik/ban-luar')->group(function () {
            Route::get('/', [BanController::class, 'index'])->name('statistik.ban-luar');
            Route::get('/transaksi-ritase/{banLogId}', [BanController::class, 'get_transaksi_ritase'])->name('statistik.ban-luar.transaksi-ritase');
            Route::get('/{vehicle}/{posisi}/histori', [BanController::class, 'histori'])->name('statistik.ban-luar.histori');
            Route::get('/histori-data', [BanController::class, 'histori_data'])->name('statistik.ban-luar.histori-data');
            Route::post('/histori-destroy/{histori}', [BanController::class, 'histori_delete'])->name('statistik.ban-luar.histori-destroy');
            Route::patch('/histori-update/{histori}', [BanController::class, 'histori_update'])->name('statistik.ban-luar.histori-update');
        });

        Route::get('statistik/filter-oli/transaksi-ritase/{log}', [FilterOliReportController::class, 'transactions'])->name('statistik.filter-oli.transaksi-ritase');
        Route::patch('statistik/filter-oli/histori/{log}', [FilterOliReportController::class, 'updateHistory'])->middleware('role:su,admin')->name('statistik.filter-oli.histori.update');
        Route::delete('statistik/filter-oli/histori/{log}', [FilterOliReportController::class, 'deleteHistory'])->middleware('role:su,admin')->name('statistik.filter-oli.histori.destroy');
        Route::get('statistik/filter-oli', [FilterOliReportController::class, 'statistics'])->name('statistik.filter-oli');
        Route::get('statistik/filter-oli/{vehicle}/{category}/histori', [FilterOliReportController::class, 'history'])->name('statistik.filter-oli.histori');

        Route::prefix('statistik/aki')->group(function () {
            Route::get('/', [StatistikController::class, 'aki_log'])->name('statistik.aki');
            Route::get('/histori-data', [StatistikController::class, 'aki_histori_data'])->name('statistik.aki.histori-data');
            Route::get('/{vehicle}/{posisi}/histori', [StatistikController::class, 'aki_histori'])->name('statistik.aki.histori');
            Route::post('/histori-destroy/{histori}', [StatistikController::class, 'aki_histori_delete'])->name('statistik.aki.histori-destroy');
            Route::patch('/histori-update/{histori}', [StatistikController::class, 'aki_histori_update'])->name('statistik.aki.histori-update');
        });
    });

    Route::group(['middleware' => 'role:admin,user,su,asisten-user,vendor'], function () {
        Route::get('transaksi/nota-bayar/{vendor}', [TransaksiController::class, 'nota_bayar'])->name('transaksi.nota-bayar');

        Route::get('/transaksi/nota-bayar/{vendor}/keranjang', [TransaksiController::class, 'nota_bayar_keranjang'])->name('transaksi.nota-bayar.keranjang');
        Route::post('/transaksi/nota-bayar/{vendor}/masuk-keranjang', [TransaksiController::class, 'nota_bayar_masuk_keranjang'])->name('transaksi.nota-bayar.masuk-keranjang');
        Route::post('/transaksi/nota-bayar/{vendor}/keluar-keranjang', [TransaksiController::class, 'nota_bayar_keluar_keranjang'])->name('transaksi.nota-bayar.keluar-keranjang');
        Route::post('/transaksi/nota-bayar/{vendor}/keranjang-semua', [TransaksiController::class, 'nota_bayar_keranjang_semua'])->name('transaksi.nota-bayar.keranjang-semua');
        Route::post('/transaksi/nota-bayar/{vendor}/kosongkan-keranjang', [TransaksiController::class, 'nota_bayar_kosongkan_keranjang'])->name('transaksi.nota-bayar.kosongkan-keranjang');

        Route::post('transaksi/nota-bayar/{vendor}/lanjut', [TransaksiController::class, 'nota_bayar_lanjut'])->name('transaksi.nota-bayar.lanjut');

        Route::prefix('billing/nota-bayar')->group(function () {
            Route::get('/{vendor}', [BillingController::class, 'nota_bayar'])->name('billing.nota-bayar');
            Route::get('/{vendor}/{jenis}', [BillingController::class, 'nota_bayar_detail_jenis'])->name('billing.nota-bayar.detail-jenis');
            Route::post('{vendor}/{jenis}', [BillingController::class, 'nota_bayar_detail_by_jenis_lanjut'])->name('billing.nota-bayar.detail-jenis.lanjut');
            Route::get('/{vendor}/{jenis}/keranjang', [BillingController::class, 'nota_bayar_detail_by_jenis_keranjang'])->name('billing.nota-bayar.detail-jenis.keranjang');
            Route::post('{vendor}/{jenis}/keranjang/{invoice}/lanjut', [BillingController::class, 'nota_bayar_detail_by_jenis_keranjang_lanjut'])->name('billing.nota-bayar.detail-jenis.keranjang.lanjut');
            Route::post('{vendor}/{jenis}/keranjang/{invoice}/back', [BillingController::class, 'nota_bayar_detail_by_jenis_keranjang_back'])->name('billing.nota-bayar.detail-jenis.keranjang.back');
        });
    });

    Route::group(['middleware' => 'role:admin,user,su'], function () {

        Route::prefix('pajak')->group(function () {

            Route::get('/', [PajakController::class, 'index'])->name('pajak.index');
            Route::prefix('rekap-ppn')->group(function () {
                Route::get('/', [PajakController::class, 'rekap_ppn'])->name('pajak.rekap-ppn');
                Route::get('/masukan/{rekapPpn}', [PajakController::class, 'rekap_ppn_masukan_detail'])->name('pajak.rekap-ppn.masukan');
                Route::get('/keluaran/{rekapPpn}', [PajakController::class, 'rekap_ppn_keluaran_detail'])->name('pajak.rekap-ppn.keluaran');
            });
            // Route::get('/rekap-ppn', [App\Http\Controllers\PajakController::class, 'rekap_ppn'])->name('pajak.rekap-ppn');

            Route::prefix('ppn-expired')->group(function () {
                Route::get('/', [PajakController::class, 'ppn_expired'])->name('pajak.ppn-expired');
                Route::post('/back/{ppnKeluaran}', [PajakController::class, 'ppn_expired_back'])->name('pajak.ppn-expired.back');
            });

            Route::prefix('ppn-masukan')->group(function () {
                Route::get('/', [PajakController::class, 'ppn_masukan'])->name('pajak.ppn-masukan');
                Route::patch('/store-faktur/{ppnMasukan}', [PajakController::class, 'ppn_masukan_store_faktur'])->name('pajak.ppn-masukan.store-faktur');
                Route::post('/keranjang-store', [PajakController::class, 'ppn_masukan_keranjang_store'])->name('pajak.ppn-masukan.keranjang-store');
                Route::post('/keranjang-destroy/{ppnMasukan}', [PajakController::class, 'ppn_masukan_keranjang_destroy'])->name('pajak.ppn-masukan.keranjang-destroy');
                Route::post('/keranjang-lanjut', [PajakController::class, 'ppn_masukan_keranjang_lanjut'])->name('pajak.ppn-masukan.keranjang-lanjut');
            });

            Route::prefix('ppn-keluaran')->group(function () {
                Route::get('/', [PajakController::class, 'ppn_keluaran'])->name('pajak.ppn-keluaran');
                Route::post('/expired/{ppnKeluaran}', [PajakController::class, 'ppn_keluaran_expired'])->name('pajak.ppn-keluaran.expired');
                Route::patch('/store-faktur/{ppnKeluaran}', [PajakController::class, 'ppn_keluaran_store_faktur'])->name('pajak.ppn-keluaran.store-faktur');
                Route::get('/keranjang', [PajakController::class, 'ppn_keluaran_keranjang'])->name('pajak.ppn-keluaran.keranjang');
                Route::post('/keranjang-store', [PajakController::class, 'ppn_keluaran_keranjang_store'])->name('pajak.ppn-keluaran.keranjang-store');
                Route::post('/keranjang-destroy/{ppnKeluaran}', [PajakController::class, 'ppn_keluaran_keranjang_destroy'])->name('pajak.ppn-keluaran.keranjang-destroy');
                Route::post('/keranjang-lanjut', [PajakController::class, 'ppn_keluaran_keranjang_lanjut'])->name('pajak.ppn-keluaran.keranjang-lanjut');
            });

            Route::prefix('pph-vendor')->group(function () {
                Route::get('/', [PajakController::class, 'pph_vendor'])->name('pajak.pph-vendor');
                Route::patch('/store-faktur/{pphVendor}', [PajakController::class, 'pph_vendor_store_faktur'])->name('pajak.pph-vendor.store-faktur');
                Route::post('/keranjang-store', [PajakController::class, 'pph_vendor_keranjang_store'])->name('pajak.pph-vendor.keranjang-store');
                Route::post('/keranjang-destroy/{pphVendor}', [PajakController::class, 'pph_vendor_keranjang_destroy'])->name('pajak.pph-vendor.keranjang-destroy');
                Route::post('/keranjang-lanjut', [PajakController::class, 'pph_vendor_keranjang_lanjut'])->name('pajak.pph-vendor.keranjang-lanjut');
            });

            Route::prefix('rekap-pph-vendor')->group(function () {
                Route::get('/', [PajakController::class, 'rekap_pph_vendor'])->name('pajak.rekap-pph-vendor');
                Route::get('/detail/{rekapPphVendor}', [PajakController::class, 'rekap_pph_vendor_detail'])->name('pajak.rekap-pph-vendor.detail');
            });

        });

        Route::get('statisik', [StatistikController::class, 'index'])->name('statisik.index');

        // Route::resource('kas-besar', App\Http\Controllers\KasBesarController::class);

        Route::prefix('kas-kecil')->group(function () {
            Route::get('/masuk', [FormKasKecilController::class, 'masuk'])->name('kas-kecil.masuk');
            Route::post('/masuk', [FormKasKecilController::class, 'masuk_store'])->name('kas-kecil.masuk.store');
            Route::get('/keluar', [FormKasKecilController::class, 'keluar'])->name('kas-kecil.keluar');
            Route::post('/keluar', [FormKasKecilController::class, 'keluar_store'])->name('kas-kecil.keluar.store');
            Route::get('/void', [FormKasKecilController::class, 'void'])->name('kas-kecil.void');
            Route::post('/void', [FormKasKecilController::class, 'void_store'])->name('kas-kecil.void.store');
            Route::get('/get-void', [FormKasKecilController::class, 'get_void'])->name('kas-kecil.get-void');
        });

        // Form maintenance
        Route::prefix('billing')->group(function () {

            Route::prefix('uj-ditahan')->group(function () {
                Route::get('/', [BillingController::class, 'uj_ditahan'])->name('billing.uj-ditahan');
                Route::get('/{id}', [BillingController::class, 'uj_ditahan_show'])->name('billing.uj-ditahan.show');
                Route::post('/cairkan', [BillingController::class, 'uj_ditahan_cairkan'])->name('billing.uj-ditahan.cairkan');
                Route::post('/{id}/cutoff', [BillingController::class, 'uj_ditahan_cutoff'])->name('billing.uj-ditahan.cutoff');
            });

            Route::get('/notif-count', [BillingController::class, 'getNotifCount'])->name('billing.notif-count');

            Route::get('/nota-csr', [TransaksiController::class, 'nota_csr'])->name('billing.nota-csr');
            Route::post('/nota-csr/lanjut', [TransaksiController::class, 'nota_csr_lanjut'])->name('billing.nota-csr.lanjut');
            Route::get('/invoice-csr', [InvoiceController::class, 'invoice_csr'])->name('billing.invoice-csr');
            Route::get('/invoice-csr/{invoiceCsr}/detail', [InvoiceController::class, 'invoice_csr_detail'])->name('billing.invoice-csr.detail');
            Route::post('/invoice-csr/{invoiceCsr}/lunas', [InvoiceController::class, 'invoice_csr_lunas'])->name('invoice.csr.lunas');

            Route::prefix('form-maintenance')->group(function () {
                Route::prefix('filter-oli')->group(function () {
                    Route::get('/', [FilterOliGantiController::class, 'index'])->name('billing.form-maintenance.filter-oli');
                    Route::post('/cart/add', [FilterOliGantiController::class, 'add'])->name('billing.form-maintenance.filter-oli.cart.add');
                    Route::delete('/cart/delete/{id}', [FilterOliGantiController::class, 'delete'])->name('billing.form-maintenance.filter-oli.cart.delete');
                    Route::delete('/cart/clear', [FilterOliGantiController::class, 'clear'])->name('billing.form-maintenance.filter-oli.cart.clear');
                    Route::get('/confirm', [FilterOliGantiController::class, 'confirm'])->name('billing.form-maintenance.filter-oli.confirm');
                    Route::post('/checkout', [FilterOliGantiController::class, 'checkout'])->name('billing.form-maintenance.filter-oli.checkout');
                });

                Route::prefix('ban-luar')->group(function () {
                    Route::get('/', [BillingController::class, 'form_ganti_ban'])->name('billing.form-maintenance.ban-luar');
                    Route::get('/get-vehicle-info', [BillingController::class, 'form_ganti_ban_get_vehicle_info'])->name('billing.form-maintenance.ban-luar.get-vehicle-info');

                    // 2. Operasi Keranjang AJAX
                    Route::post('/cart/add', [BillingController::class, 'form_ganti_ban_cart_add'])->name('billing.form-maintenance.ban-luar.cart.add');
                    Route::delete('/cart/delete/{id}', [BillingController::class, 'form_ganti_ban_cart_delete'])->name('billing.form-maintenance.ban-luar.cart.delete');
                    Route::delete('/cart/clear/{vehicle_id}', [BillingController::class, 'form_ganti_ban_cart_clear'])->name('billing.form-maintenance.ban-luar.cart.clear');

                    // 3. Halaman Terpisah Konfirmasi Invoice
                    Route::get('/confirm', [BillingController::class, 'form_ganti_ban_confirm'])->name('billing.form-maintenance.ban-luar.confirm');

                    // 4. Final Checkout Invoice
                    Route::post('/checkout', [BillingController::class, 'form_ganti_ban_checkout'])->name('billing.form-maintenance.ban-luar.checkout');
                });

                Route::prefix('aki')->group(function () {
                    Route::get('/', [AkiGantiController::class, 'form_ganti_aki'])->name('billing.form-maintenance.aki');
                    Route::get('/get-vehicle-info', [AkiGantiController::class, 'form_ganti_aki_get_vehicle_info'])->name('billing.form-maintenance.aki.get-vehicle-info');

                    // 2. Operasi Keranjang AJAX
                    Route::post('/cart/add', [AkiGantiController::class, 'form_ganti_aki_cart_add'])->name('billing.form-maintenance.aki.cart.add');
                    Route::delete('/cart/delete/{id}', [AkiGantiController::class, 'form_ganti_aki_cart_delete'])->name('billing.form-maintenance.aki.cart.delete');
                    Route::delete('/cart/clear/{vehicle_id}', [AkiGantiController::class, 'form_ganti_aki_cart_clear'])->name('billing.form-maintenance.aki.cart.clear');

                    // 3. Halaman Terpisah Konfirmasi Invoice
                    Route::get('/confirm', [AkiGantiController::class, 'form_ganti_aki_confirm'])->name('billing.form-maintenance.aki.confirm');

                    // 4. Final Checkout Invoice
                    Route::post('/checkout', [AkiGantiController::class, 'form_ganti_aki_checkout'])->name('billing.form-maintenance.aki.checkout');
                });

                Route::get('/beli', [FormMaintenanceController::class, 'beli'])->name('billing.form-maintenance.beli');
                Route::post('/barang-store', [FormMaintenanceController::class, 'beli_store'])->name('billing.form-maintenance.barang-store');
                Route::post('/keranjang-store', [FormMaintenanceController::class, 'keranjang_store'])->name('billing.form-maintenance.keranjang-store');
                Route::delete('/keranjang-destroy/{keranjang}', [FormMaintenanceController::class, 'keranjang_destroy'])->name('billing.form-maintenance.keranjang-destroy');
                Route::get('/keranjang-empty', [FormMaintenanceController::class, 'keranjang_empty'])->name('billing.form-maintenance.keranjang-empty');

                Route::get('/get-harga-jual', [FormMaintenanceController::class, 'get_harga_jual'])->name('billing.form-maintenance.get-harga-jual');
                Route::get('/get-barang', [FormMaintenanceController::class, 'get_barang'])->name('billing.form-maintenance.get-barang');

                Route::get('/jual-vendor', [FormMaintenanceController::class, 'jual_vendor'])->name('billing.form-maintenance.jual-vendor');
                Route::post('/jual-vendor-store', [FormMaintenanceController::class, 'jual_vendor_store'])->name('billing.form-maintenance.jual-vendor-store');
                Route::get('/jual-umum', [FormMaintenanceController::class, 'jual_umum'])->name('billing.form-maintenance.jual-umum');
                Route::post('/jual-umum/store', [FormMaintenanceController::class, 'jual_umum_store'])->name('billing.form-maintenance.jual-umum.store');

            });

            Route::prefix('otorisasi-maintenance')->group(function () {
                Route::prefix('filter-oli')->middleware('role:admin,su')->group(function () {
                    Route::get('/', [FilterOliGantiController::class, 'authorization'])->name('billing.otorisasi-maintenance.filter-oli');
                    Route::get('/{id}', [FilterOliGantiController::class, 'show'])->name('billing.otorisasi-maintenance.filter-oli.show');
                    Route::patch('/detail/{id}', [FilterOliGantiController::class, 'updateDetail'])->name('billing.otorisasi-maintenance.filter-oli.detail.update');
                    Route::post('/{id}/approve', [FilterOliGantiController::class, 'approve'])->name('billing.otorisasi-maintenance.filter-oli.approve');
                    Route::post('/{id}/reject', [FilterOliGantiController::class, 'reject'])->name('billing.otorisasi-maintenance.filter-oli.reject');
                });

                Route::get('/', [BillingController::class, 'otorisasi_maintenance'])->name('billing.otorisasi-maintenance');

                Route::prefix('aki')->group(function () {
                    Route::get('/', [BillingController::class, 'otorisasi_maintenance_aki'])->name('billing.otorisasi-maintenance.aki');
                    Route::post('/{id}/approve', [BillingController::class, 'otorisasi_maintenance_aki_approve'])->name('billing.otorisasi-maintenance.aki.approve');
                    Route::post('/{id}/reject', [BillingController::class, 'otorisasi_maintenance_aki_reject'])->name('billing.otorisasi-maintenance.aki.reject');
                });

                Route::post('/ban-luar/{id}/approve', [BillingController::class, 'otorisasi_maintenance_ban_luar_approve'])->name('billing.otorisasi-maintenance.ban-luar.approve');
                Route::post('/ban-luar/{id}/reject', [BillingController::class, 'otorisasi_maintenance_ban_luar_reject'])->name('billing.otorisasi-maintenance.ban-luar.reject');

            });

            Route::prefix('form-barang')->group(function () {
                Route::get('/beli', [FormBarangController::class, 'beli'])->name('billing.form-barang.beli');
                Route::get('/get-barang', [FormBarangController::class, 'get_barang'])->name('billing.form-barang.get-barang');
                Route::post('/keranjang-store', [FormBarangController::class, 'keranjang_store'])->name('billing.form-barang.keranjang-store');
                Route::delete('/keranjang-destroy/{keranjang}', [FormBarangController::class, 'keranjang_destroy'])->name('billing.form-barang.keranjang-destroy');
                Route::get('/keranjang-empty', [FormBarangController::class, 'keranjang_empty'])->name('billing.form-barang.keranjang-empty');
                Route::get('/barang-store', [FormBarangController::class, 'beli_store'])->name('billing.form-barang.barang-store');
                Route::get('/jual', [FormBarangController::class, 'jual'])->name('billing.form-barang.jual');
                Route::post('/jual-store', [FormBarangController::class, 'jual_store'])->name('billing.form-barang.jual-store');
                Route::get('/get-harga-jual', [FormBarangController::class, 'get_harga_jual'])->name('billing.form-barang.get-harga-jual');

                Route::prefix('umum')->group(function () {
                    Route::get('/', [FormBarangController::class, 'jual_umum'])->name('billing.form-barang.jual-umum');
                    Route::post('/store', [FormBarangController::class, 'jual_umum_store'])->name('billing.form-barang.jual-umum.store');
                });
            });

            // form kasbon
            Route::prefix('kasbon')->group(function () {
                Route::get('/', [FormKasbonController::class, 'index'])->name('billing.kasbon.index');

                Route::prefix('direksi')->group(function () {
                    Route::view('/', 'billing.kasbon.direksi.index')->name('billing.kasbon.direksi.index');
                    Route::get('/kasbon', [FormKasbonController::class, 'direksi_kas'])->name('billing.kasbon.direksi.kasbon');
                    Route::get('/bayar', [FormKasbonController::class, 'direksi_bayar'])->name('billing.kasbon.direksi.bayar');
                    Route::get('/bayar/list', [FormKasbonController::class, 'direksi_bayar_list'])->name('billing.kasbon.direksi.bayar.list');
                    Route::post('/bayar-store/{direksi}', [FormKasbonController::class, 'direksi_bayar_store'])->name('billing.kasbon.direksi.bayar-store');
                    Route::post('/kasbon-store', [FormKasbonController::class, 'direksi_kas_store'])->name('billing.kasbon.direksi.kasbon-store');
                });

                Route::post('/store', [FormKasbonController::class, 'store'])->name('billing.kasbon.store');
                Route::view('/kas-bon-staff', 'billing.kasbon.kas-bon-staff')->name('billing.kasbon.kas-bon-staff');

                Route::get('/kas-bon-cicil', [FormKasbonController::class, 'kas_bon_cicil'])->name('billing.kasbon.kas-bon-cicil');
                Route::post('/kas-bon-cicil/void/{kas}', [FormKasbonController::class, 'kas_bon_cicil_void'])->name('billing.kasbon.kas-bon-cicil.void');
                Route::post('/kas-bon-cicil-store', [FormKasbonController::class, 'kas_bon_cicil_store'])->name('billing.kasbon.kas-bon-cicil-store');
            });

            // Form Gaji
            Route::get('/gaji', [FormGajiController::class, 'index'])->name('billing.gaji.index');
            Route::post('/gaji/store', [FormGajiController::class, 'store'])->name('billing.gaji.store');
            Route::get('/gaji/preview-pdf', [FormGajiController::class, 'previewPdf'])->name('billing.gaji.preview-pdf');
            Route::get('/gaji/preview-excel', [FormGajiController::class, 'previewExcel'])->name('billing.gaji.preview-excel');

            Route::prefix('transaksi')->group(function () {
                Route::get('/', [TransaksiController::class, 'index'])->name('billing.transaksi.index');

                Route::prefix('invoice')->group(function () {
                    Route::get('/', [InvoiceController::class, 'index'])->name('billing.transaksi.invoice.index');

                    Route::prefix('tagihan')->group(function () {
                        Route::get('/', [InvoiceController::class, 'tagihan'])->name('invoice.tagihan.index');
                        Route::get('/{invoice}/detail', [InvoiceController::class, 'invoice_tagihan_detail'])->name('invoice.tagihan.detail');
                        Route::post('/{invoice}/lunas', [InvoiceController::class, 'tagihan_lunas'])->name('invoice.tagihan.lunas');
                        Route::post('/{invoice}/cicil', [InvoiceController::class, 'tagihan_cicil'])->name('invoice.tagihan.cicil');
                    });

                    Route::get('/tagihan-export/{invoice}', [InvoiceController::class, 'invoice_tagihan_detail_export'])->name('invoice.tagihan-detail.export');

                    Route::prefix('bayar')->group(function () {

                        Route::post('/{invoice}/lunas', [InvoiceController::class, 'invoice_bayar_lunas'])->name('invoice.bayar.lunas');
                        Route::post('/{invoice}/jenis-lunas', [InvoiceController::class, 'invoice_bayar_jenis_lunas'])->name('invoice.bayar.jenis-lunas');
                    });

                    Route::get('/bonus', [InvoiceController::class, 'invoice_bonus'])->name('invoice.bonus.index');
                    Route::get('/bonus/{invoiceBonus}/detail', [InvoiceController::class, 'invoice_bonus_detail'])->name('invoice.bonus.detail');
                    Route::post('/bonus/{invoice}/lunas', [InvoiceController::class, 'invoice_bonus_lunas'])->name('invoice.bonus.lunas');
                });

            });

            Route::prefix('form-cost-operational')->group(function () {
                Route::get('/', [BillingController::class, 'form_cost_operational'])->name('billing.form-cost-operational');

                Route::prefix('cost-operational')->group(function () {
                    Route::get('/', [BillingController::class, 'cost_operational'])->name('billing.form-cost-operational.cost-operational');
                    Route::post('/store', [BillingController::class, 'cost_operational_store'])->name('billing.form-cost-operational.cost-operational.store');
                });

                Route::prefix('masuk')->group(function () {
                    Route::get('/', [BillingController::class, 'cost_operational_masuk'])->name('billing.form-cost-operational.masuk');
                    Route::post('/store', [BillingController::class, 'cost_operational_masuk_store'])->name('billing.form-cost-operational.masuk.store');
                });
            });

        });

        Route::prefix('transaksi')->group(function () {

            // sales order
            Route::get('/sales-order', [TransaksiController::class, 'sales_order'])->name('transaksi.sales-order');

            Route::get('/nota-bonus', [TransaksiController::class, 'nota_bonus'])->name('transaksi.nota-bonus');
            Route::post('/nota-bonus/{sponsor}/lanjut', [TransaksiController::class, 'nota_bonus_lanjut'])->name('transaksi.nota-bonus.lanjut');

            Route::post('/tagihan/void/{transaksi}', [TransaksiController::class, 'void_tagihan'])->name('transaksi.tagihan.void');
            Route::post('/tagihan/void/{transaksi}/store', [TransaksiController::class, 'void_tagihan_store'])->name('transaksi.tagihan.void.store');

            Route::post('/void-masuk/{transaksi}', [TransaksiController::class, 'void'])->name('transaksi.void-masuk');
            Route::post('/void/{transaksi}', [TransaksiController::class, 'void_store'])->name('transaksi.void.store');
            Route::post('/back/{transaksi}', [TransaksiController::class, 'back'])->name('transaksi.back');
            Route::post('/back-tagihan/{transaksi}', [TransaksiController::class, 'back_tagihan'])->name('transaksi.back-tagihan');

            // Route::post('transaksi/nota-tagihan/{customer}/lanjut', [App\Http\Controllers\TransaksiController::class, 'nota_tagihan_lanjut'])->name('transaksi.nota-tagihan.lanjut');
            Route::post('/nota-tagihan-lanjut-pilih/{customer}', [TransaksiController::class, 'nota_tagihan_lanjut_pilih'])->name('transaksi.nota-tagihan.lanjut-pilih');
        });

        Route::resource('bbm-storing', BbmStoringController::class);

        Route::view('rekap-gaji', 'rekap.gaji')->name('rekap-gaji');
        Route::get('rekap-gaji-detail', [RekapController::class, 'rekap_gaji_detail'])->name('rekap-gaji-detail');
        Route::get('print-slip-gaji/{id}', [RekapController::class, 'print_slip_gaji'])->name('print-slip-gaji');
        Route::get('print-rekap-gaji', [RekapController::class, 'print_rekap_gaji'])->name('print-rekap-gaji');

        Route::prefix('rekap')->group(function () {
            Route::get('/', [RekapController::class, 'index'])->name('rekap.index');

            Route::prefix('uj-ditahan')->group(function () {
                Route::get('/', [RekapController::class, 'uj_ditahan'])->name('rekap.uj-ditahan');
            });

            Route::prefix('bunga-investor')->group(function () {
                Route::get('/', [RekapController::class, 'bunga_investor'])->name('rekap.bunga-investor');
            });

            Route::prefix('tagihan-invoice')->group(function () {
                Route::get('/', [RekapController::class, 'tagihan_invoice'])->name('rekap.tagihan-invoice');
            });

            Route::prefix('cost-opertaional')->group(function () {
                Route::get('/', [RekapController::class, 'cost_operational'])->name('rekap.cost-operational');
            });

            Route::get('/kas-besar', [RekapController::class, 'kas_besar'])->name('rekap.kas-besar');
            Route::get('/kas-besar/preview/{bulan}/{tahun}', [RekapController::class, 'preview_kas_besar'])->name('rekap.kas-besar.preview');
            Route::get('/kas-kecil', [RekapController::class, 'kas_kecil'])->name('rekap.kas-kecil');
            Route::get('/kas-kecil/preview/{bulan}/{tahun}', [RekapController::class, 'preview_kas_kecil'])->name('rekap.kas-kecil.preview');
            Route::get('/kas-uang-jalan', [RekapController::class, 'kas_uang_jalan'])->name('rekap.kas-uang-jalan');
            Route::get('/kas-uang-jalan/preview/{bulan}/{tahun}', [RekapController::class, 'preview_kas_uang_jalan'])->name('rekap.kas-uang-jalan.preview');

            Route::get('/kas-vendor', [RekapController::class, 'kas_vendor'])->name('rekap.kas-vendor');
            Route::get('/kas-vendor/{invoiceBayar}/detail', [RekapController::class, 'kas_vendor_detail'])->name('rekap.kas-vendor.detail');
            Route::post('/kas-vendor/void/{kas_vendor}', [RekapController::class, 'kas_vendor_void'])->name('rekap.kas-vendor.void');
            Route::get('/kas-vendor/preview/{bulan}/{tahun}', [RekapController::class, 'preview_kas_vendor'])->name('rekap.kas-vendor.preview');

            Route::get('/csr', [RekapController::class, 'rekap_csr'])->name('rekap.csr');
            Route::get('/csr/{invoiceCsr}/detail', [RekapController::class, 'rekap_csr_detail'])->name('rekap.csr.detail');

            Route::get('/nota-void', [RekapController::class, 'nota_void'])->name('rekap.nota-void');
            Route::get('/nota-void/preview/{bulan}/{tahun}', [RekapController::class, 'preview_nota_void'])->name('rekap.nota-void.preview');

            Route::get('/stock-barang', [RekapController::class, 'stock_barang'])->name('rekap.stock-barang');

            Route::get('/kas-bon', [RekapController::class, 'kas_bon'])->name('rekap.kas-bon');
            Route::get('/kas-bon/preview/{bulan}/{tahun}', [RekapController::class, 'preview_kas_bon'])->name('rekap.kas-bon.preview');
            Route::post('/kas-bon/void/{kas}', [RekapController::class, 'kas_bon_void'])->name('rekap.kas-bon.void');

            Route::get('/kas-bon/direksi', [RekapController::class, 'kas_bon_direksi'])->name('rekap.kas-bon.direksi');
            Route::get('/direksi', [RekapController::class, 'direksi'])->name('rekap.direksi');

            Route::get('/bonus', [RekapController::class, 'rekap_bonus'])->name('rekap.bonus');
            Route::get('/bonus/{invoiceBonus}/detail', [RekapController::class, 'rekap_bonus_detail'])->name('rekap.bonus.detail');
            Route::get('/nota-lunas', [RekapController::class, 'nota_lunas'])->name('rekap.nota-lunas');
            Route::get('/nota-lunas-detail/{invoice}', [RekapController::class, 'nota_lunas_detail'])->name('rekap.nota-lunas-detail');

            Route::get('/maintenance-vehicle', [RekapController::class, 'maintenance_vehicle'])->name('rekap.maintenance-vehicle');
            Route::get('/maintenance-vehicle/print', [RekapController::class, 'maintenance_vehicle_print'])->name('rekap.maintenance-vehicle.print');
            Route::post('/maintenance-vehicle/store-odometer', [RekapController::class, 'store_odo'])->name('rekap.maintenance-vehicle.store-odometer');

            Route::prefix('maintenance')->group(function () {
                Route::get('/filter-oli', [FilterOliReportController::class, 'recap'])->name('rekap.maintenance.filter-oli');
                Route::get('/filter-oli/{id}', [FilterOliReportController::class, 'recapDetail'])->name('rekap.maintenance.filter-oli.show');
                Route::prefix('ban-luar')->group(function () {
                    Route::get('/', [RekapController::class, 'ban_luar'])->name('rekap.maintenance.ban-luar');
                    Route::get('/{id}', [RekapController::class, 'ban_luar_detail'])->name('rekap.maintenance.ban-luar.show');
                    Route::post('/detail/{detailId}/update-tanggal', [RekapController::class, 'update_tanggal_detail_ban'])->name('rekap.maintenance.ban-luar.detail.update-tanggal');
                    Route::post('/detail/{id}/update', [BillingController::class, 'update_detail_item'])->name('rekap.maintenance.ban-luar.detail.update');
                });

                Route::prefix('aki')->group(function () {
                    Route::get('/', [RekapController::class, 'aki'])->name('rekap.maintenance.aki');
                    Route::get('/{id}', [RekapController::class, 'aki_detail'])->name('rekap.maintenance.aki.show');
                    Route::post('/detail/{detailId}/update-tanggal', [RekapController::class, 'update_tanggal_detail_aki'])->name('rekap.maintenance.aki.detail.update-tanggal');
                    Route::post('/detail/{id}/update', [BillingController::class, 'update_detail_item_aki'])->name('rekap.maintenance.aki.detail.update');
                });
            });
        });

        Route::prefix('statistik')->group(function () {

            Route::get('/perform-unit-tahunan', [StatistikController::class, 'perform_unit_tahunan'])->name('statistik.perform-unit-tahunan');
            Route::get('/perform-unit-tahunan/print', [StatistikController::class, 'perform_unit_tahunan_print'])->name('statistik.perform-unit-tahunan.print');

            Route::get('/customer', [StatistikController::class, 'statistik_customer'])->name('statistik.customer');

            Route::get('/perform-vendor', [StatistikController::class, 'perform_vendor'])->name('statistik.perform-vendor');
            Route::get('/vendor', [StatistikController::class, 'statistik_vendor'])->name('statistik.vendor');

            Route::get('/upah-gendong', [StatistikController::class, 'upah_gendong'])->name('statistik.upah-gendong');

        });

        // Route::get('dokumen/template-new', [App\Http\Controllers\DokumenNewController::class, 'index'])->name('template-new');
        // Route::get('dokumen/template-new/kontrak', [App\Http\Controllers\DokumenNewController::class, 'kontrak_new'])->name('template-new.kontrak');
        // Route::post('dokumen/template-new/kontrak/create', [App\Http\Controllers\DokumenNewController::class, 'create_template_kontrak'])->name('template-new.kontrak.create');

    });

    Route::group(['middleware' => 'role:admin,user,su,operasional,vendor'], function () {
        Route::prefix('billing/transaksi/invoice/bayar')->group(function () {
            Route::get('/', [InvoiceController::class, 'invoice_bayar'])->name('invoice.bayar.index');
            Route::get('/{invoiceBayar}/detail', [InvoiceController::class, 'invoice_bayar_detail'])->name('invoice.bayar.detail');
            Route::get('/{invoice}/detail-jenis', [InvoiceController::class, 'invoice_bayar_detail_add'])->name('invoice.bayar.detail-jenis');
        });

        Route::prefix('statistik/perform-unit')->group(function () {
            Route::get('/all-vendor', [StatistikController::class, 'perform_unit_all_vendor'])->name('statistik.perform-unit.all-vendor');
            Route::get('/all-vendor/pdf', [StatistikController::class, 'perform_unit_all_vendor_pdf'])->name('statistik.perform-unit.all-vendor.pdf');
        });

    });

    Route::group(['middleware' => 'role:vendor'], function () {
        Route::get('kas-per-vendor/{vendor}', [RekapController::class, 'kas_per_vendor'])->name('kas-per-vendor.index');
        Route::get('kas-per-vendor/{invoiceBayar}/detail', [RekapController::class, 'kas_per_vendor_detail'])->name('kas-per-vendor.detail');
        Route::get('pritn-kas-per-vendor/{vendor}/{bulan}/{tahun}', [RekapController::class, 'print_kas_per_vendor'])->name('print-kas-per-vendor.index');
        Route::get('perform-unit-pervendor', [StatistikController::class, 'perform_unit_pervendor'])->name('perform-unit-pervendor.index');
        Route::get('statistik-pervendor', [StatistikController::class, 'statistik_pervendor'])->name('statistik-pervendor.index');

        Route::prefix('per-vendor')->group(function () {
            Route::get('/upah-gendong', [PerVendorController::class, 'upah_gendong'])->name('per-vendor.upah-gendong');

            Route::prefix('maintenance-vehicle')->group(function () {
                Route::get('/', [PerVendorController::class, 'maintenance_vehicle'])->name('per-vendor.maintenance-vehicle');
                Route::get('/print', [PerVendorController::class, 'maintenance_vehicle_print'])->name('per-vendor.maintenance-vehicle.print');
                Route::post('/store-odo', [PerVendorController::class, 'store_odo'])->name('per-vendor.maintenance-vehicle.store-odo');
            });

            Route::prefix('ban-luar')->group(function () {
                Route::get('/', [PerVendorController::class, 'ban_luar'])->name('per-vendor.ban-luar');
                Route::post('/store', [PerVendorController::class, 'ban_luar_store'])->name('per-vendor.ban-luar.store');
                Route::get('/{vehicle}/{posisi}/histori', [PerVendorController::class, 'ban_histori'])->name('per-vendor.ban-luar.histori');
                Route::get('/histori-data', [PerVendorController::class, 'ban_histori_data'])->name('per-vendor.ban-luar.histori-data');
                Route::post('/histori-destroy/{histori}', [PerVendorController::class, 'ban_histori_delete'])->name('per-vendor.ban-luar.histori-destroy');
            });

        });

    });

    Route::group(['middleware' => 'role:vendor-operational'], function () {
        Route::prefix('vendor-operational')->group(function () {
            // Route::get('kas-per-vendor/{vendor}', [App\Http\Controllers\RekapController::class, 'kas_per_vendor'])->name('vendor-operational.kas-per-vendor.index');
            // Route::get('kas-per-vendor/{invoiceBayar}/detail', [App\Http\Controllers\RekapController::class, 'kas_per_vendor_detail'])->name('vendor-operational.kas-per-vendor.detail');
            // Route::get('pritn-kas-per-vendor/{vendor}/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'print_kas_per_vendor'])->name('vendor-operational.print-kas-per-vendor.index');
            Route::get('perform-unit-pervendor', [PerVendorOperationalController::class, 'perform_unit_pervendor'])->name('vendor-operational.perform-unit-pervendor.index');
            // Route::get('statistik-pervendor', [App\Http\Controllers\StatistikController::class, 'statistik_pervendor'])->name('vendor-operational.statistik-pervendor.index');

            Route::prefix('per-vendor')->group(function () {
                Route::get('/upah-gendong', [PerVendorOperationalController::class, 'upah_gendong'])->name('vendor-operational.per-vendor.upah-gendong');

                Route::prefix('maintenance-vehicle')->group(function () {
                    Route::get('/', [PerVendorOperationalController::class, 'maintenance_vehicle'])->name('vendor-operational.per-vendor.maintenance-vehicle');
                    Route::get('/print', [PerVendorOperationalController::class, 'maintenance_vehicle_print'])->name('vendor-operational.per-vendor.maintenance-vehicle.print');
                    Route::post('/store-odo', [PerVendorOperationalController::class, 'store_odo'])->name('vendor-operational.per-vendor.maintenance-vehicle.store-odo');
                });

                Route::prefix('ban-luar')->group(function () {
                    Route::get('/', [PerVendorOperationalController::class, 'ban_luar'])->name('vendor-operational.per-vendor.ban-luar');
                    // Route::post('/store', [App\Http\Controllers\PerVendorOperationalController::class, 'ban_luar_store'])->name('vendor-operational.per-vendor.ban-luar.store');
                    Route::get('/{vehicle}/{posisi}/histori', [PerVendorOperationalController::class, 'ban_histori'])->name('vendor-operational.per-vendor.ban-luar.histori');
                    Route::get('/histori-data', [PerVendorOperationalController::class, 'ban_histori_data'])->name('vendor-operational.per-vendor.ban-luar.histori-data');
                    // Route::post('/histori-destroy/{histori}', [App\Http\Controllers\PerVendorOperationalController::class, 'ban_histori_delete'])->name('vendor-operational.per-vendor.ban-luar.histori-destroy');
                });

            });
        });

    });

    Route::group(['middleware' => 'role:customer'], function () {
        Route::prefix('per-customer')->group(function () {
            // Route::get('nota-tagihan', [App\Http\Controllers\PerCustomerController::class, 'nota_tagihan'])->name('per-customer.nota-tagihan');
            // Route::get('nota-tagihan/print', [App\Http\Controllers\PerCustomerController::class, 'nota_tagihan_print'])->name('per-customer.nota-tagihan.print');

            // Route::get('invoice-tagihan', [App\Http\Controllers\PerCustomerController::class, 'invoice'])->name('per-customer.invoice-tagihan');
            // Route::get('invoice-tagihan/{invoice}/detail', [App\Http\Controllers\PerCustomerController::class, 'invoice_detail'])->name('per-customer.invoice-tagihan.detail');
            // Route::get('invoice-tagihan/{invoice}/export', [App\Http\Controllers\PerCustomerController::class, 'invoice_export'])->name('per-customer.invoice-tagihan.export');

            // Route::get('nota-lunas', [App\Http\Controllers\PerCustomerController::class, 'nota_lunas'])->name('per-customer.nota-lunas');
            // Route::get('nota-lunas/data', [App\Http\Controllers\PerCustomerController::class, 'nota_lunas_data'])->name('per-customer.nota-lunas.data');
            // Route::get('nota-lunas/{invoice}/detail', [App\Http\Controllers\PerCustomerController::class, 'nota_lunas_detail'])->name('per-customer.nota-lunas.detail');

            Route::prefix('tonase-tambang')->group(function () {
                Route::get('/', [PerCustomerController::class, 'tonase_tambang'])->name('per-customer.tonase-tambang');
                Route::get('/pdf', [PerCustomerController::class, 'tonase_tambang_download'])->name('per-customer.tonase-tambang.pdf');
            });

        });
    });

    Route::group(['middleware' => 'role:customer-admin'], function () {
        Route::prefix('per-customer-admin')->group(function () {
            Route::get('nota-tagihan', [PerCustomerAdminController::class, 'nota_tagihan'])->name('per-customer-admin.nota-tagihan');
            Route::get('nota-tagihan/print', [PerCustomerAdminController::class, 'nota_tagihan_print'])->name('per-customer-admin.nota-tagihan.print');

            Route::get('invoice-tagihan', [PerCustomerAdminController::class, 'invoice'])->name('per-customer-admin.invoice-tagihan');
            Route::get('invoice-tagihan/{invoice}/detail', [PerCustomerAdminController::class, 'invoice_detail'])->name('per-customer-admin.invoice-tagihan.detail');
            Route::get('invoice-tagihan/{invoice}/export', [PerCustomerAdminController::class, 'invoice_export'])->name('per-customer-admin.invoice-tagihan.export');

            Route::get('nota-lunas', [PerCustomerAdminController::class, 'nota_lunas'])->name('per-customer-admin.nota-lunas');
            Route::get('nota-lunas/data', [PerCustomerAdminController::class, 'nota_lunas_data'])->name('per-customer-admin.nota-lunas.data');
            Route::get('nota-lunas/{invoice}/detail', [PerCustomerAdminController::class, 'nota_lunas_detail'])->name('per-customer-admin.nota-lunas.detail');

            Route::prefix('tonase-tambang')->group(function () {
                Route::get('/', [PerCustomerAdminController::class, 'tonase_tambang'])->name('per-customer-admin.tonase-tambang');
                Route::get('/pdf', [PerCustomerAdminController::class, 'tonase_tambang_download'])->name('per-customer-admin.tonase-tambang.pdf');
            });

        });
    });

    Route::group(['middleware' => 'role:operasional'], function () {
        Route::prefix('operasional')->group(function () {
            // Route::get('kas-vendor', [App\Http\Controllers\OperasionalController::class, 'kas_vendor'])->name('operasional.kas-vendor');
            // Route::get('kas-vendor/{invoiceBayar}/detail', [App\Http\Controllers\OperasionalController::class, 'kas_vendor_detail'])->name('operasional.kas-vendor.detail');
            // Route::get('kas-vendor/preview/{bulan}/{tahun}', [App\Http\Controllers\OperasionalController::class, 'kas_vendor_print'])->name('operasional.kas-vendor.print');

            Route::get('perform-unit', [OperasionalController::class, 'perform_unit'])->name('operasional.perform-unit');
            Route::get('perform-unit/print', [OperasionalController::class, 'perform_unit_print'])->name('operasional.perform-unit.print');

            Route::get('upah-gendong', [OperasionalController::class, 'upah_gendong'])->name('operasional.upah-gendong');

            Route::prefix('maintenance')->group(function () {
                Route::get('/', [OperasionalController::class, 'maintenance_vehicle'])->name('operasional.maintenance-vehicle');
                Route::get('/print', [OperasionalController::class, 'maintenance_vehicle_print'])->name('operasional.maintenance-vehicle.print');
                Route::post('/store-odo', [OperasionalController::class, 'store_odo'])->name('operasional.maintenance-vehicle.store-odo');

            });

            Route::prefix('statistik/{customer}')->group(function () {
                Route::get('tonase-tambang', [OperasionalController::class, 'tonase_tambang'])->name('operasional.tonase-tambang');
                Route::get('tonase-tambang/download', [OperasionalController::class, 'tonase_tambang_download'])->name('operasional.tonase-tambang.download');
            });

            Route::prefix('ban-luar')->group(function () {
                Route::get('/', [OperasionalController::class, 'ban_luar'])->name('operational.ban-luar');
                Route::get('/{vehicle}/{posisi}/histori', [OperasionalController::class, 'ban_luar_histori'])->name('operational.ban-luar.histori');
                Route::get('/histori-data', [OperasionalController::class, 'ban_luar_histori_data'])->name('operational.ban-luar.histori-data');
            });

            // Route::get('statistik-vendor', [App\Http\Controllers\OperasionalController::class, 'statistik_vendor'])->name('operasional.statistik-vendor');
        });
    });

    Route::group(['prefix' => 'asisten-user', 'middleware' => 'role:asisten-user'], function () {
        Route::get('perform-unit', [AsistenUserController::class, 'perform_unit'])->name('asisten-user.perform-unit');
        Route::get('perform-unit-tahunan', [AsistenUserController::class, 'perform_unit_tahunan'])->name('asisten-user.perform-unit-tahunan');
        Route::get('upah-gendong', [AsistenUserController::class, 'upah_gendong'])->name('asisten-user.upah-gendong');
    });
});
