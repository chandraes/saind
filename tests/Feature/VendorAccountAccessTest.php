<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;

/** Akun vendor boleh melihat data vendornya, tetapi tidak memproses pembayaran. */
class VendorAccountAccessTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('ban_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->timestamps();
        });
        Schema::create('aki_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->timestamps();
        });
        Schema::create('password_konfirmasis', function (Blueprint $table): void {
            $table->id();
            $table->string('password');
        });
        DB::table('password_konfirmasis')->insert(['password' => 'admin-confirm']);
        Schema::create('invoice_add_vendors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
        });
        DB::table('invoice_add_vendors')->insert(['id' => 1, 'vendor_id' => 1]);
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '202']]);
        foreach (['ban_logs', 'aki_logs'] as $table) {
            DB::table($table)->insert([['id' => 1, 'vehicle_id' => 1], ['id' => 2, 'vehicle_id' => 2], ['id' => 3, 'vehicle_id' => 1]]);
        }
        $this->actingAs(User::factory()->create(['username' => 'vendor-a', 'password' => 'secret-pass', 'role' => 'vendor', 'vendor_id' => 1]));
    }

    #[TestWith(['/transaksi/nota-bayar/1/masuk-keranjang'])]
    #[TestWith(['/transaksi/nota-bayar/1/keluar-keranjang'])]
    #[TestWith(['/transaksi/nota-bayar/1/keranjang-semua'])]
    #[TestWith(['/transaksi/nota-bayar/1/kosongkan-keranjang'])]
    #[TestWith(['/transaksi/nota-bayar/1/lanjut'])]
    #[TestWith(['/billing/nota-bayar/1/achievement'])]
    #[TestWith(['/billing/nota-bayar/1/achievement/keranjang/1/lanjut'])]
    #[TestWith(['/billing/nota-bayar/1/achievement/keranjang/1/back'])]
    public function test_vendor_cannot_fill_cart_or_process_invoices(string $url): void
    {
        $this->post($url, ['transaksi_ids' => [1], 'total_bayar' => 1, 'ppn' => 0, 'pph' => 0, 'tempo' => '2026-12-01'])->assertForbidden();
    }

    public function test_vendor_may_still_open_the_cart_and_note_pages(): void
    {
        foreach (['transaksi.nota-bayar', 'transaksi.nota-bayar.keranjang', 'billing.nota-bayar', 'billing.nota-bayar.detail-jenis', 'billing.nota-bayar.detail-jenis.keranjang'] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertSame(['GET', 'HEAD'], $route->methods(), $name);
            $this->assertContains('role:admin,user,su,asisten-user,vendor', $route->gatherMiddleware(), $name);
        }
    }

    public function test_vendor_can_only_delete_tire_history_of_own_vehicles(): void
    {
        $this->post('/per-vendor/ban-luar/histori-destroy/2', ['password' => 'secret-pass'])->assertNotFound();
        $this->assertDatabaseHas('ban_logs', ['id' => 2]);
        $this->post('/per-vendor/ban-luar/histori-destroy/1', ['password' => 'secret-pass'])->assertRedirect();
        $this->assertDatabaseMissing('ban_logs', ['id' => 1]);
    }

    public function test_vendor_cannot_open_or_print_other_vendor_kas(): void
    {
        $this->get('/kas-per-vendor/2')->assertRedirect();
        $this->get('/pritn-kas-per-vendor/2/11/2026')->assertRedirect();
    }

    /** @return array<string, array{string, string}> */
    public static function maintenanceHistories(): array
    {
        return ['ban luar' => ['ban-luar', 'ban_logs'], 'aki' => ['aki', 'aki_logs']];
    }

    #[DataProvider('maintenanceHistories')]
    public function test_vendor_edits_and_deletes_only_own_vehicle_history_with_own_password(string $report, string $table): void
    {
        $base = '/statistik/'.$report;
        // Kendaraan vendor lain: tidak ditemukan, data tetap ada.
        $this->post($base.'/histori-destroy/2', ['password' => 'secret-pass'])->assertNotFound();
        $this->patch($base.'/histori-update/2', ['password' => 'secret-pass', 'created_at' => '01-11-2026'])->assertNotFound();
        $this->assertDatabaseHas($table, ['id' => 2]);
        // Password admin bukan password akun vendor.
        $this->post($base.'/histori-destroy/1', ['password' => 'admin-confirm'])->assertSessionHas('error', 'Password salah!!');
        $this->assertDatabaseHas($table, ['id' => 1]);

        $this->patch($base.'/histori-update/3', ['password' => 'secret-pass', 'created_at' => '01-11-2026'])->assertSessionHas('success');
        $this->assertSame('2026-11-01', substr((string) DB::table($table)->where('id', 3)->value('created_at'), 0, 10));
        $this->post($base.'/histori-destroy/1', ['password' => 'secret-pass'])->assertSessionHas('success');
        $this->assertDatabaseMissing($table, ['id' => 1]);
    }

    #[DataProvider('maintenanceHistories')]
    public function test_vendor_operational_stays_read_only_and_admin_keeps_confirmation_password(string $report, string $table): void
    {
        $this->actingAs(User::factory()->create(['username' => 'vendor-ops', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]));
        $this->post('/statistik/'.$report.'/histori-destroy/1', ['password' => 'secret-pass'])->assertForbidden();
        $this->assertDatabaseHas($table, ['id' => 1]);

        $this->actingAs(User::factory()->create(['username' => 'admin-x', 'password' => 'secret-pass', 'role' => 'admin']));
        $this->post('/statistik/'.$report.'/histori-destroy/2', ['password' => 'secret-pass'])->assertSessionHas('error', 'Password salah!!');
        $this->post('/statistik/'.$report.'/histori-destroy/2', ['password' => 'admin-confirm'])->assertSessionHas('success');
        $this->assertDatabaseMissing($table, ['id' => 2]);
    }
}
