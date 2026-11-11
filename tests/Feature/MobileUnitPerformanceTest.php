<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileUnitPerformanceTest extends MobileApiTestCase
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
            $table->integer('rute_id');
            $table->date('tanggal');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
            $table->integer('void')->default(0);
            $table->decimal('timbangan_bongkar', 10, 3)->nullable();
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'A'], ['id' => 2, 'nama' => 'B']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288'],
            ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '999'],
        ]);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'MIP - Port', 'jarak' => 60]);
        DB::table('kas_uang_jalans')->insert([
            ['id' => 1, 'vehicle_id' => 1, 'rute_id' => 1, 'tanggal' => '2026-11-02'],
            ['id' => 2, 'vehicle_id' => 2, 'rute_id' => 1, 'tanggal' => '2026-11-02'],
        ]);
        DB::table('transaksis')->insert([
            ['kas_uang_jalan_id' => 1, 'timbangan_bongkar' => 30.22, 'void' => 0],
            ['kas_uang_jalan_id' => 1, 'timbangan_bongkar' => 90, 'void' => 1],
            ['kas_uang_jalan_id' => 2, 'timbangan_bongkar' => 50, 'void' => 0],
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

    public function test_performance_is_vendor_scoped_and_uses_unloading_tonnage(): void
    {
        $user = User::factory()->create(['username' => 'perform-unit-test', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
        $headers = $this->headersFor($user);
        $url = '/api/v1/unit-performance?start_date=2026-11-01&end_date=2026-11-03';

        $this->getJson($url.'&vendor_id=2', $headers)->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.vehicle_number', '288')
            ->assertJsonPath('data.0.long_route_count', 1)
            ->assertJsonPath('data.0.total_tonnage', 30.22)
            ->assertJsonMissing(['vehicle_number' => '999']);
        $this->getJson($url.'&search=999', $headers)->assertOk()->assertJsonCount(0, 'data');
        $this->getJson($url.'&search=288', $headers)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/unit-performance?start_date=2026-01-01&end_date=2026-03-01', $headers)->assertUnprocessable();
        $this->getJson($url, array_merge($headers, ['X-Vendor-ID' => '2']))->assertConflict();
        $this->app['auth']->forgetGuards();
        $this->getJson($url, ['X-Workspace-ID' => self::WORKSPACE])->assertUnauthorized();
    }

    public function test_performance_filters_by_month_and_year(): void
    {
        $this->travelTo('2026-12-05 09:00:00');
        $user = User::factory()->create(['username' => 'perform-month', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
        $headers = $this->headersFor($user);

        $this->getJson('/api/v1/unit-performance?month=11&year=2026', $headers)->assertOk()
            ->assertJsonPath('data.0.long_route_count', 1)
            ->assertJsonPath('meta.start_date', '2026-11-01')->assertJsonPath('meta.end_date', '2026-11-30')
            ->assertJsonPath('meta.month', 11)->assertJsonPath('meta.year', 2026)->assertJsonPath('meta.years', [2026]);
        $this->getJson('/api/v1/unit-performance?month=10&year=2026', $headers)->assertOk()->assertJsonPath('data.0.long_route_count', 0);
        $this->getJson('/api/v1/unit-performance', $headers)->assertOk()
            ->assertJsonPath('meta.start_date', '2026-12-01')->assertJsonPath('meta.end_date', '2026-12-05');
        $this->getJson('/api/v1/unit-performance?month=13&year=2026', $headers)->assertUnprocessable()->assertJsonValidationErrors('month');
        $this->getJson('/api/v1/unit-performance?month=11', $headers)->assertUnprocessable()->assertJsonValidationErrors('year');
        $this->travelBack();
    }
}
