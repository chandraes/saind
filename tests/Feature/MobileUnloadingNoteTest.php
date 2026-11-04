<?php

namespace Tests\Feature;

use App\Models\LoadNoteDraft;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MobileUnloadingNoteTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['customers', 'rutes'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama');
            });
        }
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            foreach (['vendor_id', 'vehicle_id', 'customer_id', 'rute_id', 'nomor_uang_jalan'] as $field) {
                $table->unsignedBigInteger($field);
            }
            $table->unsignedBigInteger('nominal_transaksi')->default(999999);
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kas_uang_jalan_id');
            $table->integer('status')->default(2);
            $table->boolean('void')->default(false);
            foreach (['tanggal_muat', 'tanggal_bongkar', 'nota_muat', 'nota_bongkar', 'gross_muat', 'tarra_muat', 'tonase', 'gross_bongkar', 'tarra_bongkar', 'timbangan_bongkar'] as $field) {
                $table->string($field)->nullable();
            }
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '288'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '289']]);
        DB::table('customers')->insert(['id' => 1, 'nama' => 'Mine A']);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'Mine - Port']);
    }

    private function actor(int $vendor = 1, string $role = 'vendor-operational'): User
    {
        return User::factory()->create(['username' => Str::uuid()->toString(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => $vendor]);
    }

    private function headers(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => (string) $user->vendor_id,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    private function journey(int $vendor = 1, array $attributes = []): int
    {
        $cashId = DB::table('kas_uang_jalans')->insertGetId(['vendor_id' => $vendor, 'vehicle_id' => $vendor, 'customer_id' => 1, 'rute_id' => 1, 'nomor_uang_jalan' => 7]);

        return DB::table('transaksis')->insertGetId($attributes + ['kas_uang_jalan_id' => $cashId, 'status' => 2, 'void' => 0,
            'nota_muat' => 'TICKET-100', 'tanggal_muat' => '2026-11-03', 'gross_muat' => '45.54', 'tarra_muat' => '15.32', 'tonase' => '30.22']);
    }

    public function test_waiting_list_and_detail_expose_only_the_read_contract_without_writing_business_data(): void
    {
        $id = $this->journey(1, ['nota_bongkar' => 'STALE', 'tanggal_bongkar' => '2026-11-04', 'gross_bongkar' => '44', 'tarra_bongkar' => '15', 'timbangan_bongkar' => '29']);
        $this->journey(1, ['status' => 3]);
        $this->journey(1, ['void' => 1]);
        $headers = $this->headers($this->actor());
        $before = DB::table('transaksis')->get()->toArray();
        $cashBefore = DB::table('kas_uang_jalans')->get()->toArray();
        $response = $this->getJson('/api/v1/unloading-notes', $headers)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $id)->assertJsonPath('data.0.status', 'waiting')->assertJsonPath('meta.per_page', 20);
        $expected = ['id' => $id, 'status' => 'waiting', 'vehicle_id' => 1, 'vehicle_number' => '288', 'route_name' => 'Mine - Port', 'mine_name' => 'Mine A',
            'money_out_number' => 'UJ07', 'ticket_number' => 'TICKET-100', 'loaded_on' => '2026-11-03', 'unloaded_on' => null, 'unloading_ticket_number' => null,
            'gross_weight' => '45.540', 'tare_weight' => '15.320', 'net_weight' => '30.220', 'unloading_gross_weight' => null, 'unloading_tare_weight' => null, 'unloading_net_weight' => null];
        $this->assertSame($expected, $response->json('data.0'));
        $this->getJson('/api/v1/unloading-notes/'.$id, $headers)->assertOk()->assertExactJson(['data' => $expected]);
        $this->assertEquals($before, DB::table('transaksis')->get()->toArray());
        $this->assertEquals($cashBefore, DB::table('kas_uang_jalans')->get()->toArray());
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 0]);
    }

    public function test_completed_data_and_search_match_tickets_units_and_routes(): void
    {
        $id = $this->journey(1, ['status' => 3, 'nota_bongkar' => 'UNLOAD-88', 'tanggal_bongkar' => '2026-11-04', 'gross_bongkar' => '44.5', 'tarra_bongkar' => '15', 'timbangan_bongkar' => '29.5']);
        $headers = $this->headers($this->actor());
        foreach (['TICKET', '288', 'Port', 'UNLOAD'] as $search) {
            $this->getJson('/api/v1/unloading-notes?status=completed&search='.$search, $headers)->assertOk()->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $id)->assertJsonPath('data.0.unloading_net_weight', '29.500');
        }
        $this->getJson('/api/v1/unloading-notes/'.$id, $headers)->assertOk()->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.unloading_gross_weight', '44.500')->assertJsonPath('data.unloading_tare_weight', '15.000')
            ->assertJsonPath('data.unloaded_on', '2026-11-04')->assertJsonPath('data.unloading_ticket_number', 'UNLOAD-88');
        $this->getJson('/api/v1/unloading-notes?status=completed&search=missing', $headers)->assertJsonCount(0, 'data');
    }

    public function test_optional_relations_dates_and_unloading_weights_remain_nullable(): void
    {
        $id = $this->journey(1, ['status' => 3, 'tanggal_muat' => null]);
        DB::table('kas_uang_jalans')->update(['vehicle_id' => 999, 'customer_id' => 999, 'rute_id' => 999, 'nomor_uang_jalan' => 123]);
        $this->getJson('/api/v1/unloading-notes/'.$id, $this->headers($this->actor()))->assertOk()
            ->assertJsonPath('data.vehicle_number', null)->assertJsonPath('data.mine_name', null)->assertJsonPath('data.route_name', null)
            ->assertJsonPath('data.money_out_number', 'UJ123')->assertJsonPath('data.loaded_on', null)
            ->assertJsonPath('data.unloading_gross_weight', null)->assertJsonPath('data.unloading_tare_weight', null)->assertJsonPath('data.unloading_net_weight', null);
    }

    public function test_list_is_paginated_and_validates_filters(): void
    {
        for ($i = 0; $i < 21; $i++) {
            $this->journey();
        }
        $headers = $this->headers($this->actor());
        $this->getJson('/api/v1/unloading-notes', $headers)->assertOk()->assertJsonCount(20, 'data')->assertJsonPath('meta.last_page', 2);
        $this->getJson('/api/v1/unloading-notes?page=2', $headers)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.current_page', 2);
        foreach (['status=all', 'page=0', 'search[]=x'] as $query) {
            $this->getJson('/api/v1/unloading-notes?'.$query, $headers)->assertUnprocessable();
        }
    }

    public function test_other_vendors_void_and_ineligible_statuses_are_hidden_even_in_search(): void
    {
        $id = $this->journey(2);
        $void = $this->journey(1, ['void' => 1]);
        $otherStatus = $this->journey(1, ['status' => 1]);
        $headers = $this->headers($this->actor());
        $this->getJson('/api/v1/unloading-notes?search=TICKET', $headers)->assertOk()->assertJsonCount(0, 'data');
        foreach ([$id, $void, $otherStatus, 999] as $hidden) {
            $this->getJson('/api/v1/unloading-notes/'.$hidden, $headers)->assertNotFound();
        }
    }

    public function test_loading_note_status_filters_and_processed_transaction_link(): void
    {
        foreach (['2026_11_03_104025_create_load_note_drafts_table.php', '2026_11_04_060303_add_matching_fields_to_load_note_drafts_table.php', '2026_11_04_061342_add_review_lifecycle_to_load_note_drafts.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        $actor = $this->actor();
        $transaction = $this->journey();
        foreach (['draft', 'rejected', 'cancelled', 'processed'] as $status) {
            LoadNoteDraft::factory()->forVehicle(Vehicle::findOrFail(1), $actor)->create(['status' => $status, 'transaksi_id' => $status === 'processed' ? $transaction : null]);
        }
        LoadNoteDraft::factory()->forVehicle(Vehicle::findOrFail(2), $this->actor(2))->create();
        $headers = $this->headers($actor);
        $this->getJson('/api/v1/load-notes', $headers)->assertOk()->assertJsonCount(4, 'data');
        $this->getJson('/api/v1/load-notes?status=all', $headers)->assertOk()->assertJsonCount(4, 'data');
        foreach (['draft', 'rejected', 'cancelled', 'processed'] as $status) {
            $this->getJson('/api/v1/load-notes?status='.$status, $headers)->assertOk()->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.status', $status)->assertJsonPath('data.0.transaction_id', $status === 'processed' ? $transaction : null);
        }
        $this->getJson('/api/v1/load-notes?status=waiting', $headers)->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_permissions_workspace_token_and_vendor_context_are_enforced(): void
    {
        $id = $this->journey();
        $user = $this->actor();
        $headers = $this->headers($user);
        foreach (['/api/v1/unloading-notes', '/api/v1/unloading-notes/'.$id] as $url) {
            $this->app['auth']->forgetGuards();
            $this->getJson($url, ['X-Workspace-ID' => self::WORKSPACE])->assertUnauthorized();
            $this->getJson($url, $this->headers($this->actor(1, 'admin')))->assertForbidden();
            $this->app['auth']->forgetGuards();
            $this->getJson($url, array_replace($headers, ['X-Vendor-ID' => '2']))->assertConflict();
            $this->app['auth']->forgetGuards();
            $this->getJson($url, array_replace($headers, ['X-Workspace-ID' => Str::uuid()->toString()]))->assertConflict();
            $wrongToken = $user->createToken('wrong', ['workspace:'.Str::uuid()->toString()])->plainTextToken;
            $this->app['auth']->forgetGuards();
            $this->getJson($url, array_replace($headers, ['Authorization' => 'Bearer '.$wrongToken]))->assertUnauthorized();
        }
    }
}
