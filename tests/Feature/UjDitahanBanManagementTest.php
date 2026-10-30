<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UjDitahanBanManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->travelTo(now()->setDate(2026, 10, 29)->startOfDay());
        Storage::fake('local');
        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('bank')->nullable();
            $table->string('no_rek')->nullable();
            $table->string('nama_rek')->nullable();
            $table->string('no_hp')->nullable();
        });
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_lambung');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->timestamps();
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
        });
        foreach (['2026_08_07_071658_create_uj_ditahans_table.php', '2026_10_29_102252_create_uj_ditahan_bans_table.php', '2026_10_29_102253_create_uj_ditahan_ban_details_table.php', '2026_10_29_121916_add_request_token_to_uj_ditahan_ban_details_table.php', '2023_09_06_160726_create_group_was_table.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        DB::table('drivers')->insert(['id' => 1, 'nama' => 'Driver Uji', 'bank' => 'BCA', 'no_rek' => '12345', 'nama_rek' => 'Driver Uji']);
        DB::table('vehicles')->insert(['id' => 1, 'nomor_lambung' => 'BAN-01', 'driver_id' => 1]);
        DB::table('uj_ditahans')->insert(['vehicle_id' => 1, 'bulan' => 10, 'tahun' => 2026, 'total_masuk' => 999000, 'saldo' => 999000]);
        GroupWa::create(['untuk' => 'kas-uj-ditahan', 'nama_group' => 'ditahan-test']);
        $messenger = new class extends GroupWa
        {
            protected $table = 'group_was';

            public array $sent = [];

            public bool $fail = false;

            public function sendWa($tujuan, $pesan)
            {
                if ($this->fail) {
                    throw new \RuntimeException('WA unavailable');
                }
                $this->sent[] = ['to' => $tujuan, 'text' => $pesan];
            }
        };
        $this->app->instance(GroupWa::class, $messenger);
        $this->actor();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function actor(string $role = 'admin'): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role, 'password' => 'password']));
    }

    private function account(int $month = 10, int $balance = 200000): UjDitahanBan
    {
        return UjDitahanBan::factory()->create(['vehicle_id' => 1, 'bulan' => $month, 'tahun' => 2026, 'saldo' => $balance, 'total_masuk' => max(0, $balance)]);
    }

    private function withdrawal(mixed $amount = '50000'): array
    {
        return ['nominal' => $amount, 'keterangan' => 'Pencairan ban', 'bank' => 'BRI', 'no_rekening' => '6789', 'nama_rekening' => 'Bengkel', 'request_token' => (string) Str::uuid(), 'bukti_pdf' => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf')];
    }

    public function test_withdrawal_debits_only_ban_and_replay_does_not_debit_twice(): void
    {
        $account = $this->account();
        $data = $this->withdrawal();
        $this->actor('user');
        $url = route('billing.uj-ditahan-ban.cairkan', $account);
        $this->post($url, $data)->assertSessionHas('success');
        $this->assertSame(150000, $account->fresh()->saldo);
        $this->assertSame(50000, $account->fresh()->total_keluar);
        $this->assertDatabaseHas('uj_ditahan_ban_details', ['uj_ditahan_ban_id' => $account->id, 'jenis' => 'keluar', 'nominal' => 50000, 'bank' => 'BRI']);
        $this->assertDatabaseHas('uj_ditahans', ['vehicle_id' => 1, 'saldo' => 999000]);
        $this->post($url, $data)->assertSessionHas('error');
        $this->assertSame(150000, $account->fresh()->saldo);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles('uj-ditahan-ban-bukti'));
    }

    #[TestWith([0])]
    #[TestWith([-1])]
    #[TestWith(['1.000'])]
    #[TestWith(['100abc'])]
    #[TestWith(['1,5'])]
    public function test_invalid_nominal_is_rejected_before_any_balance_or_file_changes(mixed $amount): void
    {
        $account = $this->account();
        $this->post(route('billing.uj-ditahan-ban.cairkan', $account), $this->withdrawal($amount))->assertSessionHasErrors('nominal');
        $this->assertSame(200000, $account->fresh()->saldo);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_insufficient_balance_and_earlier_positive_period_prevent_withdrawal(): void
    {
        $current = $this->account();
        $this->post(route('billing.uj-ditahan-ban.cairkan', $current), $this->withdrawal('200001'))->assertSessionHasErrors('nominal');
        $this->account(9, 1);
        $this->post(route('billing.uj-ditahan-ban.cairkan', $current), $this->withdrawal())->assertSessionHasErrors('nominal');
        $this->assertSame(200000, $current->fresh()->saldo);
        $this->assertSame(0, DB::transactionLevel());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_exact_balance_can_be_withdrawn_when_older_balance_is_zero(): void
    {
        $this->account(9, 0);
        $account = $this->account();
        $this->post(route('billing.uj-ditahan-ban.cairkan', $account), $this->withdrawal('200000'))->assertSessionHas('success');
        $this->assertSame(0, $account->fresh()->saldo);
    }

    public function test_failed_ledger_insert_rolls_back_balance_and_removes_uploaded_file(): void
    {
        $account = $this->account();
        DB::unprepared("CREATE TRIGGER reject_ban_payout BEFORE INSERT ON uj_ditahan_ban_details BEGIN SELECT RAISE(ABORT, 'simulated failure'); END");
        $this->post(route('billing.uj-ditahan-ban.cairkan', $account), $this->withdrawal())->assertSessionHas('error');
        $this->assertSame(200000, $account->fresh()->saldo);
        $this->assertSame(0, $account->fresh()->total_keluar);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_failed_wa_does_not_turn_saved_withdrawal_into_failure(): void
    {
        $account = $this->account();
        app(GroupWa::class)->fail = true;
        $this->post(route('billing.uj-ditahan-ban.cairkan', $account), $this->withdrawal())->assertSessionHas('success')->assertSessionHas('warning');
        $this->assertSame(150000, $account->fresh()->saldo);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 1);
    }

    public function test_lists_default_to_current_month_separate_active_from_zero_and_offer_search(): void
    {
        $this->account();
        $this->account(9);
        $zero = $this->account(8, 0);
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];
        $url = route('billing.uj-ditahan-ban');
        $this->get($url)->assertOk()->assertSee('UJ Ditahan Ban')->assertSee('table-success', false);
        $this->getJson($url, $headers)->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.saldo', 200000);
        $this->getJson($url.'?search[value]=NOT-FOUND', $headers)->assertJsonPath('recordsFiltered', 0);
        $this->getJson(route('rekap.uj-ditahan-ban', ['bulan' => 8, 'tahun' => 2026]), $headers)->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.id', $zero->id);
        $this->getJson($url.'?bulan=13', $headers)->assertUnprocessable();
    }

    public function test_detail_scroller_and_private_pdf_endpoint_are_available(): void
    {
        $account = $this->account();
        $this->post(route('billing.uj-ditahan-ban.cairkan', $account), $this->withdrawal())->assertSessionHas('success');
        $detail = UjDitahanBanDetail::firstOrFail();
        $this->get(route('billing.uj-ditahan-ban.show', $account))->assertOk()->assertSee('UJ Ditahan Ban')->assertSee('Nominal', false);
        $this->getJson(route('billing.uj-ditahan-ban.show', $account), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('data.0.nominal_keluar', 50000);
        $this->get(route('billing.uj-ditahan-ban.bukti', $detail))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actor('operasional');
        $this->get(route('billing.uj-ditahan-ban.bukti', $detail))->assertForbidden();
    }

    public function test_unauthorized_role_cannot_view_or_withdraw_ban_funds(): void
    {
        $account = $this->account();
        $this->actor('operasional');
        $this->get(route('billing.uj-ditahan-ban'))->assertForbidden();
        $this->get(route('rekap.uj-ditahan-ban'))->assertForbidden();
        $this->get(route('billing.uj-ditahan-ban.show', $account))->assertForbidden();
        $this->post(route('billing.uj-ditahan-ban.cairkan', $account), $this->withdrawal())->assertForbidden();
        $this->assertSame(200000, $account->fresh()->saldo);
    }

    public function test_missing_receiving_account_proof_or_token_prevents_withdrawal(): void
    {
        $account = $this->account();
        $data = $this->withdrawal();
        unset($data['bank'], $data['bukti_pdf'], $data['request_token']);
        $this->post(route('billing.uj-ditahan-ban.cairkan', $account), $data)
            ->assertSessionHasErrors(['bank', 'bukti_pdf', 'request_token']);
        $this->assertSame(200000, $account->fresh()->saldo);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 0);
    }

    public function test_detail_table_limits_load_size_and_totals_include_filtered_records(): void
    {
        $account = $this->account();
        UjDitahanBanDetail::factory()->count(110)->create(['uj_ditahan_ban_id' => $account->id, 'jenis' => 'masuk', 'nominal' => 1000, 'keterangan' => 'Setoran Ban']);
        $url = route('billing.uj-ditahan-ban.show', $account);
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];
        $this->getJson($url.'?length=1000', $headers)->assertJsonCount(100, 'data')->assertJsonPath('recordsTotal', 110)->assertJsonPath('totals.masuk', 110000);
        $this->getJson($url.'?start=100&length=25', $headers)->assertJsonCount(10, 'data')->assertJsonPath('data.0.no', 101);
        $this->getJson($url.'?search[value]=NOT-FOUND', $headers)->assertJsonPath('recordsFiltered', 0)->assertJsonPath('totals.masuk', 0);
    }

    public function test_cutoff_uses_entire_period_balance_and_driver_account_only_once(): void
    {
        $account = $this->account();
        $data = ['request_token' => (string) Str::uuid(), 'nominal' => 1, 'bank' => 'Manipulated'];
        $url = route('billing.uj-ditahan-ban.cutoff', $account);
        $this->post($url, $data)->assertSessionHas('success');
        $this->assertSame(0, $account->fresh()->saldo);
        $this->assertSame(200000, $account->fresh()->total_keluar);
        $this->assertDatabaseHas('uj_ditahan_ban_details', ['uj_ditahan_ban_id' => $account->id, 'jenis' => 'keluar', 'nominal' => 200000, 'keterangan' => 'Cutoff', 'bank' => 'BCA', 'no_rekening' => '12345', 'nama_rekening' => 'Driver Uji', 'driver_id' => 1]);
        $this->assertDatabaseHas('uj_ditahans', ['vehicle_id' => 1, 'saldo' => 999000]);
        $account->update(['saldo' => 10000, 'total_masuk' => 210000]);
        $this->post($url, $data)->assertSessionHas('error');
        $this->assertSame(10000, $account->fresh()->saldo);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 1);
    }

    public function test_cutoff_is_available_only_to_admin_and_su(): void
    {
        $account = $this->account();
        $this->actor('user');
        $this->get(route('billing.uj-ditahan-ban.show', $account))->assertDontSee('Cutoff saldo');
        $this->post(route('billing.uj-ditahan-ban.cutoff', $account), ['request_token' => (string) Str::uuid()])->assertForbidden();
        $this->assertSame(200000, $account->fresh()->saldo);
        $this->actor('su');
        $this->get(route('billing.uj-ditahan-ban.show', $account))->assertDontSee('Cutoff saldo');
        $this->getJson(route('billing.uj-ditahan-ban'), ['X-Requested-With' => 'XMLHttpRequest'])->assertJsonPath('data.0.cutoff.url', route('billing.uj-ditahan-ban.cutoff', $account));
        $this->post(route('billing.uj-ditahan-ban.cutoff', $account), ['request_token' => (string) Str::uuid()])->assertSessionHas('success');
    }

    #[TestWith([0])]
    #[TestWith([-1000])]
    public function test_cutoff_rejects_nonpositive_balances(int $balance): void
    {
        $account = $this->account(10, $balance);
        $this->post(route('billing.uj-ditahan-ban.cutoff', $account), ['request_token' => (string) Str::uuid()])->assertSessionHas('error');
        $this->assertSame($balance, $account->fresh()->saldo);
        $this->assertSame(0, DB::transactionLevel());
        $this->assertDatabaseCount('uj_ditahan_ban_details', 0);
    }

    #[TestWith(['bank'])]
    #[TestWith(['no_rek'])]
    #[TestWith(['nama_rek'])]
    public function test_cutoff_requires_complete_driver_bank_details(string $field): void
    {
        $account = $this->account();
        DB::table('drivers')->where('id', 1)->update([$field => ' ']);
        $this->post(route('billing.uj-ditahan-ban.cutoff', $account), ['request_token' => (string) Str::uuid()])->assertSessionHas('error');
        $this->assertSame(200000, $account->fresh()->saldo);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 0);
    }

    public function test_cutoff_requires_token_and_missing_driver_is_rejected(): void
    {
        $account = $this->account();
        $url = route('billing.uj-ditahan-ban.cutoff', $account);
        $this->post($url, [])->assertSessionHasErrors('request_token');
        DB::table('vehicles')->where('id', 1)->update(['driver_id' => null]);
        $this->post($url, ['request_token' => (string) Str::uuid()])->assertSessionHas('error');
        $this->assertSame(200000, $account->fresh()->saldo);
    }

    public function test_cutoff_ledger_failure_rolls_back_and_wa_failure_keeps_saved_cutoff(): void
    {
        $account = $this->account();
        $url = route('billing.uj-ditahan-ban.cutoff', $account);
        DB::unprepared("CREATE TRIGGER reject_ban_cutoff BEFORE INSERT ON uj_ditahan_ban_details BEGIN SELECT RAISE(ABORT, 'simulated failure'); END");
        $this->post($url, ['request_token' => (string) Str::uuid()])->assertSessionHas('error');
        $this->assertSame(200000, $account->fresh()->saldo);
        $this->assertSame(0, $account->fresh()->total_keluar);
        $this->assertSame(0, DB::transactionLevel());
        DB::unprepared('DROP TRIGGER reject_ban_cutoff');
        app(GroupWa::class)->fail = true;
        $this->post($url, ['request_token' => (string) Str::uuid()])->assertSessionHas('success')->assertSessionHas('warning');
        $this->assertSame(0, $account->fresh()->saldo);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 1);
    }

    public function test_month_filter_marks_nonzero_ban_balances_only_for_selected_year(): void
    {
        $this->account(9, 100000);
        $this->account(10, -1000);
        $this->account(8, 0);
        UjDitahanBan::factory()->create(['vehicle_id' => 1, 'bulan' => 7, 'tahun' => 2025, 'saldo' => 50000]);
        foreach (['billing.uj-ditahan-ban', 'rekap.uj-ditahan-ban'] as $route) {
            $this->get(route($route, ['tahun' => 2026]))->assertOk()
                ->assertViewHas('activeBalanceMonths', [9, 10])
                ->assertSee('September ❗')->assertSee('Oktober ❗')
                ->assertDontSee('Agustus ❗')->assertDontSee('Juli ❗');
            $this->get(route($route, ['tahun' => 2025]))->assertOk()
                ->assertViewHas('activeBalanceMonths', [7])
                ->assertSee('Juli ❗')->assertDontSee('September ❗');
        }
    }

    public function test_cutoff_action_is_on_positive_balance_list_only_for_privileged_roles(): void
    {
        $account = $this->account();
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];
        $this->get(route('billing.uj-ditahan-ban'))->assertOk()->assertSee('id="cutoff-ban"', false);
        $this->get(route('billing.uj-ditahan-ban.show', $account))->assertOk()->assertDontSee('id="cutoff-ban"', false);
        $this->getJson(route('billing.uj-ditahan-ban'), $headers)->assertJsonPath('data.0.cutoff.driver', 'Driver Uji')->assertJsonPath('data.0.cutoff.bank', 'BCA');
        $this->actor('user');
        $this->get(route('billing.uj-ditahan-ban'))->assertDontSee('id="cutoff-ban"', false);
        $this->getJson(route('billing.uj-ditahan-ban'), $headers)->assertJsonPath('data.0.cutoff', null);
        $this->actor('admin');
        $account->update(['saldo' => -1]);
        $this->getJson(route('billing.uj-ditahan-ban'), $headers)->assertJsonPath('data.0.cutoff', null);
        $account->update(['saldo' => 0]);
        $this->getJson(route('rekap.uj-ditahan-ban'), $headers)->assertJsonPath('data.0.cutoff', null);
    }
}
