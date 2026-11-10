<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PerformUnitAllVendorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        // Fungsi MySQL YEAR() dipakai query daftar tahun; SQLite tidak memilikinya.
        $pdo = DB::connection()->getPdo();
        $year = fn ($date) => $date === null ? null : (int) substr((string) $date, 0, 4);
        method_exists($pdo, 'createFunction') ? $pdo->createFunction('YEAR', $year, 1) : $pdo->sqliteCreateFunction('YEAR', $year, 1);

        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('status')->default('aktif');
        });
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('nomor_lambung');
            $table->string('status')->default('aktif');
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->integer('jarak');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('rute_id');
            $table->date('tanggal');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kas_uang_jalan_id');
            $table->boolean('void')->default(false);
        });

        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => 'A-01', 'status' => 'aktif'],
            ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => 'B-01', 'status' => 'aktif'],
            ['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => 'A-99', 'status' => 'nonaktif'],
        ]);
        DB::table('rutes')->insert([['id' => 1, 'jarak' => 30], ['id' => 2, 'jarak' => 80]]);
        DB::table('kas_uang_jalans')->insert([
            ['id' => 1, 'vehicle_id' => 1, 'rute_id' => 1, 'tanggal' => '2026-10-05'],
            ['id' => 2, 'vehicle_id' => 1, 'rute_id' => 2, 'tanggal' => '2026-10-06'],
            ['id' => 3, 'vehicle_id' => 2, 'rute_id' => 2, 'tanggal' => '2026-10-07'],
        ]);
        DB::table('transaksis')->insert([
            ['kas_uang_jalan_id' => 1, 'void' => 0],
            ['kas_uang_jalan_id' => 2, 'void' => 0],
            ['kas_uang_jalan_id' => 3, 'void' => 0],
        ]);

        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    private function actAs(string $role, ?int $vendorId = null): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role, 'vendor_id' => $vendorId, 'password' => 'password']));
    }

    private function vehicles($response): array
    {
        return $response->viewData('data')->pluck('nomor_lambung')->all();
    }

    public function test_vendor_operational_only_sees_own_vendor_vehicles_even_when_requesting_another_vendor(): void
    {
        $this->actAs('vendor-operational', 1);

        $response = $this->get(route('statistik.perform-unit.all-vendor', ['bulan' => 10, 'tahun' => 2026, 'vendor' => 2]));

        $response->assertOk();
        $this->assertSame(['A-01'], $this->vehicles($response));
        $row = $response->viewData('data')->first();
        $this->assertSame(1, (int) $row->total_rute_pendek);
        $this->assertSame(1, (int) $row->total_rute_panjang);
        $this->assertSame(1, $response->viewData('vendor'));
        $response->assertDontSee('Filter Vendor');
        $response->assertDontSee('Vendor B');
    }

    public function test_vendor_operational_without_vendor_is_forbidden(): void
    {
        $this->actAs('vendor-operational');

        $this->get(route('statistik.perform-unit.all-vendor'))->assertForbidden();
        $this->get(route('statistik.perform-unit.all-vendor.pdf'))->assertForbidden();
    }

    public function test_admin_still_sees_all_vendors(): void
    {
        $this->actAs('admin');

        $response = $this->get(route('statistik.perform-unit.all-vendor', ['bulan' => 10, 'tahun' => 2026]));

        $response->assertOk();
        $this->assertSame(['A-01', 'B-01'], $this->vehicles($response));
        $response->assertSee('Filter Vendor');
    }

    public function test_vendor_operational_cannot_open_vendor_payment_routes(): void
    {
        $this->actAs('vendor-operational', 1);

        $this->get(route('invoice.bayar.index'))->assertForbidden();
    }
}
