<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * Kas UJ Ditahan per nolam (web & mobile), baca saja: vendor-induk hanya nolam
 * vendornya yang tidak nonaktif, operasional semua nolam yang tidak nonaktif.
 * Mutasi yang tampil adalah seluruh mutasi nolam (semua driver), seperti ledger admin.
 */
class NolamHeldCashTest extends MobileApiTestCase
{
    private User $induk;

    private User $operasional;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        Schema::table('users', fn (Blueprint $table) => $table->unsignedBigInteger('driver_id')->nullable());
        Schema::table('vehicles', fn (Blueprint $table) => $table->unsignedBigInteger('driver_id')->nullable());
        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('status');
        });
        Schema::create('uj_ditahan_payout_cancellations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('detail_id');
            $table->string('fund');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'VENDOR INDUK'], ['id' => 2, 'nama' => 'VENDOR LAIN']]);
        DB::table('drivers')->insert([['id' => 1, 'nama' => 'Driver Satu', 'status' => 'aktif'], ['id' => 2, 'nama' => 'Driver Dua', 'status' => 'aktif']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'driver_id' => 1, 'nomor_lambung' => 'NOLAM-INDUK', 'status' => 'aktif'],
            ['id' => 2, 'vendor_id' => 1, 'driver_id' => null, 'nomor_lambung' => 'NOLAM-NONAKTIF', 'status' => 'nonaktif'],
            ['id' => 3, 'vendor_id' => 2, 'driver_id' => 2, 'nomor_lambung' => 'NOLAM-LAIN', 'status' => 'proses'],
        ]);
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
                $table->string('file_pdf')->nullable();
                $table->string('bank')->nullable();
                $table->string('no_rekening')->nullable();
                $table->string('nama_rekening')->nullable();
                $table->timestamps();
            });
            DB::table($accounts)->insert([
                ['id' => 1, 'vehicle_id' => 1, 'bulan' => 10, 'tahun' => 2026],
                ['id' => 2, 'vehicle_id' => 1, 'bulan' => 12, 'tahun' => 2025],
                ['id' => 3, 'vehicle_id' => 3, 'bulan' => 10, 'tahun' => 2026],
            ]);
            foreach ([[1, 1, 'masuk', 800000, 'Setoran Driver Satu', null], [1, 2, 'masuk', 900000, 'Setoran Driver Dua', null],
                [1, null, 'keluar', 300000, 'Pencairan Tanpa Driver', 'BRI'], [2, 1, 'masuk', 200000, 'Mutasi Tahun Lalu', null], [3, 2, 'masuk', 70000, 'Mutasi Nolam Lain', null]] as [$account, $owner, $type, $amount, $description, $bank]) {
                DB::table($ledger)->insert([$foreignKey => $account, 'driver_id' => $owner, 'jenis' => $type, 'nominal' => $amount, 'keterangan' => $description,
                    'bank' => $bank, 'no_rekening' => $bank ? '0001' : null, 'nama_rekening' => $bank ? 'Pemilik' : null, 'created_at' => '2026-10-05 08:00:00']);
            }
        }
        $this->induk = User::factory()->create(['username' => 'induk', 'password' => 'secret-pass', 'role' => 'vendor-induk', 'vendor_id' => 1]);
        $this->operasional = User::factory()->create(['username' => 'ops', 'password' => 'secret-pass', 'role' => 'operasional']);
    }

    /** @return array<string, string> */
    private function headers(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken]
            + ($user->vendor_id ? ['X-Vendor-ID' => (string) $user->vendor_id] : []);
    }

    public function test_api_shows_every_mutation_of_the_chosen_nolam(): void
    {
        foreach (['sparepart', 'ban'] as $fund) {
            $response = $this->getJson('/api/v1/vehicles/1/held-cash/'.$fund.'?month=10&year=2026', $this->headers($this->induk))->assertOk()
                ->assertJsonCount(3, 'data')->assertJsonPath('data.0.driver', 'Driver Satu')->assertJsonPath('data.1.driver', 'Driver Dua')
                ->assertJsonPath('data.2.type', 'out')->assertJsonPath('data.2.account.bank', 'BRI')
                ->assertJsonPath('meta.total_in', 1700000)->assertJsonPath('meta.total_out', 300000)->assertJsonPath('meta.balance', 1400000)
                ->assertJsonPath('meta.total_balance', 1600000)->assertJsonPath('meta.vehicle_number', 'NOLAM-INDUK')->assertJsonPath('meta.vehicle_id', 1);
            $response->assertDontSee('Mutasi Nolam Lain');
            $this->getJson('/api/v1/vehicles/3/held-cash/'.$fund.'?month=10&year=2026', $this->headers($this->operasional))->assertOk()
                ->assertJsonCount(1, 'data')->assertJsonPath('data.0.description', 'Mutasi Nolam Lain');
        }
    }

    #[TestWith(['induk', 2])]
    #[TestWith(['induk', 3])]
    #[TestWith(['operasional', 2])]
    public function test_api_hides_nonaktif_and_other_vendor_nolam(string $account, int $vehicle): void
    {
        $this->getJson('/api/v1/vehicles/'.$vehicle.'/held-cash/ban', $this->headers($this->{$account}))->assertNotFound();
    }

    #[TestWith(['vendor'])]
    #[TestWith(['vendor-operational'])]
    public function test_other_vendor_roles_have_no_nolam_held_cash(string $role): void
    {
        $user = User::factory()->create(['username' => $role, 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => 1]);
        $this->getJson('/api/v1/vehicles/1/held-cash/sparepart', $this->headers($user))->assertForbidden();
        $this->getJson('/api/v1/me', $this->headers($user))->assertJsonPath('data.permissions.held_cash', false);
        $this->actingAs($user)->get(route('vendor-operational.held-cash', 'sparepart'))->assertForbidden();
    }

    public function test_me_grants_held_cash_to_induk_and_operasional(): void
    {
        $this->getJson('/api/v1/me', $this->headers($this->induk))->assertJsonPath('data.permissions.held_cash', true);
        $this->getJson('/api/v1/me', $this->headers($this->operasional))->assertJsonPath('data.permissions.held_cash', true);
    }

    public function test_web_picker_lists_only_selectable_nolam_and_shows_the_full_ledger(): void
    {
        $this->actingAs($this->induk)->get(route('vendor-operational.held-cash', 'sparepart'))->assertOk()
            ->assertSee('Pilih nolam terlebih dahulu')->assertSee('NOLAM-INDUK')->assertDontSee('NOLAM-NONAKTIF')->assertDontSee('NOLAM-LAIN');
        $this->actingAs($this->operasional)->get(route('vendor-operational.held-cash', 'ban'))->assertOk()
            ->assertSee('NOLAM-INDUK')->assertSee('NOLAM-LAIN — VENDOR LAIN')->assertDontSee('NOLAM-NONAKTIF');

        $this->actingAs($this->induk)->get(route('vendor-operational.held-cash', ['fund' => 'ban', 'vehicle_id' => 1, 'bulan' => 10, 'tahun' => 2026]))->assertOk()
            ->assertSee('Setoran Driver Satu')->assertSee('Setoran Driver Dua')->assertSee('Pencairan Tanpa Driver')->assertSee('a.n Pemilik')
            ->assertSee('Rp 1.600.000')->assertDontSee('Mutasi Nolam Lain')->assertDontSee('Cairkan Saldo')->assertDontSee('Batalkan');
        $this->actingAs($this->induk)->get(route('vendor-operational.held-cash', ['fund' => 'ban', 'vehicle_id' => 3]))->assertNotFound();
        $this->actingAs($this->operasional)->get(route('vendor-operational.held-cash', ['fund' => 'ban', 'vehicle_id' => 2]))->assertNotFound();
    }

    public function test_dashboard_links_held_cash_for_induk_and_operasional(): void
    {
        foreach ([$this->induk, $this->operasional] as $user) {
            $this->actingAs($user)->get(route('home'))->assertOk()->assertSee('Kas UJ Ditahan')
                ->assertSee(route('vendor-operational.held-cash', 'sparepart'), false)->assertSee(route('vendor-operational.held-cash', 'ban'), false);
        }
    }
}
