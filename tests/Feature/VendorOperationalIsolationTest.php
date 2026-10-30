<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureVendorOperationalVehicle;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class VendorOperationalIsolationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
        });
        DB::table('vendors')->insert(['id' => 1]);
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
        });
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1], ['id' => 2, 'vendor_id' => 2]]);
        $this->actingAs(User::factory()->make(['password' => 'test-password', 'id' => 1, 'role' => 'vendor-operational', 'vendor_id' => 1]));
    }

    #[TestWith(['/vendor-operational/per-vendor/upah-gendong?vehicle_id=2'])]
    #[TestWith(['/vendor-operational/per-vendor/maintenance-vehicle?vehicle_id=2'])]
    #[TestWith(['/vendor-operational/per-vendor/maintenance-vehicle/print?vehicle_id=2'])]
    #[TestWith(['/vendor-operational/per-vendor/ban-luar/2/1/histori'])]
    #[TestWith(['/vendor-operational/per-vendor/ban-luar/histori-data?vehicle=2&posisi=1'])]
    public function test_other_vendor_vehicle_is_not_accessible(string $url): void
    {
        $this->getJson($url)->assertNotFound();
    }

    public function test_conflicting_vehicle_parameters_cannot_bypass_vendor_scope(): void
    {
        $this->getJson('/vendor-operational/per-vendor/ban-luar/histori-data?vehicle_id=1&vehicle=2&posisi=1',
            ['X-Requested-With' => 'XMLHttpRequest'])->assertNotFound();
    }

    public function test_other_vendor_odometer_write_is_rejected_before_validation(): void
    {
        $this->postJson('/vendor-operational/per-vendor/maintenance-vehicle/store-odo', ['vehicle_id' => 2])->assertNotFound();
    }

    public function test_owned_vehicle_history_remains_accessible(): void
    {
        Schema::create('ban_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('posisi_ban_id');
            $table->timestamps();
        });
        $this->getJson('/vendor-operational/per-vendor/ban-luar/histori-data?vehicle=1&posisi=1&length=10',
            ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_assigned_vendor_request_without_vehicle_reaches_next_handler(): void
    {
        $request = Request::create('/vendor-operational/perform-unit-pervendor');
        $request->setUserResolver(fn () => auth()->user());
        $response = (new EnsureVendorOperationalVehicle)->handle($request,
            fn () => response('allowed'));
        $this->assertSame('allowed', $response->getContent());
    }

    public function test_unassigned_vendor_cannot_open_dashboard(): void
    {
        $this->actingAs(User::factory()->make(['password' => 'test-password', 'id' => 1, 'role' => 'vendor-operational', 'vendor_id' => null]));
        $this->getJson('/vendor-operational/perform-unit-pervendor')->assertForbidden();
    }
}
