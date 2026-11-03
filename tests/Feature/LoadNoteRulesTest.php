<?php

namespace Tests\Feature;

use App\Models\LoadNoteDraft;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\LoadNoteDateRule;
use App\Services\LoadNoteRules;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;

class LoadNoteRulesTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 3)->setTime(10, 0));
        (require database_path('migrations/2026_11_03_104025_create_load_note_drafts_table.php'))->up();
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->boolean('status')->default(true);
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('customer_tagihans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('rute_id');
        });
        DB::table('vendors')->insert(['id' => 1, 'nama' => 'Vendor A']);
        DB::table('vehicles')->insert(['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288']);
        DB::table('customers')->insert(['id' => 1, 'nama' => 'PT. Mustika Indah Permai']);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'MIP (Selatan) - Port']);
        DB::table('customer_tagihans')->insert(['customer_id' => 1, 'rute_id' => 1]);
        DB::table('konfigurasis')->insert(['kode' => 'nota-muat', 'status' => 1]);
    }

    private function actor(): User
    {
        return User::factory()->create(['username' => Str::uuid()->toString(), 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
    }

    private function headersFor(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => '1',
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    private function scan(): array
    {
        return ['header_name' => 'PT Mustika lndah Permai', 'unit_code' => 'SAI288',
            'route_reference' => 'stp cpp-02 - sdj', 'stampout_date' => '2026-11-01',
            'ticket_number' => 'SJB-001', 'gross_weight' => '49.22', 'tare_weight' => '15.89'];
    }

    private function alias(): void
    {
        (require database_path('migrations/2026_11_03_194757_create_rute_note_aliases_table.php'))->up();
        DB::table('rute_note_aliases')->insert(['rute_id' => 1, 'name' => 'STP CPP-02 - SDJ', 'normalized_name' => 'STPCPP02SDJ']);
    }

    public function test_inspection_resolves_customer_exact_unit_and_route_without_writing_a_draft(): void
    {
        $this->alias();
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $this->headersFor($this->actor()))
            ->assertOk()->assertJsonPath('data.vehicle_id', 1)->assertJsonPath('data.customer_id', 1)
            ->assertJsonPath('data.rute_id', 1)->assertJsonPath('data.loaded_on', '2026-11-01')
            ->assertJsonPath('data.net_weight', '33.330')->assertJsonPath('data.date_source', 'stampout')
            ->assertJsonPath('decision.accepted', true)->assertJsonPath('decision.action', 'confirm_vehicle');
        $this->assertDatabaseCount('load_note_drafts', 0);
    }

    public static function blockedVehicles(): array
    {
        return [['status', 'proses'], ['status', 'nonaktif'], ['do_count', 2], ['do_count', 3]];
    }

    #[DataProvider('blockedVehicles')]
    public function test_vehicle_status_and_do_limit_are_enforced(string $field, mixed $value): void
    {
        DB::table('vehicles')->where('id', 1)->update([$field => $value]);
        $this->expectException(ValidationException::class);
        app(LoadNoteRules::class)->assertVehicle(Vehicle::findOrFail(1));
    }

    public function test_pending_note_blocks_vehicle_but_rejected_and_cancelled_notes_do_not(): void
    {
        $vehicle = Vehicle::findOrFail(1);
        $draft = LoadNoteDraft::factory()->forVehicle($vehicle, $this->actor())->create();
        foreach (['rejected', 'cancelled'] as $status) {
            $draft->update(['status' => $status]);
            app(LoadNoteRules::class)->assertVehicle($vehicle);
        }
        $draft->update(['status' => 'draft']);
        $this->expectException(ValidationException::class);
        app(LoadNoteRules::class)->assertVehicle($vehicle);
    }

    public function test_backend_limit_can_change_without_client_changes(): void
    {
        config(['mobile.load_note_rules.max_do_count' => 3]);
        DB::table('vehicles')->where('id', 1)->update(['do_count' => 2]);
        app(LoadNoteRules::class)->assertVehicle(Vehicle::findOrFail(1));
        $this->assertTrue(true);
    }

    public function test_backdate_obeys_existing_switch_and_server_days(): void
    {
        app(LoadNoteDateRule::class)->assertAllowed('2026-11-01');
        DB::table('konfigurasis')->update(['status' => 0]);
        app(LoadNoteDateRule::class)->assertAllowed('2026-10-01');
        DB::table('konfigurasis')->update(['status' => 1]);
        config(['mobile.load_note_rules.backdate_days' => 3]);
        app(LoadNoteDateRule::class)->assertAllowed('2026-10-31');
        $this->expectException(ValidationException::class);
        app(LoadNoteDateRule::class)->assertAllowed('2026-10-30');
    }

    public function test_ambiguous_customer_is_not_guessed(): void
    {
        $this->alias();
        DB::table('customers')->insert(['nama' => 'Mustika Indah Permai']);
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $this->headersFor($this->actor()))
            ->assertUnprocessable()->assertJsonValidationErrors('header_name');
    }

    public function test_missing_tariff_or_alias_requires_admin_reference_fix(): void
    {
        $this->alias();
        $headers = $this->headersFor($this->actor());
        $this->postJson('/api/v1/load-notes/inspect', array_replace($this->scan(), ['route_reference' => 'STP CPP-01 - SDJ']), $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('route_reference')->assertJsonPath('decision.action', 'contact_admin');
        DB::table('customer_tagihans')->delete();
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('route_reference')->assertJsonPath('decision.action', 'contact_admin');
    }

    public function test_wrong_vendor_unit_and_ocr_digit_substitutions_are_not_matched(): void
    {
        $this->alias();
        $headers = $this->headersFor($this->actor());
        foreach (['SAI28B', 'SAI289'] as $unit) {
            $this->postJson('/api/v1/load-notes/inspect', array_replace($this->scan(), ['unit_code' => $unit]), $headers)
                ->assertUnprocessable()->assertJsonValidationErrors('unit_code');
        }
    }

    public function test_inspection_is_authorized_and_rejects_expired_dates(): void
    {
        $this->alias();
        $this->postJson('/api/v1/load-notes/inspect', $this->scan())->assertUnauthorized();
        $this->postJson('/api/v1/load-notes/inspect', array_replace($this->scan(), ['stampout_date' => '2026-10-31']), $this->headersFor($this->actor()))
            ->assertUnprocessable()->assertJsonValidationErrors('stampout_date')->assertJsonPath('decision.code', 'backdate_limit');
        $user = $this->actor();
        $user->update(['role' => 'user']);
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $this->headersFor($user))->assertForbidden();
    }

    public function test_exact_customer_margin_boundary_is_accepted(): void
    {
        $this->alias();
        DB::table('customers')->where('id', 1)->update(['nama' => 'ABCDEFGHIJ']);
        DB::table('customers')->insert(['nama' => 'ABCDEFGHIK']);
        $this->postJson('/api/v1/load-notes/inspect', array_replace($this->scan(), ['header_name' => 'ABCDEFGHIJ']), $this->headersFor($this->actor()))
            ->assertOk()->assertJsonPath('data.customer_id', 1);
    }

    public function test_inactive_customer_is_not_accepted_even_with_an_exact_name(): void
    {
        $this->alias();
        DB::table('customers')->where('id', 1)->update(['status' => 0]);
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $this->headersFor($this->actor()))
            ->assertUnprocessable()->assertJsonPath('decision.code', 'customer_inactive')->assertJsonPath('decision.action', 'contact_admin');
    }

    public function test_customer_similarity_threshold_is_controlled_by_backend(): void
    {
        $this->alias();
        $headers = $this->headersFor($this->actor());
        config(['mobile.load_note_rules.customer_min_similarity' => 1.0]);
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $headers)->assertUnprocessable()->assertJsonValidationErrors('header_name');
        config(['mobile.load_note_rules.customer_min_similarity' => 0.9]);
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $headers)->assertOk();
    }

    public function test_a_unit_owned_only_by_another_vendor_cannot_be_resolved(): void
    {
        $this->alias();
        DB::table('vendors')->insert(['id' => 2, 'nama' => 'Vendor B']);
        DB::table('vehicles')->where('id', 1)->update(['vendor_id' => 2]);
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $this->headersFor($this->actor()))
            ->assertUnprocessable()->assertJsonPath('decision.code', 'unit_not_found');
    }
}
