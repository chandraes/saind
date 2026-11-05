<?php

namespace Tests\Feature;

use App\Models\LoadNoteDraft;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;

class LoadNoteInboxTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_03_104025_create_load_note_drafts_table.php'))->up();
        foreach (['customers', 'rutes', 'transaksis'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name): void {
                $table->id();
                if ($name !== 'transaksis') {
                    $table->string('nama');
                    $table->boolean('status')->default(true);
                }
            });
        }
        (require database_path('migrations/2026_11_04_060303_add_matching_fields_to_load_note_drafts_table.php'))->up();
        (require database_path('migrations/2026_11_04_061342_add_review_lifecycle_to_load_note_drafts.php'))->up();
        (require database_path('migrations/2026_11_04_092518_add_transfer_override_to_load_note_drafts_table.php'))->up();
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->boolean('uj_ditahan')->default(false);
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('transfer_ke')->nullable();
            $table->string('bank')->nullable();
        });
        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();
            foreach (['nama', 'no_hp', 'nama_rek', 'bank', 'no_rek'] as $field) {
                $table->string($field)->nullable();
            }
        });
        Schema::create('vendor_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('rute_id');
            foreach (['hk_uang_jalan', 'uj_ditahan', 'uj_ditahan_ban'] as $field) {
                $table->bigInteger($field)->nullable();
            }
        });
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
    }

    public static function paymentModes(): array
    {
        return [[true, 370000, 50000, 20000], [false, 300000, 0, 0]];
    }

    #[DataProvider('paymentModes')]
    public function test_note_shows_vendor_route_payment_driver_and_enabled_funds_without_paying(bool $held, int $gross, int $sparepart, int $ban): void
    {
        $note = $this->draft(1, ['driver_name' => 'Nama pada nota']);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'Rute preview']);
        $note->update(['rute_id' => 1]);
        DB::table('drivers')->insert(['id' => 1, 'nama' => 'Driver terdaftar', 'no_hp' => '0812345678', 'bank' => 'BANK DRIVER', 'nama_rek' => 'REKENING DRIVER', 'no_rek' => '111222333']);
        DB::table('vehicles')->where('id', $note->vehicle_id)->update(['uj_ditahan' => $held, 'driver_id' => 1, 'transfer_ke' => 'REKENING UNIT', 'bank' => 'BANK UNIT', 'no_rekening' => '444555666']);
        DB::table('vendor_uang_jalans')->insert([
            ['vendor_id' => 2, 'rute_id' => 1, 'hk_uang_jalan' => 999999, 'uj_ditahan' => 1, 'uj_ditahan_ban' => 1],
            ['vendor_id' => 1, 'rute_id' => 1, 'hk_uang_jalan' => 300000, 'uj_ditahan' => 50000, 'uj_ditahan_ban' => 20000],
        ]);
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'admin', 'vendor_id' => null]));
        $before = $note->fresh()->getAttributes();
        $this->get('/billing/nota-muat/'.$note->id)->assertOk()
            ->assertSee('Rencana uang jalan')->assertSee('Driver terdaftar')->assertSee('Nama pada nota')
            ->assertSee('Rp 300.000')->assertSee('Rp '.number_format($gross, 0, ',', '.'))
            ->assertSee('REKENING DRIVER')->assertDontSee('REKENING UNIT')
            ->assertViewHas('paymentPreview', fn (array $preview): bool => $preview['transfer'] === 300000 && $preview['gross'] === $gross && $preview['sparepart'] === $sparepart && $preview['ban'] === $ban);
        $this->assertDatabaseCount('transaksis', 0);
        $this->assertSame('draft', $note->fresh()->status);
        $this->assertSame($before, $note->fresh()->getAttributes());
    }

    public function test_missing_route_agreement_is_not_presented_as_zero_payment(): void
    {
        $note = $this->draft();
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'admin', 'vendor_id' => null]));
        $this->get('/billing/nota-muat/'.$note->id)->assertOk()
            ->assertSee('Pilih rute pada nota terlebih dahulu')
            ->assertViewHas('paymentPreview', fn (array $preview): bool => $preview['gross'] === null && $preview['transfer'] === null);
    }

    public function test_enabled_fund_missing_amount_warns_and_does_not_invent_a_gross_total(): void
    {
        $note = $this->draft();
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'Rute preview']);
        $note->update(['rute_id' => 1]);
        DB::table('vehicles')->where('id', $note->vehicle_id)->update(['uj_ditahan' => true]);
        DB::table('vendor_uang_jalans')->insert(['vendor_id' => 1, 'rute_id' => 1, 'hk_uang_jalan' => 300000, 'uj_ditahan' => 50000, 'uj_ditahan_ban' => 0]);
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'user', 'vendor_id' => null]));
        $this->get('/billing/nota-muat/'.$note->id)->assertOk()
            ->assertSee('Nominal UJ Ditahan Ban belum diatur')
            ->assertViewHas('paymentPreview', fn (array $preview): bool => $preview['gross'] === null);
        $this->assertDatabaseCount('transaksis', 0);
    }

    public function test_driver_and_bank_data_are_not_read_from_a_vehicle_moved_to_another_vendor(): void
    {
        $note = $this->draft();
        DB::table('drivers')->insert(['id' => 1, 'nama' => 'FOREIGN PRIVATE DRIVER', 'no_rek' => 'PRIVATE ACCOUNT']);
        DB::table('vehicles')->where('id', $note->vehicle_id)->update(['vendor_id' => 2, 'driver_id' => 1, 'uj_ditahan' => true]);
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'admin', 'vendor_id' => null]));
        $this->get('/billing/nota-muat/'.$note->id)->assertOk()
            ->assertSee('Unit tidak ditemukan pada vendor nota')
            ->assertDontSee('FOREIGN PRIVATE DRIVER')->assertDontSee('PRIVATE ACCOUNT');
    }

    public function test_held_vehicle_without_driver_uses_its_own_transfer_account(): void
    {
        $note = $this->draft();
        DB::table('vehicles')->where('id', $note->vehicle_id)->update(['uj_ditahan' => true, 'transfer_ke' => 'REKENING KENDARAAN', 'bank' => 'BANK UNIT', 'no_rekening' => '123456789']);
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'admin', 'vendor_id' => null]));
        $this->get('/billing/nota-muat/'.$note->id)->assertOk()->assertSee('REKENING KENDARAAN')->assertSee('123456789');
    }

    public function test_payment_preview_uses_note_override_but_keeps_fund_amounts_and_user_read_only(): void
    {
        $note = $this->draft();
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'Rute preview']);
        $note->update(['rute_id' => 1, 'transfer_override' => 350000]);
        DB::table('vehicles')->where('id', $note->vehicle_id)->update(['uj_ditahan' => true]);
        DB::table('vendor_uang_jalans')->insert(['vendor_id' => 1, 'rute_id' => 1, 'hk_uang_jalan' => 300000, 'uj_ditahan' => 50000, 'uj_ditahan_ban' => 20000]);
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'user', 'vendor_id' => null]));
        $this->get('/billing/nota-muat/'.$note->id)->assertOk()->assertSee('Rp 350.000')->assertSee('Rp 420.000')
            ->assertSee('Nominal khusus nota ini')->assertDontSee('id="note-payment-modal"', false)
            ->assertViewHas('paymentPreview', fn (array $preview): bool => $preview['system_transfer'] === 300000 && $preview['transfer'] === 350000 && $preview['sparepart'] === 50000 && $preview['ban'] === 20000);
    }

    public function test_pending_badge_caches_count_and_excludes_terminal_notes(): void
    {
        config(['cache.default' => 'array']);
        Cache::store('array')->flush();
        $this->draft();
        $this->draft(2, ['status' => 'rejected']);
        $this->draft(3, ['status' => 'cancelled']);
        $this->draft(4, ['status' => 'processed']);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertSame(1, LoadNoteDraft::cachedPendingReviewCount());
        $this->assertSame(1, LoadNoteDraft::cachedPendingReviewCount());
        $countQueries = array_filter(DB::getQueryLog(), fn (array $entry): bool => str_contains($entry['query'], 'count(*)') && str_contains($entry['query'], 'load_note_drafts'));
        $this->assertCount(1, $countQueries);
        DB::disableQueryLog();
        $this->draft(5);
        $this->assertSame(1, LoadNoteDraft::cachedPendingReviewCount());
        $this->travel(31)->seconds();
        $this->assertSame(2, LoadNoteDraft::cachedPendingReviewCount());
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'admin']));
        $this->view('billing.nota-muat.menu', ['pendingLoadNoteCount' => 2])->assertSee('2 nota menunggu pemeriksaan')->assertSee('>2</span>', false);
        $this->travelBack();
    }

    public static function reviewerRoles(): array
    {
        return [['admin'], ['su'], ['user']];
    }

    public static function otherRoles(): array
    {
        return [['vendor'], ['operasional'], ['asisten-user'], ['investor']];
    }

    private function draft(int $vendor = 1, array $attributes = []): LoadNoteDraft
    {
        DB::table('vendors')->insertOrIgnore(['id' => $vendor, 'nama' => 'Vendor '.$vendor]);
        $vehicle = new Vehicle;
        $vehicle->forceFill(['vendor_id' => $vendor, 'nomor_lambung' => 'UNIT-'.$vendor])->save();
        $creator = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => $vendor]);

        return LoadNoteDraft::factory()->forVehicle($vehicle, $creator)->create($attributes);
    }

    #[DataProvider('reviewerRoles')]
    public function test_reviewers_can_read_all_vendors_and_private_photos_without_changing_drafts(string $role): void
    {
        Storage::fake('local');
        $first = $this->draft(1, ['ticket_number' => 'SJB-FIRST']);
        $second = $this->draft(2, ['ticket_number' => 'SJB-SECOND']);
        Storage::disk('local')->put($second->photo_path, 'private-photo-bytes');
        $reviewer = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => null]);
        $this->actingAs($reviewer);
        $this->assertTrue(Gate::allows('reviewAny', LoadNoteDraft::class));
        $this->get('/billing/nota-muat')->assertOk()->assertSee('SJB-FIRST')->assertSee('SJB-SECOND')
            ->assertSee('Vendor 1')->assertSee('Vendor 2');
        $this->get('/billing/nota-muat/'.$first->id)->assertOk()->assertSee('SJB-FIRST');
        $this->get('/billing/nota-muat/'.$second->id)->assertOk()->assertSee('SJB-SECOND')->assertSee('30,220');
        $this->get('/billing/nota-muat/'.$second->id.'/foto')->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertStreamedContent('private-photo-bytes');
        $this->view('billing.nota-muat.menu')->assertSee(route('billing.nota-muat.index'), false);
        $this->assertDatabaseCount('load_note_drafts', 2);
        $this->assertSame('draft', $second->fresh()->status);
    }

    #[DataProvider('otherRoles')]
    public function test_other_roles_cannot_use_inbox_routes_or_see_the_menu(string $role): void
    {
        $draft = $this->draft();
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => 1]));
        $this->assertFalse(Gate::allows('reviewAny', LoadNoteDraft::class));
        foreach (['', '/'.$draft->id, '/'.$draft->id.'/foto'] as $path) {
            $this->get('/billing/nota-muat'.$path)->assertForbidden();
        }
        $this->view('billing.nota-muat.menu')->assertDontSee('Nota Muat Masuk');
    }

    public function test_vendor_operational_reads_colleague_notes_but_cannot_read_foreign_vendor_or_mutate(): void
    {
        Storage::fake('local');
        $own = $this->draft(1, ['ticket_number' => 'OWN-COLLEAGUE-NOTE']);
        $foreign = $this->draft(2, ['ticket_number' => 'FOREIGN-NOTE']);
        Storage::disk('local')->put($own->photo_path, 'own-private-photo');
        $viewer = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
        $this->actingAs($viewer);
        config(['mobile.load_note_roles' => []]);
        $this->assertFalse(Gate::allows('reviewAny', LoadNoteDraft::class));
        $this->get('/billing/nota-muat')->assertOk()->assertSee('OWN-COLLEAGUE-NOTE')->assertDontSee('FOREIGN-NOTE')->assertDontSee('Seluruh vendor')->assertViewHas('vendors', fn ($vendors): bool => $vendors->isEmpty());
        $this->getJson('/billing/nota-muat')->assertOk()->assertJsonPath('next', null)->assertSee('OWN-COLLEAGUE-NOTE')->assertDontSee('FOREIGN-NOTE');
        $this->get('/billing/nota-muat/'.$own->id)->assertOk()->assertSee('OWN-COLLEAGUE-NOTE')->assertSee('Riwayat pemeriksaan')->assertDontSee('data-note-review', false)
            ->assertViewHas('vehicles', fn ($items): bool => $items->isEmpty())->assertViewHas('customers', fn ($items): bool => $items->isEmpty())->assertViewHas('rutes', fn ($items): bool => $items->isEmpty());
        $this->get('/billing/nota-muat/'.$own->id.'/foto')->assertOk()->assertStreamedContent('own-private-photo');
        $this->get('/billing/nota-muat?vendor_id=2')->assertNotFound();
        $this->getJson('/billing/nota-muat?vendor_id=999')->assertNotFound();
        foreach (['', '/foto'] as $suffix) {
            $this->get('/billing/nota-muat/'.$foreign->id.$suffix)->assertNotFound();
        }
        foreach ([$own, $foreign] as $note) {
            $this->patchJson('/billing/nota-muat/'.$note->id, [])->assertForbidden();
            $this->patchJson('/billing/nota-muat/'.$note->id.'/payment', [])->assertForbidden();
            $this->postJson('/billing/nota-muat/'.$note->id.'/process', [])->assertForbidden();
            $this->postJson('/billing/nota-muat/'.$note->id.'/decision', [])->assertForbidden();
        }
        $this->get('/billing')->assertForbidden();
        $this->view('billing.nota-muat.menu')->assertSee('Nota Muat Masuk');
        $this->assertSame('draft', $own->fresh()->status);
        $this->assertSame(0, $own->fresh()->revision);
        $this->assertDatabaseCount('transaksis', 0);
    }

    public function test_processed_note_shows_recorded_payment_and_account_without_current_settings(): void
    {
        Schema::table('transaksis', function (Blueprint $table): void {
            $table->unsignedBigInteger('kas_uang_jalan_id');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('nominal_transaksi');
            foreach (['transfer_ke', 'bank', 'no_rekening'] as $field) {
                $table->string($field)->nullable();
            }
        });
        foreach (['uj_ditahan_details', 'uj_ditahan_ban_details'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('transaksi_id');
                $table->string('jenis');
                $table->unsignedBigInteger('nominal');
            });
        }
        DB::table('kas_uang_jalans')->insert(['id' => 1, 'vendor_id' => 1, 'nominal_transaksi' => 370000, 'transfer_ke' => 'RECORDED RECIPIENT', 'bank' => 'RECORDED BANK', 'no_rekening' => 'RECORDED-123']);
        DB::table('transaksis')->insert(['id' => 1, 'kas_uang_jalan_id' => 1]);
        $note = $this->draft(1, ['status' => 'processed', 'transaksi_id' => 1]);
        DB::table('uj_ditahan_details')->insert(['transaksi_id' => 1, 'jenis' => 'masuk', 'nominal' => 50000]);
        DB::table('uj_ditahan_ban_details')->insert(['transaksi_id' => 1, 'jenis' => 'masuk', 'nominal' => 20000]);
        DB::table('vehicles')->where('id', $note->vehicle_id)->update(['transfer_ke' => 'CURRENT ACCOUNT', 'no_rekening' => 'CURRENT-999']);
        $this->actingAs($note->creator);
        $this->get('/billing/nota-muat/'.$note->id)->assertOk()->assertSee('Uang jalan tercatat')->assertSee('Rp 370.000')->assertSee('Rp 300.000')
            ->assertSee('RECORDED RECIPIENT')->assertSee('RECORDED-123')->assertSee('Rekening pada transaksi')->assertDontSee('CURRENT ACCOUNT')->assertDontSee('CURRENT-999')
            ->assertDontSee('Pilih rute pada nota terlebih dahulu')->assertDontSee('data-note-review', false);
        DB::table('kas_uang_jalans')->where('id', 1)->update(['vendor_id' => 2]);
        $this->get('/billing/nota-muat/'.$note->id)->assertOk()->assertSee('Rincian uang jalan transaksi belum tersedia')->assertDontSee('RECORDED RECIPIENT')->assertDontSee('RECORDED-123');
    }

    public function test_vendor_operational_without_a_valid_vendor_cannot_read_inbox(): void
    {
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 999]));
        $this->get('/billing/nota-muat')->assertForbidden();
        $this->view('billing.nota-muat.menu')->assertDontSee('Nota Muat Masuk');
    }

    public function test_incremental_list_is_bounded_and_keeps_filters(): void
    {
        $note = $this->draft(1, ['ticket_number' => 'MATCH']);
        LoadNoteDraft::factory()->count(30)->create(['vendor_id' => 1, 'vehicle_id' => $note->vehicle_id, 'created_by' => $note->created_by, 'ticket_number' => 'MATCH']);
        $this->draft(2, ['ticket_number' => 'FOREIGN-MATCH']);
        $this->actingAs($note->creator);
        $first = $this->getJson('/billing/nota-muat?search=MATCH')->assertOk()->assertDontSee('FOREIGN-MATCH');
        $this->assertSame(25, substr_count($first->json('rows'), 'aria-label="Lihat detail nota'));
        $this->assertStringContainsString('search=MATCH', $first->json('next'));
        $second = $this->getJson($first->json('next'))->assertOk()->assertJsonPath('next', null);
        $this->assertSame(6, substr_count($second->json('rows'), 'aria-label="Lihat detail nota'));
    }

    public function test_guests_must_log_in_before_reading_the_inbox_or_a_photo(): void
    {
        $draft = $this->draft();
        foreach (['', '/'.$draft->id, '/'.$draft->id.'/foto'] as $path) {
            $this->get('/billing/nota-muat'.$path)->assertRedirect(route('login'));
        }
    }

    public function test_vendor_and_search_filters_are_combined_and_pagination_keeps_them(): void
    {
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'user']));
        $this->draft(1, ['ticket_number' => 'SJB-NEEDLE']);
        $this->draft(2, ['ticket_number' => 'SJB-OTHER-VENDOR', 'mine_name' => 'NEEDLE']);
        $this->draft(1, ['ticket_number' => 'SJB-OTHER-TICKET', 'mine_name' => 'Other Mine']);
        $draft = LoadNoteDraft::firstOrFail();
        LoadNoteDraft::factory()->count(25)->create(['vendor_id' => 1, 'vehicle_id' => $draft->vehicle_id, 'created_by' => $draft->created_by, 'ticket_number' => 'SJB-NEEDLE']);
        $response = $this->get('/billing/nota-muat?vendor_id=1&search=NEEDLE')->assertOk()
            ->assertSee('SJB-NEEDLE')->assertSee('>1</td>', false)->assertSee('>25</td>', false)->assertDontSee('SJB-OTHER-VENDOR')->assertDontSee('SJB-OTHER-TICKET');
        $this->assertSame(26, $response->viewData('notes')->total());
        $this->assertStringContainsString('vendor_id=1', $response->viewData('notes')->nextPageUrl());
        $this->assertStringContainsString('search=NEEDLE', $response->viewData('notes')->nextPageUrl());
        $this->get('/billing/nota-muat?vendor_id=1&search=NEEDLE&page=2')->assertOk()->assertViewHas('notes', fn ($notes): bool => $notes->count() === 1)->assertSee('>26</td>', false);
    }

    public function test_invalid_filters_and_missing_records_or_photos_are_handled(): void
    {
        Storage::fake('local');
        $draft = $this->draft();
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'admin']));
        $this->getJson('/billing/nota-muat?vendor_id=999')->assertUnprocessable()->assertJsonValidationErrors('vendor_id');
        $this->getJson('/billing/nota-muat?search='.str_repeat('a', 101))->assertUnprocessable()->assertJsonValidationErrors('search');
        $this->get('/billing/nota-muat/999')->assertNotFound();
        $this->get('/billing/nota-muat/'.$draft->id.'/foto')->assertNotFound();
    }

    public function test_note_content_is_escaped_in_the_list_and_detail(): void
    {
        $danger = '<script>alert("nota")</script>';
        $draft = $this->draft(1, ['ticket_number' => $danger, 'mine_name' => $danger, 'driver_name' => $danger, 'notes' => $danger]);
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => 'su']));
        foreach (['', '/'.$draft->id] as $path) {
            $this->get('/billing/nota-muat'.$path)->assertOk()->assertSee(e($danger), false)->assertDontSee($danger, false)
                ->assertDontSee($draft->photo_path, false);
        }
    }
}
