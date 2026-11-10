<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileTireMonitoringTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('nama_driver')->nullable();
            $table->string('nama_pengurus')->nullable();
        });
        Schema::create('posisi_bans', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('jenis')->nullable();
            $table->timestamps();
        });
        Schema::create('ban_logs', function (Blueprint $table): void {
            $table->id();
            $table->integer('vehicle_id');
            $table->integer('posisi_ban_id');
            $table->string('merk')->nullable();
            $table->string('no_seri')->nullable();
            $table->integer('kondisi')->nullable();
            $table->decimal('ritase', 8, 2)->nullable();
            $table->timestamps();
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'A'], ['id' => 2, 'nama' => 'B']]);
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '999'], ['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => '300']]);
        DB::table('posisi_bans')->insert([['id' => 1, 'nama' => 'Depan'], ['id' => 11, 'nama' => 'Serep']]);
        DB::table('upah_gendongs')->insert(['vehicle_id' => 1, 'nama_driver' => 'Budi', 'nama_pengurus' => 'Ari']);
        DB::table('ban_logs')->insert([
            ['id' => 1, 'vehicle_id' => 1, 'posisi_ban_id' => 1, 'merk' => 'Lama', 'no_seri' => 'A', 'kondisi' => 50, 'ritase' => 3, 'created_at' => '2026-01-01'],
            ['id' => 2, 'vehicle_id' => 1, 'posisi_ban_id' => 1, 'merk' => 'Baru', 'no_seri' => 'B', 'kondisi' => 80, 'ritase' => 7.5, 'created_at' => '2026-02-01'],
        ]);
    }

    public function test_monitoring_scopes_vehicle_and_returns_latest_log_and_empty_positions(): void
    {
        $user = User::factory()->create(['username' => 'tire-test', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
        $headers = ['X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => '1',
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
        $this->getJson('/api/v1/vehicles/1/tire-monitoring', $headers)->assertOk()
            ->assertJsonPath('data.vehicle.nama_driver', 'Budi')
            ->assertJsonPath('data.vehicle.pengurus', 'Ari')
            ->assertJsonPath('data.positions.0.current_log.id', 2)
            ->assertJsonPath('data.positions.0.current_log.merk', 'Baru')
            ->assertJsonPath('data.positions.0.current_log.ritase', 7.5)
            ->assertJsonPath('data.positions.1.current_log', null);
        $this->getJson('/api/v1/vehicles/3/tire-monitoring', $headers)->assertOk()->assertJsonPath('data.positions.0.current_log', null);
        $this->getJson('/api/v1/vehicles/2/tire-monitoring', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/1/tire-monitoring', array_merge($headers, ['X-Vendor-ID' => '2']))->assertConflict();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/vehicles/1/tire-monitoring', ['X-Workspace-ID' => self::WORKSPACE])->assertUnauthorized();
    }
}
