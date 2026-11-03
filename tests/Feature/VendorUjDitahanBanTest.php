<?php

namespace Tests\Feature;

use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class VendorUjDitahanBanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['vendors', 'rutes', 'vehicles', 'drivers', 'transaksis'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name): void {
                $table->id();
                $table->string('nama')->default('Test');
                if ($name === 'rutes') {
                    $table->bigInteger('uang_jalan')->default(100000);
                    $table->bigInteger('uj_ditahan')->default(10000);
                }
                $table->timestamps();
            });
        }
        Schema::create('vendor_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('rute_id');
            $table->bigInteger('hk_uang_jalan')->nullable();
            $table->bigInteger('uj_ditahan')->default(0);
            $table->timestamps();
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('rutes')->insert([['id' => 1], ['id' => 2]]);
        DB::table('vehicles')->insert(['id' => 1]);
        DB::table('vendor_uang_jalans')->insert(['vendor_id' => 1, 'rute_id' => 1, 'hk_uang_jalan' => 100000, 'uj_ditahan' => 15000]);
        (require database_path('migrations/2026_08_07_071658_create_uj_ditahans_table.php'))->up();
        DB::table('uj_ditahans')->insert(['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'total_masuk' => 50000, 'saldo' => 50000]);
        foreach (['2026_10_29_102252_add_uj_ditahan_ban_to_vendor_uang_jalans_table.php', '2026_10_29_102252_create_uj_ditahan_bans_table.php', '2026_10_29_102253_create_uj_ditahan_ban_details_table.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    private function payload(): array
    {
        return ['vendor_id' => 1, 'rute_id' => [1, 2], 'uang_jalan' => ['100000', '200000'], 'uj_ditahan' => ['15000', '25000'], 'uj_ditahan_ban' => ['20000', '30000']];
    }

    public function test_migration_keeps_existing_sparepart_amount_and_defaults_new_ban_amount_to_zero(): void
    {
        $this->assertTrue(Schema::hasColumn('vendor_uang_jalans', 'uj_ditahan_ban'));
        $this->assertDatabaseHas('vendor_uang_jalans', ['vendor_id' => 1, 'rute_id' => 1, 'hk_uang_jalan' => 100000, 'uj_ditahan' => 15000, 'uj_ditahan_ban' => 0]);
        $this->assertTrue(Schema::hasTable('uj_ditahan_bans'));
        $this->assertTrue(Schema::hasTable('uj_ditahan_ban_details'));
        $this->assertDatabaseHas('uj_ditahans', ['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'saldo' => 50000]);
        $this->assertDatabaseCount('uj_ditahan_bans', 0);
    }

    public function test_update_saves_separate_ban_and_sparepart_amounts_without_changing_other_vendor(): void
    {
        DB::table('vendor_uang_jalans')->insert(['vendor_id' => 2, 'rute_id' => 1, 'hk_uang_jalan' => 50000, 'uj_ditahan' => 5000]);
        $this->post(route('uj.vendor.uang-jalan.update', 1), $this->payload())->assertSessionHas('success');
        $this->assertDatabaseHas('vendor_uang_jalans', ['vendor_id' => 1, 'rute_id' => 1, 'uj_ditahan' => 15000, 'uj_ditahan_ban' => 20000]);
        $this->assertDatabaseHas('vendor_uang_jalans', ['vendor_id' => 1, 'rute_id' => 2, 'uj_ditahan' => 25000, 'uj_ditahan_ban' => 30000]);
        $this->assertDatabaseHas('vendor_uang_jalans', ['vendor_id' => 2, 'hk_uang_jalan' => 50000, 'uj_ditahan' => 5000, 'uj_ditahan_ban' => 0]);
    }

    public function test_create_saves_ban_amount_and_does_not_append_duplicate_agreements(): void
    {
        $data = $this->payload();
        $data['vendor_id'] = 2;
        $this->post(route('uj.vendor.uang-jalan.store'), $data)->assertSessionHas('success');
        $this->assertDatabaseHas('vendor_uang_jalans', ['vendor_id' => 2, 'rute_id' => 1, 'uj_ditahan_ban' => 20000]);
        $this->post(route('uj.vendor.uang-jalan.store'), $data)->assertSessionHasErrors('vendor_id');
        $this->assertSame(2, DB::table('vendor_uang_jalans')->where('vendor_id', 2)->count());
    }

    #[TestWith([-1])]
    #[TestWith(['1.000'])]
    #[TestWith(['1,5'])]
    #[TestWith(['100abc'])]
    public function test_invalid_ban_nominal_is_rejected_before_existing_agreements_are_changed(mixed $value): void
    {
        $data = $this->payload();
        $data['uj_ditahan_ban'][0] = $value;
        $this->post(route('uj.vendor.uang-jalan.update', 1), $data)->assertSessionHasErrors('uj_ditahan_ban.0');
        $this->assertDatabaseHas('vendor_uang_jalans', ['vendor_id' => 1, 'rute_id' => 1, 'uj_ditahan' => 15000]);
        $this->assertSame(1, DB::table('vendor_uang_jalans')->where('vendor_id', 1)->count());
    }

    public function test_missing_or_duplicate_route_amounts_and_wrong_vendor_are_rejected(): void
    {
        $data = $this->payload();
        unset($data['uj_ditahan_ban'][1]);
        $this->post(route('uj.vendor.uang-jalan.update', 1), $data)->assertSessionHasErrors('uj_ditahan_ban');
        $data = $this->payload();
        $data['rute_id'] = [1, 1];
        $this->post(route('uj.vendor.uang-jalan.update', 1), $data)->assertSessionHasErrors('rute_id.0');
        $data = $this->payload();
        $data['vendor_id'] = 2;
        $this->post(route('uj.vendor.uang-jalan.update', 1), $data)->assertSessionHasErrors('vendor_id');
    }

    public function test_forms_show_both_funds_and_separate_raw_currency_fields(): void
    {
        foreach (['uj.vendor.uang-jalan' => 1, 'uj.vendor.uang-jalan.edit' => 1] as $route => $id) {
            $this->get(route($route, $id))->assertOk()->assertSee('UJ Ditahan Sparepart')->assertSee('UJ Ditahan Ban')->assertSee('name="uj_ditahan_ban[0]"', false)->assertSee('data-vendor-money', false)->assertSee('table-success', false);
        }
    }

    public function test_non_admin_cannot_modify_agreements(): void
    {
        $this->actingAs(User::factory()->make(['id' => 2, 'role' => 'user', 'password' => 'password']));
        $this->post(route('uj.vendor.uang-jalan.update', 1), $this->payload())->assertForbidden();
    }

    public function test_failed_replacement_rolls_back_original_vendor_amounts(): void
    {
        DB::unprepared("CREATE TRIGGER reject_vendor_uj BEFORE INSERT ON vendor_uang_jalans BEGIN SELECT RAISE(ABORT, 'simulated failure'); END");
        $this->post(route('uj.vendor.uang-jalan.update', 1), $this->payload())->assertSessionHas('error');
        $this->assertDatabaseCount('vendor_uang_jalans', 1);
        $this->assertDatabaseHas('vendor_uang_jalans', ['vendor_id' => 1, 'hk_uang_jalan' => 100000, 'uj_ditahan' => 15000, 'uj_ditahan_ban' => 0]);
    }

    public function test_ban_monthly_accounts_and_details_are_separate_from_sparepart(): void
    {
        $vehicle = Vehicle::findOrFail(1);
        $first = UjDitahanBan::factory()->for($vehicle)->create(['bulan' => 9, 'tahun' => 2026, 'total_masuk' => 30000, 'saldo' => 30000]);
        $second = UjDitahanBan::factory()->for($vehicle)->create(['bulan' => 10, 'tahun' => 2026]);
        $detail = UjDitahanBanDetail::factory()->for($first, 'ujDitahanBan')->create(['nominal' => 30000]);
        $this->assertSame($first->id, $detail->ujDitahanBan->id);
        $this->assertSame(1, $first->details()->count());
        $this->assertSame(0, $second->details()->count());
        $this->assertSame(1, $first->vehicle->id);
        $this->assertDatabaseHas('uj_ditahans', ['vehicle_id' => 1, 'bulan' => 9, 'saldo' => 50000]);
        $this->assertSame(30000, $first->fresh()->saldo);
    }

    public function test_one_ban_balance_per_vehicle_and_month_is_enforced(): void
    {
        $vehicle = Vehicle::findOrFail(1);
        UjDitahanBan::factory()->for($vehicle)->create(['bulan' => 9, 'tahun' => 2026]);
        $this->expectException(UniqueConstraintViolationException::class);
        UjDitahanBan::factory()->for($vehicle)->create(['bulan' => 9, 'tahun' => 2026]);
    }

    public function test_vendor_modal_displays_separate_sparepart_and_ban_amounts(): void
    {
        DB::table('vendor_uang_jalans')->where('vendor_id', 1)->update(['uj_ditahan_ban' => 27000]);
        $vendor = Vendor::with('vendor_uang_jalan.rute')->findOrFail(1);
        $this->view('database.vendor.partials.uang_jalan_btn', ['d' => $vendor])
            ->assertSee('UJ Ditahan Sparepart')->assertSee('UJ Ditahan Ban')
            ->assertSee('15.000')->assertSee('27.000')->assertSee('100.000');
    }
}
