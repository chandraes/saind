<?php

namespace Tests\Feature;

use App\Models\LoadNoteDraft;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Schema\Blueprint;
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
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
    }

    public static function reviewerRoles(): array
    {
        return [['admin'], ['su'], ['user']];
    }

    public static function otherRoles(): array
    {
        return [['vendor-operational'], ['vendor'], ['operasional'], ['asisten-user'], ['investor']];
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
            ->assertSee('SJB-NEEDLE')->assertDontSee('SJB-OTHER-VENDOR')->assertDontSee('SJB-OTHER-TICKET');
        $this->assertSame(26, $response->viewData('notes')->total());
        $this->assertStringContainsString('vendor_id=1', $response->viewData('notes')->nextPageUrl());
        $this->assertStringContainsString('search=NEEDLE', $response->viewData('notes')->nextPageUrl());
        $this->get('/billing/nota-muat?vendor_id=1&search=NEEDLE&page=2')->assertOk()->assertViewHas('notes', fn ($notes): bool => $notes->count() === 1);
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
