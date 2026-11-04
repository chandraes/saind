<?php

namespace Tests\Feature;

use App\Models\LoadNoteDraft;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\LoadNoteReviewService;
use App\Services\LoadNoteRules;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;

class LoadNoteReviewTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        foreach (['customers', 'rutes'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama');
                $table->boolean('status')->default(true);
            });
        }
        foreach (['customer_tagihans', 'customer_rute'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('rute_id');
            });
        }
        Schema::create('vendor_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('rute_id');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
        });
        foreach (['2026_11_03_104025_create_load_note_drafts_table.php', '2026_11_04_060303_add_matching_fields_to_load_note_drafts_table.php', '2026_11_04_061342_add_review_lifecycle_to_load_note_drafts.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '289']]);
        DB::table('customers')->insert(['id' => 1, 'nama' => 'PT Mustika Indah Permai']);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'MIP Selatan - Port']);
        foreach (['customer_tagihans', 'customer_rute'] as $name) {
            DB::table($name)->insert(['customer_id' => 1, 'rute_id' => 1]);
        }
        DB::table('vendor_uang_jalans')->insert(['vendor_id' => 1, 'rute_id' => 1]);
    }

    private function actor(string $role = 'admin'): User
    {
        return User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => 1]);
    }

    private function note(): LoadNoteDraft
    {
        return LoadNoteDraft::factory()->forVehicle(Vehicle::findOrFail(1), $this->actor('vendor-operational'))->create(['customer_id' => 1, 'rute_id' => 1, 'date_source' => 'stampout']);
    }

    private function correction(): array
    {
        return ['revision' => 0, 'reason' => 'Koreksi nomor tiket sesuai foto', 'vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 1,
            'ticket_number' => 'SJB-CORRECTED', 'loaded_on' => today()->toDateString(), 'gross_weight' => '45.54', 'tare_weight' => '15.32', 'notes' => 'Diperiksa'];
    }

    public static function reviewers(): array
    {
        return [['admin'], ['su'], ['user']];
    }

    #[DataProvider('reviewers')]
    public function test_reviewers_can_correct_a_note_with_reason_and_audit(string $role): void
    {
        $note = $this->note();
        $this->actingAs($reviewer = $this->actor($role))->patchJson('/billing/nota-muat/'.$note->id, $this->correction())->assertOk();
        $this->assertDatabaseHas('load_note_drafts', ['id' => $note->id, 'ticket_number' => 'SJB-CORRECTED', 'net_kg' => 30220, 'revision' => 1, 'status' => 'draft']);
        $this->assertDatabaseHas('load_note_events', ['load_note_draft_id' => $note->id, 'actor_id' => $reviewer->id, 'action' => 'updated', 'reason' => 'Koreksi nomor tiket sesuai foto']);
        $this->get('/billing/nota-muat/'.$note->id)->assertOk()->assertSee('Koreksi nomor tiket sesuai foto');
    }

    public function test_stale_edit_or_decision_cannot_overwrite_a_newer_revision(): void
    {
        $note = $this->note();
        $this->actingAs($this->actor())->patchJson('/billing/nota-muat/'.$note->id, $this->correction())->assertOk();
        $this->patchJson('/billing/nota-muat/'.$note->id, $this->correction())->assertUnprocessable()->assertJsonValidationErrors('revision');
        $this->postJson('/billing/nota-muat/'.$note->id.'/decision', ['revision' => 0, 'action' => 'reject', 'reason' => 'Nota keliru'])->assertUnprocessable();
        $this->assertDatabaseCount('load_note_events', 1);
    }

    public static function decisions(): array
    {
        return [['reject', 'rejected'], ['cancel', 'cancelled']];
    }

    #[DataProvider('decisions')]
    public function test_decisions_keep_note_history_and_release_vehicle_pending_block(string $action, string $status): void
    {
        $note = $this->note();
        $this->actingAs($this->actor())->postJson('/billing/nota-muat/'.$note->id.'/decision', ['revision' => 0, 'action' => $action, 'reason' => 'Nota tidak sesuai foto'])->assertOk();
        $this->assertDatabaseHas('load_note_drafts', ['id' => $note->id, 'status' => $status, 'revision' => 1]);
        $this->assertDatabaseCount('load_note_events', 1);
        app(LoadNoteRules::class)->assertVehicle(Vehicle::findOrFail(1));
        $this->patchJson('/billing/nota-muat/'.$note->id, array_replace($this->correction(), ['revision' => 1]))->assertUnprocessable();
    }

    public function test_mutations_require_reason_and_reject_cross_vendor_vehicle(): void
    {
        $note = $this->note();
        $this->actingAs($this->actor());
        $this->postJson('/billing/nota-muat/'.$note->id.'/decision', ['revision' => 0, 'action' => 'cancel'])->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->patchJson('/billing/nota-muat/'.$note->id, array_replace($this->correction(), ['vehicle_id' => 2]))->assertUnprocessable()->assertJsonValidationErrors('vehicle_id');
        $this->assertDatabaseCount('load_note_events', 0);
    }

    public function test_vendor_cannot_review_or_forward_even_their_own_note(): void
    {
        $note = $this->note();
        $this->actingAs($this->actor('vendor-operational'));
        $this->patchJson('/billing/nota-muat/'.$note->id, $this->correction())->assertForbidden();
        $this->postJson('/billing/nota-muat/'.$note->id.'/decision', ['revision' => 0, 'action' => 'reject', 'reason' => 'Tidak sesuai'])->assertForbidden();
        $this->get('/kas-uang-jalan/keluar?load_note_id='.$note->id)->assertForbidden();
        $this->assertDatabaseCount('load_note_events', 0);
    }

    public function test_processing_links_note_only_with_a_successful_transaction_and_rolls_back_together(): void
    {
        $note = $this->note();
        $user = $this->actor();
        $service = app(LoadNoteReviewService::class);
        try {
            DB::transaction(function () use ($note, $user, $service): void {
                $locked = $service->lockForProcessing($user, $note, 0);
                $id = DB::table('transaksis')->insertGetId([]);
                $service->markProcessed($user, $locked, Transaksi::findOrFail($id));
                throw new \RuntimeException('Rollback scenario');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Rollback scenario', $exception->getMessage());
        }
        $this->assertSame('draft', $note->fresh()->status);
        $this->assertDatabaseCount('load_note_events', 0);
        $this->assertDatabaseCount('transaksis', 0);
        DB::transaction(function () use ($note, $user, $service): void {
            $locked = $service->lockForProcessing($user, $note, 0);
            $id = DB::table('transaksis')->insertGetId([]);
            $service->markProcessed($user, $locked, Transaksi::findOrFail($id));
        });
        $this->assertSame('processed', $note->fresh()->status);
        $this->assertNotNull($note->fresh()->transaksi_id);
        $this->assertDatabaseCount('load_note_events', 1);
        $this->actingAs($user)->postJson('/billing/nota-muat/'.$note->id.'/decision', ['revision' => 1, 'action' => 'cancel', 'reason' => 'Tidak boleh'])->assertUnprocessable();
    }
}
