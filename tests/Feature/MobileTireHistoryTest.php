<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileTireHistoryTest extends MobileTireMonitoringTest
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
            $table->integer('nomor_uang_jalan');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
        });
        Schema::create('ban_log_transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('ban_log_id');
            $table->integer('transaksi_id')->nullable();
            $table->decimal('nilai_ritase', 4, 1);
        });
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'MIP - Port', 'jarak' => 60]);
        DB::table('kas_uang_jalans')->insert(['id' => 1, 'vehicle_id' => 1, 'vendor_id' => 1, 'rute_id' => 1, 'tanggal' => '2026-01-10', 'nomor_uang_jalan' => 15]);
        for ($i = 1; $i <= 3; $i++) {
            DB::table('transaksis')->insert(['id' => $i, 'kas_uang_jalan_id' => 1]);
            DB::table('ban_log_transaksis')->insert(['id' => $i, 'ban_log_id' => $i === 3 ? 2 : 1, 'transaksi_id' => $i, 'nilai_ritase' => 1]);
        }
    }

    public function test_history_and_trips_are_paged_and_scoped_to_exact_log(): void
    {
        DB::table('ban_logs')->insert(['id' => 3, 'vehicle_id' => 1, 'posisi_ban_id' => 11, 'merk' => 'Lama', 'no_seri' => 'A', 'kondisi' => 50, 'ritase' => 3, 'created_at' => '2026-03-01']);
        $user = User::factory()->create(['username' => 'tire-history', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
        $headers = ['X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => '1', 'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
        $this->getJson('/api/v1/vehicles/1/tire-positions/1/history?per_page=1', $headers)->assertOk()->assertJsonPath('data.0.id', 2)->assertJsonPath('meta.has_more', true);
        $this->getJson('/api/v1/vehicles/1/tire-positions/1/history?per_page=1&page=2', $headers)->assertOk()->assertJsonPath('data.0.id', 1)->assertJsonPath('meta.has_more', false);
        $this->getJson('/api/v1/vehicles/1/tire-positions/11/history', $headers)->assertOk()->assertJsonPath('data.0.id', 3);
        $this->getJson('/api/v1/vehicles/3/tire-positions/11/history', $headers)->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/vehicles/1/tire-logs/3/trips', $headers)->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/vehicles/1/tire-logs/1/trips?per_page=1', $headers)->assertOk()->assertJsonPath('data.0.transaksi_id', 2)->assertJsonPath('data.0.nomor_uang_jalan', 15)->assertJsonPath('meta.has_more', true);
        $this->getJson('/api/v1/vehicles/1/tire-logs/1/trips?per_page=1&page=2', $headers)->assertOk()->assertJsonPath('data.0.transaksi_id', 1)->assertJsonPath('meta.has_more', false);
        $this->getJson('/api/v1/vehicles/1/tire-logs/2/trips', $headers)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.transaksi_id', 3);
        $this->getJson('/api/v1/vehicles/2/tire-logs/1/trips', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/3/tire-logs/1/trips', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/1/tire-positions/999/history', $headers)->assertNotFound();
        $this->getJson('/api/v1/vehicles/1/tire-logs/1/trips', array_merge($headers, ['X-Vendor-ID' => '2']))->assertConflict();
        $this->getJson('/api/v1/vehicles/1/tire-logs/1/trips?page=0', $headers)->assertUnprocessable();
        $this->getJson('/api/v1/vehicles/1/tire-logs/1/trips?per_page=101', $headers)->assertUnprocessable();
    }
}
