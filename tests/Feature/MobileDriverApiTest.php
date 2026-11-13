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
use PHPUnit\Framework\Attributes\TestWith;

/**
 * API mobile role driver: hanya menu driver, dikunci ke nolam yang sedang
 * ditugaskan (nolam rekan satu vendor pun tidak terlihat), baca saja kecuali
 * mengirim nota muat untuk nolamnya sendiri.
 */
class MobileDriverApiTest extends MobileApiTestCase
{
    private User $driver;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Schema::table('users', fn (Blueprint $table) => $table->unsignedBigInteger('driver_id')->nullable()->unique());
        Schema::table('vehicles', fn (Blueprint $table) => $table->unsignedBigInteger('driver_id')->nullable()->unique());
        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('status');
        });
        (require database_path('migrations/2026_11_03_104025_create_load_note_drafts_table.php'))->up();
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kas_uang_jalan_id')->nullable();
            $table->string('nota_muat')->nullable()->unique();
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
        });
        (require database_path('migrations/2026_11_04_061342_add_review_lifecycle_to_load_note_drafts.php'))->up();
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('drivers')->insert([['id' => 1, 'nama' => 'Driver A', 'status' => 'aktif'], ['id' => 2, 'nama' => 'Driver B', 'status' => 'aktif']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'driver_id' => 1, 'nomor_lambung' => '169'],
            ['id' => 2, 'vendor_id' => 1, 'driver_id' => 2, 'nomor_lambung' => '170'],
            ['id' => 3, 'vendor_id' => 2, 'driver_id' => null, 'nomor_lambung' => '082'],
        ]);
        $this->driver = User::factory()->create(['username' => 'driver-a', 'password' => 'secret-pass', 'role' => 'driver', 'driver_id' => 1]);
    }

    /** @return array<string, string> */
    private function headers(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    /** Hasil baca nota (OCR) seperti yang dikirim aplikasi; nomor unit dari foto. */
    private function scan(string $unit = 'SAI169', string $ticket = 'SJB-001'): array
    {
        return ['header_name' => 'PT Mustika lndah Permai', 'unit_code' => $unit, 'route_reference' => 'stp cpp-02 - sdj',
            'stampout_date' => '2026-11-01', 'ticket_number' => $ticket, 'gross_weight' => '49.22', 'tare_weight' => '15.89'];
    }

    /** @return array<string, mixed> */
    private function submission(int $confirmed, string $unit = 'SAI169', string $ticket = 'SJB-001'): array
    {
        return $this->scan($unit, $ticket) + ['request_id' => Str::uuid()->toString(), 'confirmed_vehicle_id' => $confirmed,
            'photo' => UploadedFile::fake()->image('nota.jpg', 200, 300)];
    }

    private function scanReferences(): void
    {
        (require database_path('migrations/2026_11_04_060303_add_matching_fields_to_load_note_drafts_table.php'))->up();
        (require database_path('migrations/2026_11_03_194757_create_rute_note_aliases_table.php'))->up();
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('tagihan_dari')->nullable();
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
        DB::table('customers')->insert(['id' => 1, 'nama' => 'PT. Mustika Indah Permai']);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'MIP (Selatan) - Port']);
        DB::table('customer_tagihans')->insert(['customer_id' => 1, 'rute_id' => 1]);
        DB::table('rute_note_aliases')->insert(['rute_id' => 1, 'name' => 'STP CPP-02 - SDJ', 'normalized_name' => 'STPCPP02SDJ']);
    }

    public function test_login_requires_an_active_driver_with_an_assigned_nolam(): void
    {
        $login = fn () => $this->postJson('/api/v1/login', ['username' => 'driver-a', 'password' => 'secret-pass', 'device_name' => 'HP'], ['X-Workspace-ID' => self::WORKSPACE]);
        DB::table('vehicles')->where('id', 1)->update(['status' => 'nonaktif']);
        $login()->assertForbidden();
        DB::table('vehicles')->where('id', 1)->update(['status' => 'aktif']);
        $login()->assertOk()->assertJsonPath('data.user.role', 'driver')
            ->assertJsonPath('data.user.permissions.driver', true)->assertJsonPath('data.user.permissions.load_notes', true)
            ->assertJsonPath('data.user.permissions.vendor_billing', false)->assertJsonPath('data.user.permissions.vendor_finance', false)
            ->assertJsonPath('data.user.permissions.maintenance_history', false)->assertJsonPath('data.user.permissions.upah_gendong_settings', false)
            ->assertJsonPath('data.user.driver_vehicle', ['id' => 1, 'nomor_lambung' => '169', 'vendor_id' => 1]);
        DB::table('drivers')->where('id', 1)->update(['status' => 'non_aktif']);
        $login()->assertForbidden();
    }

    public function test_an_existing_session_stops_when_the_driver_loses_the_nolam(): void
    {
        $headers = $this->headers($this->driver);
        $this->getJson('/api/v1/load-notes', $headers)->assertOk();
        DB::table('vehicles')->where('id', 1)->update(['driver_id' => null]);
        $this->getJson('/api/v1/me', $headers)->assertForbidden();
        $this->getJson('/api/v1/load-notes', $headers)->assertForbidden();
    }

    public function test_scan_is_rejected_when_the_note_belongs_to_another_nolam(): void
    {
        $this->scanReferences();
        $headers = $this->headers($this->driver);
        // Nolam rekan satu vendor (170) dan nolam vendor lain (082) ditolak saat diperiksa maupun dikirim.
        foreach ([['SAI170', 2], ['SAI082', 3]] as [$unit, $vehicle]) {
            $this->postJson('/api/v1/load-notes/inspect', $this->scan($unit), $headers)->assertUnprocessable()
                ->assertJsonPath('decision.code', 'unit_not_found')->assertJsonPath('message', 'Nomor lambung pada nota bukan nolam Anda. Periksa foto atau hubungi admin.');
            $this->postJson('/api/v1/load-notes/scanned', $this->submission($vehicle, $unit), $headers)->assertUnprocessable()
                ->assertJsonPath('decision.code', 'unit_not_found');
        }
        // Nota nolam sendiri tidak bisa dikonfirmasi ke nolam lain.
        $this->postJson('/api/v1/load-notes/scanned', $this->submission(2), $headers)->assertUnprocessable()
            ->assertJsonPath('decision.code', 'vehicle_confirmation_changed');
        $this->assertDatabaseCount('load_note_drafts', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_driver_scans_notes_for_the_own_nolam_and_reads_only_those(): void
    {
        $this->scanReferences();
        $headers = $this->headers($this->driver);
        $this->postJson('/api/v1/load-notes/inspect', $this->scan(), $headers)->assertOk()->assertJsonPath('data.vehicle_id', 1);
        $id = $this->postJson('/api/v1/load-notes/scanned', $this->submission(1), $headers)->assertCreated()
            ->assertJsonPath('data.vehicle_number', '169')->assertJsonPath('data.created_by.id', $this->driver->id)->json('data.id');
        $this->assertSame(1, LoadNoteDraft::findOrFail($id)->vendor_id);
        $this->postJson('/api/v1/load-notes', [], $headers)->assertMethodNotAllowed();

        // Nota rekan satu vendor (nolam 170) tidak terlihat oleh driver.
        $vendor = User::factory()->create(['username' => 'vo', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
        $colleague = $this->postJson('/api/v1/load-notes/scanned', $this->submission(2, 'SAI170', 'SJB-002'), $this->headers($vendor) + ['X-Vendor-ID' => '1'])
            ->assertCreated()->json('data.id');

        $headers = $this->headers($this->driver);
        $this->getJson('/api/v1/load-notes', $headers)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $id);
        $this->getJson('/api/v1/load-notes/'.$id, $headers)->assertOk();
        $this->getJson('/api/v1/load-notes/'.$id.'/photo', $headers)->assertOk();
        $this->getJson('/api/v1/load-notes/'.$colleague, $headers)->assertNotFound();
        $this->getJson('/api/v1/load-notes/'.$colleague.'/photo', $headers)->assertNotFound();
        $this->getJson('/api/v1/load-notes/vehicles', $headers)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);
        $this->getJson('/api/v1/vehicles', $headers)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);
        $this->getJson('/api/v1/vehicles/2', $headers)->assertNotFound();
    }

    #[TestWith(['GET', '/api/v1/vendor-options'])]
    #[TestWith(['GET', '/api/v1/payment-notes'])]
    #[TestWith(['GET', '/api/v1/vendor-invoices'])]
    #[TestWith(['GET', '/api/v1/vendor-cash'])]
    #[TestWith(['GET', '/api/v1/unit-performance/all-vendor'])]
    #[TestWith(['GET', '/api/v1/upah-gendong-settings'])]
    #[TestWith(['POST', '/api/v1/vehicles/1/upah-gendong-setting'])]
    #[TestWith(['POST', '/api/v1/vehicles/1/tire-logs'])]
    #[TestWith(['POST', '/api/v1/vehicles/1/battery-logs'])]
    #[TestWith(['POST', '/api/v1/vehicles/1/filter-oil-logs'])]
    #[TestWith(['DELETE', '/api/v1/vehicles/1/tire-logs/1'])]
    #[TestWith(['PATCH', '/api/v1/vehicles/1/battery-logs/1'])]
    #[TestWith(['GET', '/api/v1/database/vehicles'])]
    public function test_menus_and_writes_outside_the_driver_role_are_forbidden(string $method, string $url): void
    {
        $this->json($method, $url, [], $this->headers($this->driver))->assertForbidden();
    }

    #[TestWith(['/api/v1/vehicles/2/tire-monitoring'])]
    #[TestWith(['/api/v1/vehicles/2/battery-monitoring'])]
    #[TestWith(['/api/v1/vehicles/3/filter-oil-monitoring'])]
    #[TestWith(['/api/v1/vehicles/2/upah-gendong'])]
    #[TestWith(['/api/v1/vehicles/2/tire-positions/1/history'])]
    #[TestWith(['/api/v1/vehicles/2/filter-oil-categories/1/history'])]
    public function test_other_nolam_even_from_the_same_vendor_is_hidden(string $url): void
    {
        $this->getJson($url, $this->headers($this->driver))->assertNotFound();
    }

    public function test_maintenance_lists_only_the_driver_nolam(): void
    {
        $this->getJson('/api/v1/maintenance/vehicles?vendor_id=2', $this->headers($this->driver))->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);
    }

    public function test_held_cash_shows_only_the_driver_mutations_on_the_assigned_nolam(): void
    {
        Schema::create('uj_ditahan_payout_cancellations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('detail_id');
            $table->string('fund');
            $table->string('reason')->nullable();
        });
        foreach (['sparepart' => 'uj_ditahans', 'ban' => 'uj_ditahan_bans'] as $fund => $accounts) {
            $foreignKey = $fund === 'ban' ? 'uj_ditahan_ban_id' : 'uj_ditahan_id';
            $ledger = $fund === 'ban' ? 'uj_ditahan_ban_details' : 'uj_ditahan_details';
            Schema::create($accounts, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('vehicle_id');
                $table->integer('bulan');
                $table->integer('tahun');
            });
            Schema::create($ledger, function (Blueprint $table) use ($foreignKey): void {
                $table->id();
                $table->unsignedBigInteger($foreignKey);
                $table->unsignedBigInteger('driver_id')->nullable();
                $table->string('jenis');
                $table->integer('nominal');
                $table->string('keterangan');
                $table->string('payout_origin')->nullable();
                $table->string('bank')->nullable();
                $table->string('no_rekening')->nullable();
                $table->string('nama_rekening')->nullable();
                $table->timestamps();
            });
            DB::table($accounts)->insert([
                ['id' => 1, 'vehicle_id' => 1, 'bulan' => 10, 'tahun' => 2026],
                ['id' => 2, 'vehicle_id' => 1, 'bulan' => 12, 'tahun' => 2025],
                ['id' => 3, 'vehicle_id' => 2, 'bulan' => 10, 'tahun' => 2026],
            ]);
            foreach ([[1, 1, 'masuk', 800000, 'Setoran Driver'], [1, 1, 'keluar', 300000, 'Pencairan Driver'], [1, 2, 'masuk', 900000, 'Mutasi Driver Lain'],
                [1, null, 'masuk', 50000, 'Mutasi Tanpa Driver'], [2, 1, 'masuk', 200000, 'Mutasi Tahun Lalu'], [3, 1, 'masuk', 70000, 'Nolam Lain']] as [$account, $owner, $type, $amount, $description]) {
                DB::table($ledger)->insert([$foreignKey => $account, 'driver_id' => $owner, 'jenis' => $type, 'nominal' => $amount, 'keterangan' => $description, 'created_at' => '2026-10-05 08:00:00']);
            }

            $response = $this->getJson('/api/v1/driver/held-cash/'.$fund.'?month=10&year=2026', $this->headers($this->driver))->assertOk()
                ->assertJsonCount(2, 'data')->assertJsonPath('data.0.description', 'Setoran Driver')->assertJsonPath('data.0.type', 'in')
                ->assertJsonPath('data.1.type', 'out')->assertJsonPath('data.1.amount', 300000)
                ->assertJsonPath('meta.total_in', 800000)->assertJsonPath('meta.total_out', 300000)->assertJsonPath('meta.balance', 500000)
                ->assertJsonPath('meta.total_balance', 700000)->assertJsonPath('meta.vehicle_number', '169')->assertJsonPath('meta.has_more', false);
            $this->assertEqualsCanonicalizing([['year' => 2026, 'month' => 10, 'balance' => 500000], ['year' => 2025, 'month' => 12, 'balance' => 200000]],
                $response->json('meta.months_with_balance'));
            $response->assertDontSee('Mutasi Driver Lain')->assertDontSee('Mutasi Tanpa Driver')->assertDontSee('Nolam Lain');
        }
        $this->getJson('/api/v1/driver/held-cash/lain', $this->headers($this->driver))->assertNotFound();

        // Akun lain tidak punya kas driver.
        $vendor = User::factory()->create(['username' => 'vo', 'password' => 'secret-pass', 'role' => 'vendor-operational', 'vendor_id' => 1]);
        $this->getJson('/api/v1/driver/held-cash/ban', $this->headers($vendor) + ['X-Vendor-ID' => '1'])->assertForbidden();
    }
}
