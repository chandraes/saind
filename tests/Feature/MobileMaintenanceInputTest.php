<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Akun vendor mencatat ban, aki, dan filter & oli langsung dari mobile (tanpa otorisasi admin). */
class MobileMaintenanceInputTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->decimal('jarak', 10, 1);
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id');
            $table->foreignId('rute_id');
            $table->integer('nomor_uang_jalan')->default(1);
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kas_uang_jalan_id');
            $table->boolean('void')->default(false);
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        foreach (['posisi_bans', 'posisi_akis'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama');
            });
        }
        Schema::create('ban_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id');
            $table->foreignId('posisi_ban_id');
            $table->string('merk')->nullable();
            $table->string('no_seri')->nullable();
            $table->integer('kondisi')->nullable();
            $table->decimal('ritase', 10, 1)->default(0);
            $table->timestamps();
        });
        Schema::create('ban_log_transaksis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ban_log_id');
            $table->foreignId('transaksi_id');
            $table->decimal('nilai_ritase', 5, 1);
            $table->timestamps();
        });
        Schema::create('aki_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id');
            $table->foreignId('posisi_aki_id');
            $table->string('merk');
            $table->string('no_seri')->nullable();
            $table->integer('kondisi');
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_04_145713_add_pembatasan_filter_oli_to_vehicles_table.php'))->up();
        foreach ([
            '2026_10_04_094812_create_kategori_filter_oli_mesins_table.php',
            '2026_10_04_103058_create_filter_oli_logs_table.php',
            '2026_10_04_103101_create_filter_oli_ganti_invoices_table.php',
            '2026_10_04_103102_create_filter_oli_ganti_invoice_details_table.php',
            '2026_10_04_113741_create_filter_oli_log_transaksis_table.php',
            '2026_10_07_191623_drop_limit_ritase_from_filter_oli_logs_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101', 'status' => 'aktif'],
            ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '202', 'status' => 'aktif'],
            ['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => '303', 'status' => 'nonaktif'],
        ]);
        DB::table('posisi_bans')->insert([['id' => 1, 'nama' => 'Depan Kiri'], ['id' => 11, 'nama' => 'Serep']]);
        DB::table('posisi_akis')->insert(['id' => 1, 'nama' => 'Aki 1']);
        DB::table('kategori_filter_oli_mesins')->insert(['id' => 1, 'nama' => 'Oli Mesin', 'limit_ritase' => 100]);
        DB::table('rutes')->insert([['id' => 1, 'nama' => 'Jauh', 'jarak' => 60], ['id' => 2, 'nama' => 'Dekat', 'jarak' => 20]]);
        DB::table('kas_uang_jalans')->insert([['id' => 1, 'vehicle_id' => 1, 'rute_id' => 1], ['id' => 2, 'vehicle_id' => 1, 'rute_id' => 2]]);
        DB::table('transaksis')->insert([
            ['id' => 1, 'kas_uang_jalan_id' => 1, 'void' => 0, 'created_at' => now()->subDays(3)],
            ['id' => 2, 'kas_uang_jalan_id' => 2, 'void' => 0, 'created_at' => now()->subDays(2)],
            ['id' => 3, 'kas_uang_jalan_id' => 1, 'void' => 1, 'created_at' => now()->subDay()],
            ['id' => 4, 'kas_uang_jalan_id' => 1, 'void' => 0, 'created_at' => now()->subDays(30)],
        ]);
    }

    private function headers(string $role = 'vendor'): array
    {
        $user = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => 1]);
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => '1',
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_new_tire_counts_trips_since_replacement_date(): void
    {
        $date = now()->subDays(5)->toDateString();
        $this->postJson('/api/v1/vehicles/1/tire-logs', ['position_id' => 1, 'source' => 'baru', 'brand' => ' bridgestone ',
            'serial_number' => 'sn-1', 'condition' => 90, 'replaced_on' => $date], $this->headers())->assertCreated();

        $log = DB::table('ban_logs')->sole();
        $this->assertSame(['BRIDGESTONE', 'SN-1', 90, 1.5], [$log->merk, $log->no_seri, (int) $log->kondisi, (float) $log->ritase]);
        $this->assertSame([1, 2], DB::table('ban_log_transaksis')->orderBy('transaksi_id')->pluck('transaksi_id')->all());
    }

    public function test_spare_tire_swaps_with_current_tire(): void
    {
        DB::table('ban_logs')->insert([
            ['vehicle_id' => 1, 'posisi_ban_id' => 11, 'merk' => 'SEREP', 'no_seri' => 'S1', 'kondisi' => 80, 'ritase' => 4, 'created_at' => '2026-01-01'],
            ['vehicle_id' => 1, 'posisi_ban_id' => 1, 'merk' => 'LAMA', 'no_seri' => 'L1', 'kondisi' => 40, 'ritase' => 70, 'created_at' => '2026-01-01'],
        ]);
        $headers = $this->headers();
        $this->postJson('/api/v1/vehicles/1/tire-logs', ['position_id' => 11, 'source' => 'serep', 'replaced_on' => now()->toDateString()], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('position_id');
        $this->postJson('/api/v1/vehicles/1/tire-logs', ['position_id' => 1, 'source' => 'serep', 'replaced_on' => now()->toDateString()], $headers)
            ->assertCreated();

        $this->assertSame('SEREP', DB::table('ban_logs')->where('posisi_ban_id', 1)->orderByDesc('id')->value('merk'));
        $this->assertSame('LAMA', DB::table('ban_logs')->where('posisi_ban_id', 11)->orderByDesc('id')->value('merk'));
    }

    public function test_battery_and_filter_oil_are_recorded_and_validated(): void
    {
        $headers = $this->headers();
        $this->postJson('/api/v1/vehicles/1/battery-logs', ['position_id' => 1, 'brand' => 'gs', 'condition' => 100, 'replaced_on' => now()->toDateString()], $headers)
            ->assertCreated();
        $this->assertDatabaseHas('aki_logs', ['vehicle_id' => 1, 'merk' => 'GS', 'no_seri' => null]);
        $this->postJson('/api/v1/vehicles/1/battery-logs', ['position_id' => 1, 'brand' => 'gs', 'condition' => 101, 'replaced_on' => now()->addDay()->toDateString()], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors(['condition', 'replaced_on']);

        $this->postJson('/api/v1/vehicles/1/filter-oil-logs', ['category_id' => 1, 'brand' => 'shell', 'condition' => 100, 'replaced_on' => now()->subDays(5)->toDateString()], $headers)
            ->assertCreated();
        $log = DB::table('filter_oli_logs')->sole();
        $this->assertSame('SHELL', $log->merk);
        $this->assertSame(1.5, (float) $log->ritase);
    }

    public function test_vendor_edits_and_deletes_filter_oil_history_and_ritase_is_recomputed(): void
    {
        $headers = $this->headers();
        $id = $this->postJson('/api/v1/vehicles/1/filter-oil-logs', ['category_id' => 1, 'brand' => 'shell', 'condition' => 100, 'replaced_on' => now()->subDays(5)->toDateString()], $headers)
            ->json('data.id');
        $this->patchJson("/api/v1/vehicles/1/filter-oil-logs/{$id}", ['replaced_on' => now()->subDays(40)->toDateString(), 'password' => 'wrong'], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->patchJson("/api/v1/vehicles/1/filter-oil-logs/{$id}", ['replaced_on' => now()->subDays(40)->toDateString(), 'password' => 'secret-pass'], $headers)
            ->assertOk();
        $this->assertSame(2.5, (float) DB::table('filter_oli_logs')->where('id', $id)->value('ritase'));
        $this->deleteJson("/api/v1/vehicles/1/filter-oil-logs/{$id}", ['password' => 'secret-pass'], $headers)->assertOk();
        $this->assertDatabaseMissing('filter_oli_logs', ['id' => $id]);
    }

    public function test_other_vendor_inactive_vehicle_and_read_only_account_are_rejected(): void
    {
        $body = ['position_id' => 1, 'brand' => 'gs', 'condition' => 100, 'replaced_on' => now()->toDateString()];
        $this->postJson('/api/v1/vehicles/2/battery-logs', $body, $this->headers())->assertNotFound();
        $this->postJson('/api/v1/vehicles/3/battery-logs', $body, $this->headers())->assertNotFound();
        $this->postJson('/api/v1/vehicles/1/battery-logs', $body, $this->headers('vendor-operational'))->assertForbidden();
        $this->assertDatabaseCount('aki_logs', 0);
    }
}
