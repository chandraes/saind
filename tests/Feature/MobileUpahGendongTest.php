<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileUpahGendongTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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
            $table->date('tanggal');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
            $table->integer('void')->default(0);
            $table->decimal('timbangan_bongkar', 10, 3)->nullable();
        });
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->integer('nominal');
            $table->float('tonase_min');
            $table->string('nama_driver');
            $table->date('tanggal_masuk_driver')->nullable();
            $table->string('nama_pengurus');
            $table->date('tanggal_masuk_pengurus')->nullable();
            $table->string('no_rek');
            $table->string('bank');
            $table->string('nama_rek');
            $table->timestamps();
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'A'], ['id' => 2, 'nama' => 'B']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288', 'nopol' => 'BG 1'],
            ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '999', 'nopol' => 'BG 2'],
            ['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => '300', 'nopol' => 'BG 3'],
        ]);
        DB::table('rutes')->insert([
            ['id' => 1, 'nama' => 'Rute 60', 'jarak' => 60],
            ['id' => 2, 'nama' => 'Rute 50', 'jarak' => 50],
            ['id' => 3, 'nama' => 'Rute 49', 'jarak' => 49],
        ]);
        foreach ([1, 2] as $vehicle) {
            DB::table('upah_gendongs')->insert(['vehicle_id' => $vehicle, 'nominal' => 20000, 'tonase_min' => 30, 'nama_driver' => 'BUDI',
                'tanggal_masuk_driver' => '2025-01-02', 'nama_pengurus' => 'ARI', 'tanggal_masuk_pengurus' => '2025-02-03',
                'no_rek' => '0001', 'bank' => 'BRI', 'nama_rek' => 'BUDI']);
        }
        $trips = [
            // [id, vehicle, vendor, rute, tanggal, tonase, void]
            [1, 1, 1, 1, '2026-11-02', 34.5, 0],
            [2, 1, 1, 2, '2026-11-03', 32.0, 0],  // tepat 50 km ikut rute panjang
            [3, 1, 1, 3, '2026-11-04', 40.0, 0],  // < 50 km tidak dapat upah
            [4, 1, 1, 1, '2026-11-05', 28.0, 0],  // di bawah minimum
            [5, 1, 1, 1, '2026-11-06', 50.0, 1],  // void diabaikan
            [6, 1, 1, 1, '2026-10-30', 45.0, 0],  // bulan lain
            [7, 1, 2, 1, '2026-11-07', 45.0, 0],  // dicatat vendor lain
            [8, 2, 2, 1, '2026-11-02', 45.0, 0],  // kendaraan vendor lain
        ];
        foreach ($trips as [$id, $vehicle, $vendor, $route, $date, $tonnage, $void]) {
            DB::table('kas_uang_jalans')->insert(['id' => $id, 'vehicle_id' => $vehicle, 'vendor_id' => $vendor, 'rute_id' => $route, 'tanggal' => $date]);
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => $id, 'timbangan_bongkar' => $tonnage, 'void' => $void]);
        }
    }

    private function headers(int $vendor = 1, string $role = 'vendor-operational'): array
    {
        $user = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => $vendor]);
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => (string) $vendor,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_monthly_wage_counts_50_km_as_long_route_and_matches_web_formula(): void
    {
        $response = $this->getJson('/api/v1/vehicles/1/upah-gendong?month=11&year=2026', $this->headers())->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('meta.configured', true)
            ->assertJsonPath('meta.rate.driver_name', 'BUDI')->assertJsonPath('meta.rate.manager_name', 'ARI')
            ->assertJsonPath('meta.rate.bank', 'BRI')->assertJsonPath('meta.rate.nominal', 20000)
            ->assertJsonPath('meta.totals.excess_tonnage', 6.5)->assertJsonPath('meta.totals.amount', 130000)
            ->assertJsonPath('meta.totals.long_tonnage', 94.5)->assertJsonPath('meta.totals.short_tonnage', 40)
            ->assertJsonPath('meta.total', 4)->assertJsonPath('meta.long_route_min_km', 50);
        $rows = collect($response->json('data'))->keyBy('id');
        $this->assertSame([4.5, 90000], [$rows[1]['excess_tonnage'], $rows[1]['amount']]);
        $this->assertSame([32, 2, 40000], [$rows[2]['long_tonnage'], $rows[2]['excess_tonnage'], $rows[2]['amount']]);
        $this->assertSame([40, 0, 0], [$rows[3]['short_tonnage'], $rows[3]['excess_tonnage'], $rows[3]['amount']]);
        $this->assertSame(0, $rows[4]['amount']);
        $this->getJson('/api/v1/vehicles/1/upah-gendong?month=10&year=2026', $this->headers())->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('meta.totals.amount', 300000);
    }

    public function test_vehicle_without_rate_other_vendors_and_bad_filters(): void
    {
        $headers = $this->headers();
        $this->getJson('/api/v1/vehicles/3/upah-gendong', $headers)->assertOk()
            ->assertJsonPath('meta.configured', false)->assertJsonPath('meta.message', 'Data upah gendong kendaraan ini belum diatur admin.')
            ->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/vehicles/2/upah-gendong', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/1/upah-gendong?month=13&year=2026', $headers)->assertUnprocessable()->assertJsonValidationErrors('month');
        $this->getJson('/api/v1/vehicles/1/upah-gendong?month=11', $headers)->assertUnprocessable()->assertJsonValidationErrors('year');
        $this->getJson('/api/v1/vehicles/1/upah-gendong', array_replace($headers, ['X-Vendor-ID' => '2']))->assertConflict();
    }

    public function test_pages_hold_25_trips_and_totals_cover_the_whole_month(): void
    {
        for ($id = 100; $id < 130; $id++) {
            DB::table('kas_uang_jalans')->insert(['id' => $id, 'vehicle_id' => 1, 'vendor_id' => 1, 'rute_id' => 1, 'tanggal' => '2026-12-01']);
            DB::table('transaksis')->insert(['id' => $id, 'kas_uang_jalan_id' => $id, 'timbangan_bongkar' => 31]);
        }
        $headers = $this->headers();
        $this->getJson('/api/v1/vehicles/1/upah-gendong?month=12&year=2026', $headers)->assertOk()->assertJsonCount(25, 'data')
            ->assertJsonPath('meta.last_page', 2)->assertJsonPath('meta.totals.amount', 600000);
        $this->getJson('/api/v1/vehicles/1/upah-gendong?month=12&year=2026&page=2', $headers)->assertOk()->assertJsonCount(5, 'data');
    }
}
