<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureDriverWebAccess;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * Role driver: hanya menu driver, baca saja, dan selalu dikunci ke nolam yang
 * sedang ditugaskan (nolam lain milik vendor yang sama pun tidak terlihat).
 */
class DriverAccessTest extends MobileApiTestCase
{
    private User $driver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        Schema::table('users', fn (Blueprint $table) => $table->unsignedBigInteger('driver_id')->nullable()->unique());
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->unsignedBigInteger('driver_id')->nullable()->unique();
            $table->boolean('pembatasan_filter_oli')->default(true);
        });
        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('status');
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'VENDOR NOLAM'], ['id' => 2, 'nama' => 'VENDOR LAIN']]);
        DB::table('drivers')->insert([['id' => 1, 'nama' => 'Driver A', 'status' => 'aktif'], ['id' => 2, 'nama' => 'Driver B', 'status' => 'aktif']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'driver_id' => 1, 'nomor_lambung' => 'NOLAM-DRIVER'],
            ['id' => 2, 'vendor_id' => 1, 'driver_id' => 2, 'nomor_lambung' => 'NOLAM-REKAN'],
            ['id' => 3, 'vendor_id' => 2, 'driver_id' => null, 'nomor_lambung' => 'NOLAM-VENDOR-LAIN'],
        ]);
        foreach (['ban_logs', 'filter_oli_logs'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('vehicle_id');
            });
            DB::table($name)->insert([['id' => 1, 'vehicle_id' => 1], ['id' => 2, 'vehicle_id' => 2], ['id' => 3, 'vehicle_id' => 3]]);
        }
        $this->driver = User::factory()->create(['username' => 'driver-a', 'password' => 'secret-pass', 'role' => 'driver', 'driver_id' => 1]);
        $this->actingAs($this->driver);
    }

    public function test_dashboard_links_every_driver_menu(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Dashboard Driver')->assertSee('NOLAM-DRIVER')
            ->assertSee(route('driver.notes', 'muat'), false)->assertSee(route('driver.notes', 'bongkar'), false)
            ->assertSee('ujDitahanMenu')
            ->assertSee(route('statistik.perform-unit'), false)->assertSee(route('statistik.upah-gendong'), false)
            ->assertSee(route('statistik.ban-luar'), false)->assertSee(route('statistik.aki'), false)->assertSee(route('statistik.filter-oli'), false)
            ->assertDontSee('vendor-operational/statistics/vehicles', false);
    }

    /** Setiap rute yang boleh dibuka driver hanya GET dan memang terdaftar. */
    public function test_readable_routes_exist_and_are_get_only(): void
    {
        foreach (EnsureDriverWebAccess::READABLE_ROUTES as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, $name);
            $this->assertSame(['GET', 'HEAD'], $route->methods(), $name);
        }
    }

    #[TestWith(['/pengguna'])]
    #[TestWith(['/billing/nota-muat'])]
    #[TestWith(['/vendor-operational/nota-bayar'])]
    #[TestWith(['/vendor-operational/nota-bongkar'])]
    #[TestWith(['/vendor-operational/invoice'])]
    #[TestWith(['/vendor-operational/statistics/vehicles?report=aki'])]
    #[TestWith(['/statistik/perform-unit/print?start_date=2026-11-01&end_date=2026-11-30'])]
    #[TestWith(['/statistik/perform-unit/all-vendor'])]
    #[TestWith(['/statisik'])]
    #[TestWith(['/rekap'])]
    #[TestWith(['/database'])]
    #[TestWith(['/mobile/ruang-kerja'])]
    public function test_menus_outside_the_driver_role_are_forbidden(string $url): void
    {
        $this->get($url)->assertForbidden();
    }

    #[TestWith(['/statistik/aki?vehicle_id=2'])]
    #[TestWith(['/statistik/aki?vehicle_id=3'])]
    #[TestWith(['/statistik/ban-luar?vehicle_id=2'])]
    #[TestWith(['/statistik/filter-oli?vehicle_id=2'])]
    #[TestWith(['/statistik/upah-gendong?vehicle_id=2'])]
    #[TestWith(['/statistik/aki/2/1/histori'])]
    #[TestWith(['/statistik/ban-luar/3/1/histori'])]
    #[TestWith(['/statistik/filter-oli/2/1/histori'])]
    #[TestWith(['/statistik/aki/histori-data?vehicle=2&posisi=1'])]
    #[TestWith(['/statistik/ban-luar/histori-data?vehicle=2&posisi=1'])]
    #[TestWith(['/statistik/ban-luar/histori-data?vehicle_id=1&vehicle=2&posisi=1'])]
    #[TestWith(['/statistik/ban-luar/transaksi-ritase/2'])]
    #[TestWith(['/statistik/filter-oli/transaksi-ritase/3'])]
    public function test_other_nolam_even_from_the_same_vendor_is_hidden(string $url): void
    {
        $this->getJson($url)->assertNotFound();
    }

    #[TestWith(['POST', '/statistik/aki/histori-destroy/1'])]
    #[TestWith(['PATCH', '/statistik/aki/histori-update/1'])]
    #[TestWith(['POST', '/statistik/ban-luar/histori-destroy/1'])]
    #[TestWith(['PATCH', '/statistik/ban-luar/histori-update/1'])]
    #[TestWith(['PATCH', '/statistik/filter-oli/histori/1'])]
    #[TestWith(['DELETE', '/statistik/filter-oli/histori/1'])]
    #[TestWith(['POST', '/statistik/ban-luar/store'])]
    #[TestWith(['PATCH', '/transaksi/nota-bongkar/update/1'])]
    public function test_every_write_is_forbidden(string $method, string $url): void
    {
        $this->json($method, $url, ['vehicle_id' => 1])->assertForbidden();
    }

    public function test_maintenance_opens_directly_on_the_driver_nolam_without_actions(): void
    {
        Schema::create('posisi_akis', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('aki_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('posisi_aki_id');
            $table->string('merk');
            $table->integer('kondisi');
            $table->timestamps();
        });
        DB::table('posisi_akis')->insert(['id' => 1, 'nama' => 'DEPAN']);
        DB::table('aki_logs')->insert([
            ['vehicle_id' => 1, 'posisi_aki_id' => 1, 'merk' => 'AKI-DRIVER', 'kondisi' => 90, 'created_at' => now()],
            ['vehicle_id' => 2, 'posisi_aki_id' => 1, 'merk' => 'AKI-REKAN', 'kondisi' => 80, 'created_at' => now()],
        ]);

        // Tanpa pemilih nolam: vehicle_id selalu nolam driver.
        $this->get('/statistik/aki')->assertOk()->assertViewHas('vehicle', fn ($vehicle): bool => $vehicle->id === 1)
            ->assertSee('AKI-DRIVER')->assertDontSee('AKI-REKAN')->assertDontSee('Ganti kendaraan')->assertDontSee(route('rekap.index'), false);

        Schema::create('posisi_bans', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        DB::table('posisi_bans')->insert(['id' => 1, 'nama' => 'DEPAN']);
        foreach (['aki', 'ban-luar'] as $report) {
            $this->get('/statistik/'.$report.'/1/1/histori')->assertOk()
                ->assertDontSee('passwordForm')->assertDontSee('updateForm')->assertDontSee('delete-btn')->assertDontSee('edit-btn')
                ->assertSee('vendorStatisticsTable');
        }
    }

    public function test_unactivated_upah_gendong_and_filter_oli_return_to_the_dashboard_with_a_message(): void
    {
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
        });
        $this->get('/statistik/upah-gendong')->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Upah gendong belum diaktifkan untuk Nolam NOLAM-DRIVER.');

        DB::table('vehicles')->where('id', 1)->update(['pembatasan_filter_oli' => false]);
        $this->get('/statistik/filter-oli')->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Filter & oli mesin belum diaktifkan untuk nolam Anda.');
    }

    public function test_perform_unit_shows_only_the_driver_nolam(): void
    {
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('jarak');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('rute_id');
            $table->date('tanggal');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kas_uang_jalan_id');
            $table->boolean('void')->default(false);
            $table->decimal('timbangan_bongkar', 10, 3);
        });
        DB::table('rutes')->insert([['id' => 1, 'nama' => 'RUTE-DRIVER', 'jarak' => 75], ['id' => 2, 'nama' => 'RUTE-REKAN', 'jarak' => 40]]);
        foreach ([1, 2] as $vehicle) {
            DB::table('kas_uang_jalans')->insert(['id' => $vehicle, 'vendor_id' => 1, 'vehicle_id' => $vehicle, 'rute_id' => $vehicle, 'tanggal' => '2026-11-01']);
            DB::table('transaksis')->insert(['id' => $vehicle, 'kas_uang_jalan_id' => $vehicle, 'timbangan_bongkar' => $vehicle === 1 ? 30.125 : 77.777]);
        }

        $response = $this->get('/statistik/perform-unit?bulan=11&tahun=2026&vendor=2')->assertOk()
            ->assertSee('NOLAM-DRIVER')->assertSee('RUTE-DRIVER')
            ->assertDontSee('NOLAM-REKAN')->assertDontSee('RUTE-REKAN')->assertDontSee('NOLAM-VENDOR-LAIN')
            ->assertDontSee('id="printForm"', false)->assertViewHas('jumlah_vehicle', 1)->assertViewHas('grand_total_tonase', 30.125);
        $this->assertSame(['NOLAM-DRIVER'], array_keys($response->viewData('statistics')));
    }

    public function test_reassigned_driver_loses_the_old_nolam_immediately(): void
    {
        DB::table('vehicles')->where('id', 1)->update(['driver_id' => null]);
        DB::table('vehicles')->where('id', 3)->update(['driver_id' => 1]);

        $this->getJson('/statistik/ban-luar/transaksi-ritase/1')->assertNotFound();
        $this->get(route('home'))->assertOk()->assertSee('NOLAM-VENDOR-LAIN')->assertDontSee('Nolam NOLAM-DRIVER');
    }
}
