<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileBatteryTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-03-11 10:00:00');
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('nama_driver')->nullable();
            $table->string('nama_pengurus')->nullable();
        });
        Schema::create('posisi_akis', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
        Schema::create('aki_logs', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->integer('posisi_aki_id');
            $table->string('merk');
            $table->string('no_seri');
            $table->integer('kondisi')->default(100);
            $table->timestamps();
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'A'], ['id' => 2, 'nama' => 'B']]);
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '999']]);
        DB::table('posisi_akis')->insert([['id' => 1, 'nama' => 'Aki 1'], ['id' => 2, 'nama' => 'Aki 2']]);
        DB::table('upah_gendongs')->insert(['vehicle_id' => 1, 'nama_driver' => 'Budi', 'nama_pengurus' => 'Ari']);
        DB::table('aki_logs')->insert([
            ['id' => 1, 'vehicle_id' => 1, 'posisi_aki_id' => 1, 'merk' => 'GS Lama', 'no_seri' => 'A', 'kondisi' => 30, 'created_at' => '2026-01-01 08:00:00'],
            ['id' => 2, 'vehicle_id' => 1, 'posisi_aki_id' => 1, 'merk' => 'Yuasa', 'no_seri' => 'B', 'kondisi' => 90, 'created_at' => '2026-03-01 08:00:00'],
            ['id' => 3, 'vehicle_id' => 2, 'posisi_aki_id' => 2, 'merk' => 'Lain', 'no_seri' => 'C', 'kondisi' => 90, 'created_at' => '2026-03-01 08:00:00'],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function headers(): array
    {
        $user = User::factory()->create(['username' => 'aki-test', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);

        return ['X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => '1',
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_maintenance_nolam_list_hides_nonaktif_and_other_vendors(): void
    {
        DB::table('vehicles')->insert([['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => '150', 'status' => 'nonaktif'],
            ['id' => 4, 'vendor_id' => 1, 'nomor_lambung' => '300', 'status' => 'aktif']]);

        $headers = $this->headers();
        $this->getJson('/api/v1/maintenance/vehicles', $headers)->assertOk()
            ->assertJsonPath('data.*.nomor_lambung', ['288', '300'])->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/maintenance/vehicles?search=30', $headers)->assertOk()
            ->assertJsonPath('data.*.id', [4]);
    }

    public function test_monitoring_returns_latest_battery_per_position_with_age(): void
    {
        $headers = $this->headers();
        $this->getJson('/api/v1/vehicles/1/battery-monitoring', $headers)->assertOk()
            ->assertJsonPath('data.vehicle.nama_driver', 'Budi')
            ->assertJsonPath('data.vehicle.pengurus', 'Ari')
            ->assertJsonPath('data.positions.0.nama', 'Aki 1')
            ->assertJsonPath('data.positions.0.current_log.id', 2)
            ->assertJsonPath('data.positions.0.current_log.merk', 'Yuasa')
            ->assertJsonPath('data.positions.0.current_log.kondisi', 90)
            ->assertJsonPath('data.positions.0.current_log.umur_hari', 10)
            ->assertJsonMissingPath('data.positions.0.current_log.no_seri')
            ->assertJsonPath('data.positions.1.current_log', null);
        $this->getJson('/api/v1/vehicles/2/battery-monitoring', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/1/battery-monitoring', array_merge($headers, ['X-Vendor-ID' => '2']))->assertConflict();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/vehicles/1/battery-monitoring', ['X-Workspace-ID' => self::WORKSPACE])->assertUnauthorized();
    }

    public function test_history_is_paged_searchable_and_reports_usage_period(): void
    {
        $headers = $this->headers();
        $this->getJson('/api/v1/vehicles/1/battery-positions/1/history?per_page=1', $headers)->assertOk()
            ->assertJsonPath('data.0.id', 2)->assertJsonPath('data.0.masih_digunakan', true)
            ->assertJsonPath('data.0.tanggal_selesai', null)->assertJsonPath('data.0.umur_hari', 10)
            ->assertJsonPath('position.nama', 'Aki 1')->assertJsonPath('meta.has_more', true);
        $this->getJson('/api/v1/vehicles/1/battery-positions/1/history?per_page=1&page=2', $headers)->assertOk()
            ->assertJsonPath('data.0.id', 1)->assertJsonPath('data.0.masih_digunakan', false)
            ->assertJsonPath('data.0.umur_hari', 59)->assertJsonPath('meta.has_more', false);
        $this->getJson('/api/v1/vehicles/1/battery-positions/1/history?search=yuasa', $headers)->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 2);
        $this->getJson('/api/v1/vehicles/1/battery-positions/2/history', $headers)->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/vehicles/2/battery-positions/2/history', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/1/battery-positions/99/history', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/1/battery-positions/1/history?per_page=101', $headers)->assertUnprocessable();
    }
}
