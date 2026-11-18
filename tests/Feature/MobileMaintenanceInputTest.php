<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Akun vendor mencatat ban, aki, dan filter & oli langsung dari mobile dan web (tanpa
 * otorisasi admin). Kedua jalur memakai VendorMaintenanceInput yang sama.
 */
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
        // Vendor-induk mengganti maintenance lewat Form Maintenance admin, bukan input langsung.
        $this->postJson('/api/v1/vehicles/1/battery-logs', $body, $this->headers('vendor-induk'))->assertForbidden();
        $this->assertDatabaseCount('aki_logs', 0);
    }

    /** Admin, SU, dan role seluruh vendor lain: tanpa vendor, tanpa header X-Vendor-ID. */
    private function allVendorHeaders(string $role): array
    {
        $user = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => null]);
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_admin_and_su_edit_and_delete_any_vendor_history_with_the_confirmation_password(): void
    {
        (require database_path('migrations/2023_09_20_145203_create_password_konfirmasis_table.php'))->up();
        DB::table('password_konfirmasis')->insert(['password' => 'konfirmasi-1']);
        foreach (['admin', 'su'] as $role) {
            $tire = DB::table('ban_logs')->insertGetId(['vehicle_id' => 2, 'posisi_ban_id' => 1, 'merk' => 'A', 'kondisi' => 90, 'ritase' => 0, 'created_at' => now()->subDays(2)]);
            $battery = DB::table('aki_logs')->insertGetId(['vehicle_id' => 2, 'posisi_aki_id' => 1, 'merk' => 'GS', 'kondisi' => 100, 'created_at' => now()->subDays(2)]);
            $headers = $this->allVendorHeaders($role);
            $this->assertTrue($this->getJson('/api/v1/me', $headers)->json('data.permissions.maintenance_history_edit'));
            $this->assertFalse($this->getJson('/api/v1/me', $headers)->json('data.permissions.maintenance_history'));

            $date = now()->subDays(10)->toDateString();
            $this->patchJson("/api/v1/vehicles/2/tire-logs/{$tire}", ['replaced_on' => $date, 'password' => 'secret-pass'], $headers)
                ->assertUnprocessable()->assertJsonValidationErrors(['password' => 'Password salah.']);
            $this->patchJson("/api/v1/vehicles/2/tire-logs/{$tire}", ['replaced_on' => $date], $headers)
                ->assertUnprocessable()->assertJsonValidationErrors(['password' => 'Masukkan password konfirmasi.']);
            $this->patchJson("/api/v1/vehicles/2/tire-logs/{$tire}", ['replaced_on' => $date, 'password' => 'konfirmasi-1'], $headers)->assertOk();
            $this->assertStringStartsWith($date, DB::table('ban_logs')->where('id', $tire)->value('created_at'));
            $this->patchJson("/api/v1/vehicles/2/battery-logs/{$battery}", ['replaced_on' => $date, 'password' => 'konfirmasi-1'], $headers)->assertOk();
            $this->assertStringStartsWith($date, DB::table('aki_logs')->where('id', $battery)->value('created_at'));

            // Nolam lain atau log milik nolam lain: tidak ditemukan.
            $this->deleteJson("/api/v1/vehicles/1/tire-logs/{$tire}", ['password' => 'konfirmasi-1'], $headers)->assertNotFound();
            $this->deleteJson("/api/v1/vehicles/2/tire-logs/{$tire}", ['password' => 'konfirmasi-1'], $headers)->assertOk();
            $this->deleteJson("/api/v1/vehicles/2/battery-logs/{$battery}", ['password' => 'konfirmasi-1'], $headers)->assertOk();
            $this->assertDatabaseMissing('ban_logs', ['id' => $tire]);
            $this->assertDatabaseMissing('aki_logs', ['id' => $battery]);

            // Filter & oli juga memakai password konfirmasi di mobile dan ritase dihitung ulang.
            $filter = DB::table('filter_oli_logs')->insertGetId(['vehicle_id' => 1, 'kategori_filter_oli_mesin_id' => 1, 'merk' => 'SHELL', 'kondisi' => 100, 'ritase' => 0, 'created_at' => now()->subDays(5)]);
            $this->deleteJson("/api/v1/vehicles/1/filter-oil-logs/{$filter}", [], $headers)
                ->assertUnprocessable()->assertJsonValidationErrors('password');
            $this->patchJson("/api/v1/vehicles/1/filter-oil-logs/{$filter}", ['replaced_on' => now()->subDays(40)->toDateString(), 'password' => 'konfirmasi-1'], $headers)->assertOk();
            $this->assertSame(2.5, (float) DB::table('filter_oli_logs')->where('id', $filter)->value('ritase'));
            $this->deleteJson("/api/v1/vehicles/1/filter-oil-logs/{$filter}", ['password' => 'konfirmasi-1'], $headers)->assertOk();
            $this->assertDatabaseMissing('filter_oli_logs', ['id' => $filter]);

            // Catat baru tetap hanya akun vendor.
            $this->postJson('/api/v1/vehicles/2/battery-logs', ['position_id' => 1, 'brand' => 'gs', 'condition' => 100, 'replaced_on' => now()->toDateString()], $headers)
                ->assertForbidden();
        }
    }

    public function test_other_all_vendor_roles_cannot_edit_history(): void
    {
        (require database_path('migrations/2023_09_20_145203_create_password_konfirmasis_table.php'))->up();
        DB::table('password_konfirmasis')->insert(['password' => 'konfirmasi-1']);
        $tire = DB::table('ban_logs')->insertGetId(['vehicle_id' => 2, 'posisi_ban_id' => 1, 'merk' => 'A', 'kondisi' => 90, 'ritase' => 0, 'created_at' => now()->subDays(2)]);
        foreach (['operasional', 'general-manager', 'direksi'] as $role) {
            $headers = $this->allVendorHeaders($role);
            $this->assertFalse($this->getJson('/api/v1/me', $headers)->json('data.permissions.maintenance_history_edit'));
            $this->deleteJson("/api/v1/vehicles/2/tire-logs/{$tire}", ['password' => 'konfirmasi-1'], $headers)->assertForbidden();
        }
        $this->assertDatabaseHas('ban_logs', ['id' => $tire]);
    }

    private function webUser(string $role = 'vendor'): User
    {
        $user = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => 1]);
        $this->actingAs($user);

        return $user;
    }

    public function test_web_vendor_records_tire_battery_and_filter_oil_from_statistics_pages(): void
    {
        $this->webUser();
        $date = now()->subDays(5)->toDateString();
        $this->from('/statistik/ban-luar?vehicle_id=1')->post('/statistik/ban-luar/1/ganti', ['position_id' => 1, 'source' => 'baru',
            'brand' => 'bridgestone', 'serial_number' => 'sn-1', 'condition' => 90, 'replaced_on' => $date])
            ->assertRedirect('/statistik/ban-luar?vehicle_id=1')->assertSessionHas('success');
        $this->assertSame(1.5, (float) DB::table('ban_logs')->where('merk', 'BRIDGESTONE')->value('ritase'));

        $this->post('/statistik/aki/1/ganti', ['position_id' => 1, 'brand' => 'gs', 'serial_number' => '', 'condition' => 100, 'replaced_on' => $date])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('aki_logs', ['vehicle_id' => 1, 'merk' => 'GS', 'no_seri' => null]);

        $this->post('/statistik/filter-oli/1/ganti', ['category_id' => 1, 'brand' => 'shell', 'condition' => 100, 'replaced_on' => $date])
            ->assertSessionHas('success');
        $this->assertSame(1.5, (float) DB::table('filter_oli_logs')->where('merk', 'SHELL')->value('ritase'));
    }

    public function test_web_spare_tire_swap_and_validation_errors_return_to_the_form(): void
    {
        DB::table('ban_logs')->insert([
            ['vehicle_id' => 1, 'posisi_ban_id' => 11, 'merk' => 'SEREP', 'no_seri' => 'S1', 'kondisi' => 80, 'ritase' => 4, 'created_at' => '2026-01-01'],
            ['vehicle_id' => 1, 'posisi_ban_id' => 1, 'merk' => 'LAMA', 'no_seri' => 'L1', 'kondisi' => 40, 'ritase' => 70, 'created_at' => '2026-01-01'],
        ]);
        $this->webUser();
        $this->post('/statistik/ban-luar/1/ganti', ['position_id' => 11, 'source' => 'serep', 'replaced_on' => now()->toDateString()])
            ->assertSessionHasErrors('position_id');
        $this->post('/statistik/ban-luar/1/ganti', ['position_id' => 1, 'source' => 'serep', 'replaced_on' => now()->toDateString()])
            ->assertSessionHas('success');
        $this->assertSame('LAMA', DB::table('ban_logs')->where('posisi_ban_id', 11)->orderByDesc('id')->value('merk'));

        $this->post('/statistik/aki/1/ganti', ['position_id' => 1, 'brand' => 'gs', 'condition' => 101, 'replaced_on' => now()->addDay()->toDateString()])
            ->assertSessionHasErrors(['condition', 'replaced_on']);
        $this->assertDatabaseCount('aki_logs', 0);
    }

    public function test_web_vendor_edits_and_deletes_filter_oil_history_with_account_password(): void
    {
        $this->webUser();
        $this->post('/statistik/filter-oli/1/ganti', ['category_id' => 1, 'brand' => 'shell', 'condition' => 100, 'replaced_on' => now()->subDays(5)->toDateString()]);
        $id = DB::table('filter_oli_logs')->value('id');

        $this->patch("/statistik/filter-oli/1/histori-vendor/{$id}", ['replaced_on' => now()->subDays(40)->toDateString(), 'password' => 'wrong'])
            ->assertSessionHasErrors('password');
        $this->patch("/statistik/filter-oli/1/histori-vendor/{$id}", ['replaced_on' => now()->subDays(40)->toDateString(), 'password' => 'secret-pass'])
            ->assertSessionHas('success');
        $this->assertSame(2.5, (float) DB::table('filter_oli_logs')->where('id', $id)->value('ritase'));
        $this->delete("/statistik/filter-oli/1/histori-vendor/{$id}", ['password' => 'secret-pass'])->assertSessionHas('success');
        $this->assertDatabaseMissing('filter_oli_logs', ['id' => $id]);
    }

    public function test_web_other_vendor_inactive_vehicle_and_read_only_accounts_are_rejected(): void
    {
        $body = ['position_id' => 1, 'brand' => 'gs', 'condition' => 100, 'replaced_on' => now()->toDateString()];
        $this->webUser();
        $this->post('/statistik/aki/2/ganti', $body)->assertNotFound();
        $this->post('/statistik/aki/3/ganti', $body)->assertNotFound();
        foreach (['vendor-operational', 'vendor-induk'] as $role) {
            $this->webUser($role);
            $this->post('/statistik/aki/1/ganti', $body)->assertForbidden();
            $this->post('/statistik/filter-oli/1/ganti', ['category_id' => 1] + $body)->assertForbidden();
        }
        $this->assertDatabaseCount('aki_logs', 0);
        $this->assertDatabaseCount('filter_oli_logs', 0);
    }

    public function test_web_filter_oil_history_shows_actions_only_to_vendor(): void
    {
        $this->webUser();
        $this->get('/statistik/filter-oli/1/1/histori')->assertOk()
            ->assertSee('vendorFilterEditModal', false)->assertSee('histori-vendor', false);
        $this->webUser('vendor-induk');
        $this->get('/statistik/filter-oli/1/1/histori')->assertOk()->assertDontSee('vendorFilterEditModal', false);
    }
}
