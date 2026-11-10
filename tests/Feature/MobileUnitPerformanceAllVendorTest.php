<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileUnitPerformanceAllVendorTest extends MobileApiTestCase
{
    private const URL = '/api/v1/unit-performance/all-vendor';

    protected function setUp(): void
    {
        parent::setUp();
        // Fungsi MySQL YEAR() dipakai query daftar tahun; SQLite tidak memilikinya.
        $pdo = DB::connection()->getPdo();
        $yearOf = fn ($date) => $date === null ? null : (int) substr((string) $date, 0, 4);
        method_exists($pdo, 'createFunction') ? $pdo->createFunction('YEAR', $yearOf, 1) : $pdo->sqliteCreateFunction('YEAR', $yearOf, 1);

        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->integer('jarak');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->integer('rute_id');
            $table->date('tanggal');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
            $table->integer('void')->default(0);
        });
        DB::table('vendors')->insert([
            ['id' => 1, 'nama' => 'Vendor A', 'status' => 'aktif'],
            ['id' => 2, 'nama' => 'Vendor B', 'status' => 'aktif'],
        ]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => 'A-01', 'status' => 'aktif'],
            ['id' => 2, 'vendor_id' => 1, 'nomor_lambung' => 'A-02', 'status' => 'aktif'],
            ['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => 'A-99', 'status' => 'nonaktif'],
            ['id' => 4, 'vendor_id' => 2, 'nomor_lambung' => 'B-01', 'status' => 'aktif'],
        ]);
        DB::table('rutes')->insert([
            ['id' => 1, 'nama' => 'Pendek', 'jarak' => 30],
            ['id' => 2, 'nama' => 'Batas', 'jarak' => 50],
        ]);
        DB::table('kas_uang_jalans')->insert([
            ['id' => 1, 'vehicle_id' => 1, 'rute_id' => 1, 'tanggal' => '2026-10-05'],
            ['id' => 2, 'vehicle_id' => 1, 'rute_id' => 2, 'tanggal' => '2026-10-06'],
            ['id' => 3, 'vehicle_id' => 1, 'rute_id' => 1, 'tanggal' => '2026-09-30'],
            ['id' => 4, 'vehicle_id' => 4, 'rute_id' => 2, 'tanggal' => '2026-10-07'],
        ]);
        DB::table('transaksis')->insert([
            ['kas_uang_jalan_id' => 1, 'void' => 0],
            ['kas_uang_jalan_id' => 2, 'void' => 0],
            ['kas_uang_jalan_id' => 3, 'void' => 0],
            ['kas_uang_jalan_id' => 4, 'void' => 0],
        ]);
    }

    private function headersFor(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return [
            'Accept' => 'application/json',
            'X-Workspace-ID' => self::WORKSPACE,
            'X-Vendor-ID' => (string) $user->vendor_id,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken,
        ];
    }

    public function test_all_vendor_performance_lists_only_own_active_vehicles_for_the_month(): void
    {
        $user = User::factory()->create(['username' => 'all-vendor-test', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
        $headers = $this->headersFor($user);

        $this->getJson(self::URL.'?month=10&year=2026&vendor=2&vendor_id=2', $headers)->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.vehicle_number', 'A-01')
            ->assertJsonPath('data.0.vendor_name', 'Vendor A')
            ->assertJsonPath('data.0.short_route_count', 1)
            ->assertJsonPath('data.0.long_route_count', 1)
            ->assertJsonPath('data.0.total_route_count', 2)
            ->assertJsonPath('data.1.vehicle_number', 'A-02')
            ->assertJsonPath('data.1.total_route_count', 0)
            ->assertJsonPath('meta.month', 10)
            ->assertJsonPath('meta.totals.vehicles', 2)
            ->assertJsonPath('meta.totals.total_route_count', 2)
            ->assertJsonMissing(['vehicle_number' => 'B-01'])
            ->assertJsonMissing(['vehicle_number' => 'A-99']);
    }

    public function test_all_vendor_performance_rejects_invalid_period_and_foreign_vendor_header(): void
    {
        $user = User::factory()->create(['username' => 'all-vendor-guard', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
        $headers = $this->headersFor($user);

        $this->getJson(self::URL.'?month=13&year=2026', $headers)->assertUnprocessable();
        $this->getJson(self::URL.'?month=10&year=2026', array_merge($headers, ['X-Vendor-ID' => '2']))->assertConflict();
        $this->app['auth']->forgetGuards();
        $this->getJson(self::URL, ['X-Workspace-ID' => self::WORKSPACE])->assertUnauthorized();
    }
}
