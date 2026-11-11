<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureVendorOperationalVehicle;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class VendorOperationalStatisticsAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('nomor_lambung')->default('');
            $table->string('status')->default('aktif');
            $table->boolean('pembatasan_filter_oli')->default(true);
        });
        DB::table('vendors')->insert(['id' => 1]);
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1], ['id' => 2, 'vendor_id' => 2]]);
        foreach (['ban_logs', 'filter_oli_logs'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('vehicle_id');
            });
            DB::table($name)->insert([['id' => 1, 'vehicle_id' => 1], ['id' => 2, 'vehicle_id' => 2]]);
        }
        Route::middleware(['web', EnsureVendorOperationalVehicle::class.':statistics'])->group(function (): void {
            Route::get('/statistics-access-test/log/{log}', fn () => response('allowed'));
            Route::get('/statistics-access-test/ban/{banLogId}', fn () => response('allowed'));
            Route::match(['GET', 'POST', 'PATCH', 'DELETE'], '/statistics-access-test', fn () => response()->json(['vendor' => request('vendor')]));
        });
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->actingAs(User::factory()->make(['password' => 'test-password', 'id' => 1, 'role' => 'vendor-operational', 'vendor_id' => 1]));
    }

    #[TestWith(['/statistics-access-test/log/2'])]
    #[TestWith(['/statistics-access-test/ban/2'])]
    #[TestWith(['/statistics-access-test?vehicle_id=2'])]
    #[TestWith(['/statistics-access-test?vehicle_id=1&vehicle=2'])]
    public function test_other_vendor_records_are_hidden(string $url): void
    {
        $this->getJson($url)->assertNotFound();
    }

    #[TestWith(['POST'])]
    #[TestWith(['PATCH'])]
    #[TestWith(['DELETE'])]
    public function test_statistics_are_read_only(string $method): void
    {
        $this->json($method, '/statistics-access-test')->assertForbidden();
    }

    public function test_owned_logs_remain_readable_and_vendor_filter_cannot_be_forged(): void
    {
        $this->getJson('/statistics-access-test/log/1')->assertOk();
        $this->getJson('/statistics-access-test/ban/1')->assertOk();
        $this->getJson('/statistics-access-test?vendor=2')->assertOk()->assertJson(['vendor' => 1]);
    }

    public function test_unassigned_vendor_is_denied_before_any_statistics_query(): void
    {
        auth()->user()->vendor_id = null;
        $this->getJson('/statistics-access-test')->assertForbidden();
    }

    public function test_other_roles_keep_their_statistics_behavior(): void
    {
        auth()->user()->role = 'admin';
        $this->postJson('/statistics-access-test', ['vendor' => 2])->assertOk()->assertJson(['vendor' => 2]);
    }

    #[TestWith(['/statistik/aki?vehicle_id=2'])]
    #[TestWith(['/statistik/ban-luar?vehicle_id=2'])]
    #[TestWith(['/statistik/filter-oli?vehicle_id=2'])]
    #[TestWith(['/statistik/upah-gendong?vehicle_id=2'])]
    #[TestWith(['/statistik/aki/2/1/histori'])]
    #[TestWith(['/statistik/ban-luar/2/1/histori'])]
    #[TestWith(['/statistik/filter-oli/2/1/histori'])]
    #[TestWith(['/statistik/aki/histori-data?vehicle=2&posisi=1'])]
    #[TestWith(['/statistik/ban-luar/histori-data?vehicle=2&posisi=1'])]
    #[TestWith(['/statistik/ban-luar/transaksi-ritase/2'])]
    #[TestWith(['/statistik/filter-oli/transaksi-ritase/2'])]
    public function test_shared_statistic_endpoints_hide_other_vendor_data(string $url): void
    {
        $this->getJson($url)->assertNotFound();
    }

    #[TestWith(['aki'])]
    #[TestWith(['ban-luar'])]
    #[TestWith(['filter-oli'])]
    #[TestWith(['upah-gendong'])]
    public function test_statistic_entry_redirects_to_vendor_vehicle_selector(string $report): void
    {
        $this->get('/statistik/'.$report)->assertRedirect(route('vendor-operational.statistics.vehicles', ['report' => $report]));
    }

    #[TestWith(['POST', '/statistik/aki/histori-destroy/1'])]
    #[TestWith(['PATCH', '/statistik/aki/histori-update/1'])]
    #[TestWith(['POST', '/statistik/ban-luar/histori-destroy/1'])]
    #[TestWith(['PATCH', '/statistik/ban-luar/histori-update/1'])]
    #[TestWith(['PATCH', '/statistik/filter-oli/histori/1'])]
    #[TestWith(['DELETE', '/statistik/filter-oli/histori/1'])]
    public function test_real_statistics_mutation_routes_are_forbidden(string $method, string $url): void
    {
        $this->json($method, $url)->assertForbidden();
    }

    public function test_selector_html_and_incremental_search_expose_only_owned_vehicles(): void
    {
        $url = route('vendor-operational.statistics.vehicles', ['report' => 'aki']);
        $this->get($url)->assertOk()->assertSee('statisticsVehicle')->assertDontSee('method="post"', false);
        $vehicles = [];
        for ($id = 3; $id <= 30; $id++) {
            $vehicles[] = ['id' => $id, 'vendor_id' => 1, 'nomor_lambung' => 'OWN-'.str_pad($id, 3, '0', STR_PAD_LEFT)];
        }
        DB::table('vehicles')->insert($vehicles);
        DB::table('vehicles')->where('id', 2)->update(['nomor_lambung' => 'FOREIGN']);
        $first = $this->getJson($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(25, 'results')->assertJsonPath('pagination.more', true);
        $second = $this->getJson($url.'&page=2', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(4, 'results')->assertJsonPath('pagination.more', false);
        $this->assertNotContains(2, array_column(array_merge($first->json('results'), $second->json('results')), 'id'));
    }

    public function test_history_html_contains_no_mutation_forms_or_handlers(): void
    {
        foreach (['posisi_akis', 'posisi_bans'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama');
            });
            DB::table($name)->insert(['id' => 1, 'nama' => 'DEPAN']);
        }
        foreach (['aki', 'ban-luar'] as $report) {
            $this->get('/statistik/'.$report.'/1/1/histori')->assertOk()
                ->assertDontSee('passwordForm')->assertDontSee('updateForm')->assertDontSee('delete-btn')->assertDontSee('edit-btn')
                ->assertSee('vendorStatisticsTable')->assertSee('table-bordered');
        }
    }

    public function test_vendor_history_has_edit_and_delete_with_matching_action_column(): void
    {
        foreach (['posisi_akis', 'posisi_bans'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama');
            });
            DB::table($name)->insert(['id' => 1, 'nama' => 'DEPAN']);
        }
        $this->actingAs(User::factory()->make(['password' => 'test-password', 'id' => 1, 'role' => 'vendor', 'vendor_id' => 1]));
        foreach (['aki', 'ban-luar'] as $report) {
            $response = $this->get('/statistik/'.$report.'/1/1/histori')->assertOk()
                ->assertSee('passwordForm')->assertSee('updateForm')->assertSee('delete-btn')->assertSee('edit-btn')->assertSee('Password akun Anda');
            // The header must declare the ACTION column the script adds, or DataTables breaks.
            $dom = new \DOMDocument;
            @$dom->loadHTML($response->getContent());
            $this->assertSame(1, (new \DOMXPath($dom))->query('//table[@id="rekapTable"]/thead//th[normalize-space()="ACTION"]')->length, $report);
            $this->get('/statistik/'.$report.'/2/1/histori')->assertNotFound();
        }
    }

    private function createTransactionFixtures(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('jarak');
        });
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'RUTE SENDIRI', 'jarak' => 60]);
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->integer('vendor_id');
            $table->integer('rute_id');
            $table->integer('customer_id')->nullable();
            $table->date('tanggal');
            $table->string('nomor_uang_jalan');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
            $table->boolean('void')->default(false);
            $table->decimal('timbangan_bongkar');
            $table->timestamps();
        });
        for ($id = 1; $id <= 30; $id++) {
            DB::table('kas_uang_jalans')->insert(['id' => $id, 'vehicle_id' => 1, 'vendor_id' => 1, 'rute_id' => 1, 'tanggal' => '2026-11-01', 'nomor_uang_jalan' => $id]);
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => $id, 'timbangan_bongkar' => 40, 'created_at' => '2026-11-01 08:00:00', 'updated_at' => '2026-11-01 08:00:00']);
        }
    }

    public function test_upah_statistics_load_in_batches_with_complete_totals_and_no_mutation_ui(): void
    {
        $this->createTransactionFixtures();
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->decimal('tonase_min');
            $table->integer('nominal');
        });
        DB::table('upah_gendongs')->insert(['vehicle_id' => 1, 'tonase_min' => 30, 'nominal' => 1000]);
        $url = '/statistik/upah-gendong?vehicle_id=1&bulan=11&tahun=2026';
        $this->get($url)->assertOk()->assertSee('300.000')->assertSee('vendorStatisticsTable')->assertDontSee('method="post"', false);
        $this->getJson($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(25, 'data')->assertJsonPath('recordsFiltered', 30)->assertJsonPath('data.0.nominal', 10000);
        $this->getJson($url.'&start=25', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(5, 'data');
    }

    public function test_upah_web_counts_a_50_km_route_as_long_route(): void
    {
        $this->createTransactionFixtures();
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->decimal('tonase_min');
            $table->integer('nominal');
        });
        DB::table('upah_gendongs')->insert(['vehicle_id' => 1, 'tonase_min' => 30, 'nominal' => 1000]);
        DB::table('rutes')->where('id', 1)->update(['jarak' => 50]);
        $url = '/statistik/upah-gendong?vehicle_id=1&bulan=11&tahun=2026';
        $this->getJson($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()
            ->assertJsonPath('data.0.over_50', 40)->assertJsonPath('data.0.under_50', 0)->assertJsonPath('data.0.kelebihan', 10)->assertJsonPath('data.0.nominal', 10000);
        $this->get($url)->assertOk()->assertSee('300.000')->assertSee('Tonase ≥50 km');
    }

    public function test_owned_transaction_audits_are_bounded_and_history_remains_read_only(): void
    {
        $this->createTransactionFixtures();
        Schema::create('kategori_filter_oli_mesins', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('limit_ritase');
        });
        DB::table('kategori_filter_oli_mesins')->insert(['id' => 1, 'nama' => 'Oli', 'limit_ritase' => 50]);
        Schema::table('filter_oli_logs', function (Blueprint $table): void {
            $table->integer('kategori_filter_oli_mesin_id')->default(1);
            $table->string('merk')->default('Merek');
            $table->integer('kondisi')->default(100);
            $table->decimal('ritase')->default(0);
            $table->timestamps();
        });
        DB::table('filter_oli_logs')->update(['created_at' => '2026-11-01 00:00:00']);
        foreach (['filter_oli_log_transaksis' => 'filter_oli_log_id', 'ban_log_transaksis' => 'ban_log_id'] as $tableName => $logKey) {
            Schema::create($tableName, function (Blueprint $table) use ($logKey): void {
                $table->id();
                $table->integer($logKey);
                $table->integer('transaksi_id');
                $table->decimal('nilai_ritase');
                $table->timestamps();
            });
            for ($id = 1; $id <= 30; $id++) {
                DB::table($tableName)->insert([$logKey => 1, 'transaksi_id' => $id, 'nilai_ritase' => 1]);
            }
        }
        foreach (['/statistik/filter-oli/transaksi-ritase/1', '/statistik/ban-luar/transaksi-ritase/1'] as $url) {
            $this->getJson($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(25, 'data')->assertJsonPath('recordsFiltered', 30);
            $this->getJson($url.'?start=25', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(5, 'data');
        }
        $this->get('/statistik/filter-oli/1/1/histori')->assertOk()->assertSee('vendorStatisticsTable')->assertDontSee('editHistory')->assertDontSee('pagination');
        $this->getJson('/statistik/filter-oli/1/1/histori', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(1, 'data');
        for ($id = 3; $id <= 31; $id++) {
            DB::table('filter_oli_logs')->insert(['id' => $id, 'vehicle_id' => 1, 'kategori_filter_oli_mesin_id' => 1, 'created_at' => '2026-11-02 00:00:00']);
        }
        $first = $this->getJson('/statistik/filter-oli/1/1/histori', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(25, 'data');
        $second = $this->getJson('/statistik/filter-oli/1/1/histori?start=25', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(5, 'data');
        $this->assertSame([], array_intersect(array_column($first->json('data'), 'id'), array_column($second->json('data'), 'id')));
        DB::table('kas_uang_jalans')->where('id', 1)->update(['vendor_id' => 2, 'vehicle_id' => 2]);
        foreach (['/statistik/filter-oli/transaksi-ritase/1', '/statistik/ban-luar/transaksi-ritase/1'] as $url) {
            $this->getJson($url, ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('recordsFiltered', 29);
        }

    }
}
