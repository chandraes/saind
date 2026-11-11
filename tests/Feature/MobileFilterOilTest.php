<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileFilterOilTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->boolean('pembatasan_filter_oli')->default(false);
        });
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('nama_driver')->nullable();
            $table->string('nama_pengurus')->nullable();
        });
        Schema::create('kategori_filter_oli_mesins', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('limit_ritase');
            $table->timestamps();
        });
        Schema::create('filter_oli_logs', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->integer('kategori_filter_oli_mesin_id');
            $table->string('merk', 100);
            $table->unsignedTinyInteger('kondisi')->default(100);
            $table->decimal('ritase', 10, 1)->default(0);
            $table->unsignedInteger('limit_ritase')->default(0);
            $table->timestamps();
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('jarak');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->integer('vendor_id');
            $table->integer('rute_id');
            $table->integer('nomor_uang_jalan');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
            $table->timestamps();
        });
        Schema::create('filter_oli_log_transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('filter_oli_log_id');
            $table->integer('transaksi_id');
            $table->decimal('nilai_ritase', 4, 1);
            $table->timestamps();
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'A'], ['id' => 2, 'nama' => 'B']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288', 'status' => 'aktif', 'pembatasan_filter_oli' => true],
            ['id' => 2, 'vendor_id' => 1, 'nomor_lambung' => '300', 'status' => 'aktif', 'pembatasan_filter_oli' => false],
            ['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => '301', 'status' => 'nonaktif', 'pembatasan_filter_oli' => true],
            ['id' => 4, 'vendor_id' => 2, 'nomor_lambung' => '999', 'status' => 'aktif', 'pembatasan_filter_oli' => true],
        ]);
        DB::table('upah_gendongs')->insert(['vehicle_id' => 1, 'nama_driver' => 'Budi', 'nama_pengurus' => 'Ari']);
        DB::table('kategori_filter_oli_mesins')->insert([['id' => 1, 'nama' => 'Oli Mesin', 'limit_ritase' => 10], ['id' => 2, 'nama' => 'Filter Solar', 'limit_ritase' => 20]]);
        DB::table('filter_oli_logs')->insert([
            ['id' => 1, 'vehicle_id' => 1, 'kategori_filter_oli_mesin_id' => 1, 'merk' => 'Shell Lama', 'kondisi' => 100, 'ritase' => 12, 'limit_ritase' => 10, 'created_at' => '2026-01-01 00:00:00'],
            ['id' => 2, 'vehicle_id' => 1, 'kategori_filter_oli_mesin_id' => 1, 'merk' => 'Shell', 'kondisi' => 100, 'ritase' => 10.5, 'limit_ritase' => 10, 'created_at' => '2026-02-01 00:00:00'],
        ]);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'MIP - Port', 'jarak' => 60]);
        DB::table('kas_uang_jalans')->insert([
            ['id' => 1, 'vehicle_id' => 1, 'vendor_id' => 1, 'rute_id' => 1, 'nomor_uang_jalan' => 15],
            ['id' => 2, 'vehicle_id' => 4, 'vendor_id' => 2, 'rute_id' => 1, 'nomor_uang_jalan' => 16],
        ]);
        DB::table('transaksis')->insert([
            ['id' => 1, 'kas_uang_jalan_id' => 1, 'created_at' => '2026-02-02 08:00:00'],
            ['id' => 2, 'kas_uang_jalan_id' => 1, 'created_at' => '2026-02-03 08:00:00'],
            ['id' => 3, 'kas_uang_jalan_id' => 2, 'created_at' => '2026-02-03 09:00:00'],
        ]);
        DB::table('filter_oli_log_transaksis')->insert([
            ['id' => 1, 'filter_oli_log_id' => 2, 'transaksi_id' => 2, 'nilai_ritase' => 1],
            ['id' => 2, 'filter_oli_log_id' => 2, 'transaksi_id' => 1, 'nilai_ritase' => 1.5],
            ['id' => 3, 'filter_oli_log_id' => 2, 'transaksi_id' => 3, 'nilai_ritase' => 1],
        ]);
    }

    private function headers(string $role = 'vendor-operational'): array
    {
        $user = User::factory()->create(['username' => 'filter-'.$role, 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => 1]);
        $this->app['auth']->forgetGuards();

        return ['X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => '1',
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_vehicle_list_matches_web_eligibility_and_vendor(): void
    {
        $headers = $this->headers();
        $this->getJson('/api/v1/filter-oil/vehicles', $headers)->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1)->assertJsonPath('meta.has_more', false);
        $this->getJson('/api/v1/filter-oil/vehicles?search=999', $headers)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_vendor_sees_own_active_vehicles_without_admin_activation(): void
    {
        $headers = $this->headers('vendor');
        $this->getJson('/api/v1/filter-oil/vehicles', $headers)->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('data.0.id', 1)->assertJsonPath('data.1.id', 2);
        $this->getJson('/api/v1/vehicles/2/filter-oil-monitoring', $headers)->assertOk()->assertJsonPath('data.vehicle.id', 2);
        $this->getJson('/api/v1/vehicles/3/filter-oil-monitoring', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/2/filter-oil-monitoring', $this->headers())->assertNotFound();
        // Vendor-induk tetap butuh pengaktifan admin.
        $this->getJson('/api/v1/filter-oil/vehicles', $this->headers('vendor-induk'))->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);
    }

    public function test_monitoring_returns_latest_log_per_category_and_limit_status(): void
    {
        $headers = $this->headers();
        $this->getJson('/api/v1/vehicles/1/filter-oil-monitoring', $headers)->assertOk()
            ->assertJsonPath('data.vehicle.nama_driver', 'Budi')
            ->assertJsonPath('data.categories.0.nama', 'Oli Mesin')
            ->assertJsonPath('data.categories.0.limit_ritase', 10)
            ->assertJsonPath('data.categories.0.current_log.id', 2)
            ->assertJsonPath('data.categories.0.current_log.ritase', 10.5)
            ->assertJsonPath('data.categories.0.current_log.mencapai_limit', true)
            ->assertJsonPath('data.categories.1.current_log', null);
        foreach ([2, 3, 4] as $vehicle) {
            $this->getJson("/api/v1/vehicles/{$vehicle}/filter-oil-monitoring", $headers)->assertNotFound();
        }
        $this->getJson('/api/v1/vehicles/1/filter-oil-monitoring', array_merge($headers, ['X-Vendor-ID' => '2']))->assertConflict();
    }

    public function test_history_and_trips_are_paged_and_scoped(): void
    {
        $headers = $this->headers();
        $this->getJson('/api/v1/vehicles/1/filter-oil-categories/1/history?per_page=1', $headers)->assertOk()
            ->assertJsonPath('data.0.id', 2)->assertJsonPath('category.limit_ritase', 10)->assertJsonPath('meta.has_more', true);
        $this->getJson('/api/v1/vehicles/1/filter-oil-categories/1/history?per_page=1&page=2', $headers)->assertOk()
            ->assertJsonPath('data.0.id', 1)->assertJsonPath('meta.has_more', false);
        $this->getJson('/api/v1/vehicles/1/filter-oil-categories/99/history', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/4/filter-oil-categories/1/history', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/1/filter-oil-logs/2/trips', $headers)->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('data.0.transaksi_id', 1)
            ->assertJsonPath('data.0.nomor_uang_jalan', 15)->assertJsonPath('filter.kategori', 'Oli Mesin');
        $this->getJson('/api/v1/vehicles/1/filter-oil-logs/2/trips?per_page=1', $headers)->assertOk()->assertJsonPath('meta.has_more', true);
        $this->getJson('/api/v1/vehicles/2/filter-oil-logs/2/trips', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/1/filter-oil-logs/2/trips?page=0', $headers)->assertUnprocessable();
    }
}
