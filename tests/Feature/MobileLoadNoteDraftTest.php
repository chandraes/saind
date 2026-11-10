<?php

namespace Tests\Feature;

use App\Models\LoadNoteDraft;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MobileLoadNoteDraftTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        (require database_path('migrations/2026_11_03_104025_create_load_note_drafts_table.php'))->up();
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->string('nota_muat')->nullable()->unique();
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '169'],
            ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '082'],
        ]);
    }

    private function actor(int $vendor = 1, string $role = 'vendor-operational'): User
    {
        return User::factory()->create(['username' => Str::uuid()->toString(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => $vendor]);
    }

    /** @return array<string, string> */
    private function headers(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => (string) $user->vendor_id,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['request_id' => Str::uuid()->toString(), 'vehicle_id' => 1, 'ticket_number' => 'SJB-001',
            'mine_name' => 'PT Mustika Indah Permai', 'loaded_on' => '2026-10-23',
            'gross_weight' => '45.54', 'tare_weight' => '15.32', 'driver_name' => 'JUANTO',
            'photo' => UploadedFile::fake()->image('nota.jpg', 200, 300)];
    }

    public function test_stale_vendor_context_blocks_upload_before_storing_a_photo(): void
    {
        $user = $this->actor();
        $headers = $this->headers($user);
        $user->update(['vendor_id' => 2]);
        $payload = $this->payload();
        $payload['vehicle_id'] = 2;
        $this->postJson('/api/v1/load-notes', $payload, $headers)->assertConflict();
        $this->getJson('/api/v1/load-notes', $headers)->assertConflict();
        $this->assertDatabaseCount('load_note_drafts', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }

    public function test_upload_creates_only_a_private_draft_using_server_vendor_and_net_weight(): void
    {
        $headers = $this->headers($user = $this->actor());
        $payload = $this->payload() + ['vendor_id' => 2, 'created_by' => 99, 'status' => 'paid', 'net_weight' => '999'];
        $response = $this->postJson('/api/v1/load-notes', $payload, $headers)->assertCreated()
            ->assertJsonPath('data.status', 'draft')->assertJsonPath('data.net_weight', '30.220')
            ->assertJsonPath('data.vehicle_number', '169')->assertJsonPath('data.created_by.id', $user->id)
            ->assertJsonMissingPath('data.photo_path')->assertJsonMissingPath('data.payload_hash');
        $draft = LoadNoteDraft::findOrFail($response->json('data.id'));
        $this->assertSame(1, $draft->vendor_id);
        $this->assertSame(30220, $draft->net_kg);
        Storage::disk('local')->assertExists($draft->photo_path);
        $this->getJson('/api/v1/load-notes/'.$draft->id.'/photo', $headers)->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->getJson('/api/v1/me', $headers)->assertJsonPath('data.permissions.load_notes', true);
    }

    public function test_vendor_colleagues_can_read_notes_but_other_vendors_cannot_read_list_detail_or_photo(): void
    {
        $creator = $this->actor();
        $id = $this->postJson('/api/v1/load-notes', $this->payload(), $this->headers($creator))->assertCreated()->json('data.id');
        $colleague = $this->headers($this->actor());
        $this->getJson('/api/v1/load-notes', $colleague)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/load-notes/'.$id, $colleague)->assertOk()->assertJsonPath('data.created_by.id', $creator->id);
        $this->getJson('/api/v1/load-notes/'.$id.'/photo', $colleague)->assertOk();
        $other = $this->headers($this->actor(2));
        $this->getJson('/api/v1/load-notes', $other)->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/load-notes/'.$id, $other)->assertNotFound();
        $this->getJson('/api/v1/load-notes/'.$id.'/photo', $other)->assertNotFound();
        $this->getJson('/api/v1/load-notes/vehicles', $other)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 2);
    }

    public function test_other_roles_and_unauthenticated_requests_are_refused(): void
    {
        $this->getJson('/api/v1/load-notes', ['X-Workspace-ID' => self::WORKSPACE])->assertUnauthorized();
        foreach (['admin', 'su', 'user', 'asisten-user'] as $role) {
            $headers = $this->headers($this->actor(1, $role));
            $this->getJson('/api/v1/load-notes', $headers)->assertForbidden();
            $this->getJson('/api/v1/load-notes/1', $headers)->assertForbidden();
            $this->getJson('/api/v1/load-notes/1/photo', $headers)->assertForbidden();
            $this->postJson('/api/v1/load-notes', [], $headers)->assertForbidden();
            $this->getJson('/api/v1/me', $headers)->assertJsonPath('data.permissions.load_notes', false);
        }
    }

    public function test_wrong_workspace_and_cross_vendor_vehicle_are_rejected_before_storing_a_photo(): void
    {
        $headers = $this->headers($this->actor());
        $wrong = $headers;
        $wrong['X-Workspace-ID'] = Str::uuid()->toString();
        $this->postJson('/api/v1/load-notes', $this->payload(), $wrong)->assertStatus(409);
        $payload = $this->payload();
        $payload['vehicle_id'] = 2;
        $this->postJson('/api/v1/load-notes', $payload, $headers)->assertUnprocessable()->assertJsonValidationErrors('vehicle_id');
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertDatabaseCount('load_note_drafts', 0);
    }

    public function test_invalid_file_and_weights_are_refused(): void
    {
        $headers = $this->headers($this->actor());
        $payload = $this->payload();
        $payload['photo'] = UploadedFile::fake()->create('nota.pdf', 100, 'application/pdf');
        $this->postJson('/api/v1/load-notes', $payload, $headers)->assertUnprocessable()->assertJsonValidationErrors('photo');
        $payload = $this->payload();
        $payload['tare_weight'] = '50';
        $this->postJson('/api/v1/load-notes', $payload, $headers)->assertUnprocessable()->assertJsonValidationErrors('tare_weight');
        $payload = $this->payload();
        $payload['gross_weight'] = '45.1234';
        $this->postJson('/api/v1/load-notes', $payload, $headers)->assertUnprocessable()->assertJsonValidationErrors('gross_weight');
        $this->assertDatabaseCount('load_note_drafts', 0);
    }

    public function test_retry_is_idempotent_and_changed_payload_cannot_overwrite_the_existing_draft(): void
    {
        $headers = $this->headers($this->actor());
        $payload = $this->payload();
        $id = $this->postJson('/api/v1/load-notes', $payload, $headers)->assertCreated()->json('data.id');
        $this->postJson('/api/v1/load-notes', $payload, $headers)->assertOk()->assertJsonPath('data.id', $id);
        $payload['ticket_number'] = 'DIUBAH';
        $this->postJson('/api/v1/load-notes', $payload, $headers)->assertStatus(409);
        $this->assertDatabaseCount('load_note_drafts', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_rechecks_vehicle_status_do_count_and_pending_notes_before_storing_photos(): void
    {
        $headers = $this->headers($this->actor());
        DB::table('vehicles')->where('id', 1)->update(['status' => 'proses']);
        $this->postJson('/api/v1/load-notes', $this->payload(), $headers)->assertUnprocessable()->assertJsonValidationErrors('vehicle_id');
        DB::table('vehicles')->where('id', 1)->update(['status' => 'aktif', 'do_count' => 2]);
        $this->postJson('/api/v1/load-notes', $this->payload(), $headers)->assertUnprocessable()->assertJsonValidationErrors('vehicle_id');
        $this->assertCount(0, Storage::disk('local')->allFiles());
        DB::table('vehicles')->where('id', 1)->update(['do_count' => 1]);
        $this->postJson('/api/v1/load-notes', $this->payload(), $headers)->assertCreated();
        $next = $this->payload();
        $next['ticket_number'] = 'SJB-NEXT';
        $this->postJson('/api/v1/load-notes', $next, $headers)->assertUnprocessable()->assertJsonValidationErrors('vehicle_id');
        $this->assertDatabaseCount('load_note_drafts', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_store_enforces_backdate_before_photo_storage(): void
    {
        config(['mobile.load_note_rules.enforce_mobile_backdate' => true]);
        $this->travelTo(now()->setDate(2026, 11, 3)->setTime(12, 0));
        DB::table('konfigurasis')->insert(['kode' => 'nota-muat', 'status' => 1]);
        $this->postJson('/api/v1/load-notes', $this->payload(), $this->headers($this->actor()))
            ->assertUnprocessable()->assertJsonValidationErrors('loaded_on');
        $this->assertDatabaseCount('load_note_drafts', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }
}
