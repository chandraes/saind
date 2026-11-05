<?php

namespace Tests\Feature;

use App\Models\LoadNoteDraft;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\LoadNoteDateRule;
use App\Services\LoadNoteRules;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;

class LoadNoteRulesTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['mobile.load_note_rules.enforce_mobile_backdate' => true]);
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
        Schema::create('vendor_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('rute_id');
            $table->integer('hk_uang_jalan')->nullable();
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

    private function submission(): array
    {
        $this->alias();
        (require database_path('migrations/2026_11_04_060303_add_matching_fields_to_load_note_drafts_table.php'))->up();
        Storage::fake('local');

        return $this->scan() + ['request_id' => Str::uuid()->toString(), 'confirmed_vehicle_id' => 1,
            'photo' => UploadedFile::fake()->image('nota.jpg', 200, 300)];
    }

    public function test_scanned_submission_persists_backend_matches_and_stampout_ignoring_client_ids(): void
    {
        $payload = $this->submission() + ['customer_id' => 999, 'rute_id' => 999, 'vehicle_id' => 999, 'loaded_on' => '2000-01-01'];
        $headers = $this->headersFor($this->actor());
        $response = $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertCreated()
            ->assertJsonPath('data.customer_id', 1)->assertJsonPath('data.rute_id', 1)
            ->assertJsonPath('data.vehicle_id', 1)->assertJsonPath('data.loaded_on', '2026-11-01')
            ->assertJsonPath('data.date_source', 'stampout')->assertJsonPath('data.net_weight', '33.330');
        $this->assertDatabaseHas('load_note_drafts', ['id' => $response->json('data.id'), 'customer_id' => 1, 'rute_id' => 1,
            'header_name' => $payload['header_name'], 'route_reference' => $payload['route_reference'], 'unit_code' => 'SAI288']);
        $this->getJson('/api/v1/load-notes/'.$response->json('data.id'), $headers)->assertOk()->assertJsonPath('data.customer_id', 1);
    }

    public function test_scanned_retry_returns_existing_note_even_after_reference_or_status_changes(): void
    {
        $payload = $this->submission();
        $headers = $this->headersFor($this->actor());
        $id = $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertCreated()->json('data.id');
        DB::table('vehicles')->where('id', 1)->update(['status' => 'proses']);
        DB::table('rute_note_aliases')->delete();
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertOk()->assertJsonPath('data.id', $id);
        $payload['route_reference'] = 'CHANGED';
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertConflict();
        $this->assertDatabaseCount('load_note_drafts', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_submission_rechecks_changed_alias_after_successful_inspection_without_storing_photo(): void
    {
        $payload = $this->submission();
        $headers = $this->headersFor($this->actor());
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $headers)->assertOk();
        DB::table('rute_note_aliases')->delete();
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertUnprocessable()->assertJsonPath('decision.code', 'route_reference_missing');
        $this->assertDatabaseCount('load_note_drafts', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }

    public function test_confirmation_cannot_silently_switch_to_another_unit(): void
    {
        $payload = $this->submission();
        $payload['confirmed_vehicle_id'] = 2;
        $this->postJson('/api/v1/load-notes/scanned', $payload, $this->headersFor($this->actor()))->assertUnprocessable()
            ->assertJsonPath('decision.code', 'vehicle_confirmation_changed');
        $this->assertDatabaseCount('load_note_drafts', 0);
    }

    public function test_trial_backdate_switch_allows_old_stampout_and_can_be_reenabled_on_backend(): void
    {
        $payload = $this->submission();
        $payload['stampout_date'] = '2026-10-01';
        $headers = $this->headersFor($this->actor());
        config(['mobile.load_note_rules.enforce_mobile_backdate' => true]);
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertUnprocessable()->assertJsonPath('decision.code', 'backdate_limit');
        config(['mobile.load_note_rules.enforce_mobile_backdate' => false]);
        $this->postJson('/api/v1/load-notes/inspect', $payload, $headers)->assertOk()->assertJsonPath('data.loaded_on', '2026-10-01');
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertCreated()->assertJsonPath('data.loaded_on', '2026-10-01');
        $this->expectException(ValidationException::class);
        app(LoadNoteDateRule::class)->assertAllowed('2026-10-01');
    }

    public function test_scanned_submission_requires_authentication_valid_ocr_and_a_photo(): void
    {
        $payload = $this->submission();
        $this->postJson('/api/v1/load-notes/scanned', $payload)->assertUnauthorized();
        unset($payload['photo'], $payload['unit_code']);
        $this->postJson('/api/v1/load-notes/scanned', $payload, $this->headersFor($this->actor()))->assertUnprocessable()
            ->assertJsonValidationErrors(['photo', 'unit_code']);
        $this->assertDatabaseCount('load_note_drafts', 0);
    }

    public function test_submission_rechecks_do_count_after_preview_and_stores_no_photo_when_blocked(): void
    {
        $payload = $this->submission();
        $headers = $this->headersFor($this->actor());
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $headers)->assertOk();
        DB::table('vehicles')->where('id', 1)->update(['do_count' => 2]);
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertUnprocessable()->assertJsonPath('decision.code', 'do_limit_reached');
        $this->assertDatabaseCount('load_note_drafts', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }

    public function test_a_new_submission_is_blocked_by_note_saved_after_preview(): void
    {
        $payload = $this->submission();
        $headers = $this->headersFor($this->actor());
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $headers)->assertOk();
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertCreated();
        $payload['request_id'] = Str::uuid()->toString();
        $payload['ticket_number'] = 'SJB-NEXT';
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertUnprocessable()->assertJsonPath('decision.code', 'pending_note');
        $this->assertDatabaseCount('load_note_drafts', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_scanned_photo_changes_cannot_reuse_an_existing_request_id(): void
    {
        $payload = $this->submission();
        $headers = $this->headersFor($this->actor());
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertCreated();
        $payload['photo'] = UploadedFile::fake()->image('different.jpg', 201, 301);
        $this->postJson('/api/v1/load-notes/scanned', $payload, $headers)->assertConflict();
        $this->assertDatabaseCount('load_note_drafts', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles());
    }
}
