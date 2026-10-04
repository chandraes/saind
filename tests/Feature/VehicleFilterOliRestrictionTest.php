<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class VehicleFilterOliRestrictionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('nomor_lambung');
            $table->string('nopol')->default('BG 1234');
            $table->string('status')->default('aktif');
            $table->integer('no_index')->default(35);
            $table->integer('tahun')->default(2020);
            $table->boolean('gps')->default(false);
            $table->unsignedBigInteger('updated_by')->nullable();
            foreach (['tanggal_pajak_stnk', 'tanggal_kir', 'tanggal_kimper', 'tanggal_sim'] as $column) {
                $table->date($column)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('perusahaan')->nullable();
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
        });
        (require database_path('migrations/2026_10_04_145713_add_pembatasan_filter_oli_to_vehicles_table.php'))->up();
    }

    private function vehicle(): Vehicle
    {
        $id = DB::table('vehicles')->insertGetId(['nomor_lambung' => '101']);

        return Vehicle::findOrFail($id);
    }

    private function loginAs(string $role): void
    {
        $this->actingAs(User::factory()->make(['id' => 5, 'role' => $role, 'password' => 'password']));
    }

    public function test_flag_defaults_to_false_for_existing_and_new_vehicles(): void
    {
        $migration = require database_path('migrations/2026_10_04_145713_add_pembatasan_filter_oli_to_vehicles_table.php');
        $migration->down();
        $existing = DB::table('vehicles')->insertGetId(['nomor_lambung' => '102']);
        $migration->up();
        $this->assertFalse(Vehicle::findOrFail($existing)->pembatasan_filter_oli);
        $this->assertFalse($this->vehicle()->pembatasan_filter_oli);
    }

    #[TestWith(['su'])]
    #[TestWith(['admin'])]
    public function test_managers_can_check_and_uncheck_only_the_requested_flag(string $role): void
    {
        $this->loginAs($role);
        $vehicle = $this->vehicle();
        $other = $this->vehicle();
        $this->patchJson(route('vehicle.pembatasan-filter-oli.update', $vehicle), ['pembatasan_filter_oli' => true, 'nopol' => 'CHANGED', 'status' => 'nonaktif'])
            ->assertOk()->assertJsonPath('pembatasan_filter_oli', true);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'pembatasan_filter_oli' => 1, 'nopol' => 'BG 1234', 'status' => 'aktif', 'updated_by' => 5]);
        $this->assertFalse($other->fresh()->pembatasan_filter_oli);
        $this->patchJson(route('vehicle.pembatasan-filter-oli.update', $vehicle), ['pembatasan_filter_oli' => false])
            ->assertOk()->assertJsonPath('pembatasan_filter_oli', false);
        $this->assertFalse($vehicle->fresh()->pembatasan_filter_oli);
    }

    public function test_ajax_vehicle_table_returns_checkbox_matching_saved_flag(): void
    {
        $this->loginAs('admin');
        $vehicle = $this->vehicle();
        $response = $this->getJson(route('vehicle.index'), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertStringContainsString('vehicle-filter-oli-restriction', $response->json('data.0.pembatasan_filter_oli'));
        $this->assertStringNotContainsString('checked', $response->json('data.0.pembatasan_filter_oli'));
        $vehicle->update(['pembatasan_filter_oli' => true]);
        $response = $this->getJson(route('vehicle.index'), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertStringContainsString('checked', $response->json('data.0.pembatasan_filter_oli'));
    }

    public function test_flag_validation_and_missing_vehicle_are_handled(): void
    {
        $this->loginAs('admin');
        $vehicle = $this->vehicle();
        $this->patchJson(route('vehicle.pembatasan-filter-oli.update', $vehicle), [])->assertUnprocessable()->assertJsonValidationErrors('pembatasan_filter_oli');
        $this->patchJson(route('vehicle.pembatasan-filter-oli.update', $vehicle), ['pembatasan_filter_oli' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors('pembatasan_filter_oli');
        $this->assertFalse($vehicle->fresh()->pembatasan_filter_oli);
        $this->patchJson(route('vehicle.pembatasan-filter-oli.update', 999999), ['pembatasan_filter_oli' => true])->assertNotFound();
    }

    #[TestWith(['user'])]
    #[TestWith(['vendor'])]
    #[TestWith(['operasional'])]
    public function test_other_roles_cannot_update_flag(string $role): void
    {
        $this->loginAs($role);
        $vehicle = $this->vehicle();
        $this->patchJson(route('vehicle.pembatasan-filter-oli.update', $vehicle), ['pembatasan_filter_oli' => true])->assertForbidden();
        $this->assertFalse($vehicle->fresh()->pembatasan_filter_oli);
    }

    public function test_guest_cannot_update_flag(): void
    {
        $vehicle = $this->vehicle();
        $this->patchJson(route('vehicle.pembatasan-filter-oli.update', $vehicle), ['pembatasan_filter_oli' => true])->assertUnauthorized();
        $this->assertFalse($vehicle->fresh()->pembatasan_filter_oli);
    }
}
