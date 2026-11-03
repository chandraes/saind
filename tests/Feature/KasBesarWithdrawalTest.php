<?php

namespace Tests\Feature;

use App\Models\PesanWa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestWith;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class KasBesarWithdrawalTest extends KasBesarDepositTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2023_10_30_195412_change_untuk_column_to_table_rekenings.php'))->up();
        $this->migrateToken();
        DB::table('jenis_transaksis')->insert(['id' => 2, 'nama' => 'Keluar']);
        DB::table('rekenings')->insert(['untuk' => 'withdraw', 'nama_rekening' => 'Penerima Dana', 'nama_bank' => 'BCA', 'nomor_rekening' => '54321']);
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
    }

    private function seedBalance(int $balance = 1000000): void
    {
        DB::table('kas_besars')->insert(['tanggal' => now()->toDateString(), 'uraian' => 'Deposit', 'nomor_kode_deposit' => 1, 'jenis_transaksi_id' => 1, 'nominal_transaksi' => $balance, 'saldo' => $balance, 'modal_investor_terakhir' => -$balance, 'transfer_ke' => '-', 'bank' => '-', 'no_rekening' => '-']);
    }

    #[TestWith(['0'])]
    #[TestWith(['-1'])]
    #[TestWith(['1,5'])]
    #[TestWith(['1.000'])]
    public function test_invalid_amount_is_rejected(string $amount): void
    {
        Mockery::mock('overload:App\\Services\\StarSender')->shouldNotReceive('sendGroup');
        $this->post(route('kas-besar.keluar.store'), ['nominal_transaksi' => $amount, 'request_token' => (string) Str::uuid()])->assertSessionHasErrors('nominal_transaksi');
        $this->assertDatabaseCount('kas_besars', 0);
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_empty_or_insufficient_balance_cannot_be_withdrawn(bool $existing): void
    {
        Mockery::mock('overload:App\\Services\\StarSender')->shouldNotReceive('sendGroup');
        if ($existing) {
            $this->seedBalance(100);
        }
        $this->post(route('kas-besar.keluar.store'), ['nominal_transaksi' => 101, 'request_token' => (string) Str::uuid()])->assertSessionHasErrors('nominal_transaksi');
        $this->assertDatabaseCount('kas_besars', $existing ? 1 : 0);
        $this->assertDatabaseCount('pesan_was', 0);
    }

    public function test_withdrawal_updates_balance_and_does_not_increment_deposit_number_or_allow_duplicate(): void
    {
        $this->seedBalance();
        Mockery::mock('overload:App\\Services\\StarSender')->shouldReceive('sendGroup')->once()->andReturn(true);
        $payload = ['nominal_transaksi' => 1000000, 'request_token' => (string) Str::uuid()];
        $this->post(route('kas-besar.keluar.store'), $payload)->assertSessionHas('success');
        $this->post(route('kas-besar.keluar.store'), $payload)->assertSessionHasErrors('request_token');
        $this->assertDatabaseCount('kas_besars', 2);
        $this->assertDatabaseHas('kas_besars', ['uraian' => 'Withdraw', 'saldo' => 0, 'modal_investor' => 1000000, 'modal_investor_terakhir' => 0, 'nomor_kode_deposit' => null, 'no_rekening' => '54321']);
        $this->assertStringContainsString('Form Pengembalian Deposit', DB::table('pesan_was')->value('pesan'));
        $this->assertDatabaseHas('pesan_was', ['status' => 1]);
    }

    public function test_two_withdrawals_use_the_remaining_balance(): void
    {
        $this->seedBalance();
        Mockery::mock('overload:App\\Services\\StarSender')->shouldReceive('sendGroup')->once()->andReturn(true);
        $this->post(route('kas-besar.keluar.store'), ['nominal_transaksi' => 600000, 'request_token' => (string) Str::uuid()])->assertSessionHas('success');
        $this->post(route('kas-besar.keluar.store'), ['nominal_transaksi' => 600000, 'request_token' => (string) Str::uuid()])->assertSessionHasErrors('nominal_transaksi');
        $this->assertSame(400000, DB::table('kas_besars')->orderByDesc('id')->value('saldo'));
        $this->assertDatabaseCount('pesan_was', 1);
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_wa_failure_keeps_withdrawal_and_pending_message(bool $throws): void
    {
        $this->seedBalance();
        $mock = Mockery::mock('overload:App\\Services\\StarSender')->shouldReceive('sendGroup')->once();
        if ($throws) {
            $mock->andThrow(new \RuntimeException('Unavailable'));
        } else {
            $mock->andReturn(false);
        }
        $this->post(route('kas-besar.keluar.store'), ['nominal_transaksi' => 100000, 'request_token' => (string) Str::uuid()])->assertSessionHas('success')->assertSessionHas('warning');
        $this->assertDatabaseHas('kas_besars', ['uraian' => 'Withdraw', 'saldo' => 900000]);
        $this->assertDatabaseHas('pesan_was', ['status' => 0]);
    }

    public function test_failed_message_record_rolls_back_withdrawal(): void
    {
        $this->seedBalance();
        PesanWa::creating(function (): void {
            throw new \RuntimeException('Storage failure');
        });
        $this->post(route('kas-besar.keluar.store'), ['nominal_transaksi' => 100000, 'request_token' => (string) Str::uuid()])->assertStatus(500);
        $this->assertDatabaseCount('kas_besars', 1);
        $this->assertDatabaseCount('pesan_was', 0);
        $this->assertSame(0, DB::transactionLevel());
    }

    #[TestWith(['rekenings'])]
    #[TestWith(['group_was'])]
    public function test_missing_configuration_rejects_withdrawal(string $table): void
    {
        $this->seedBalance();
        DB::table($table)->delete();
        $this->post(route('kas-besar.keluar.store'), ['nominal_transaksi' => 100000, 'request_token' => (string) Str::uuid()])->assertSessionHasErrors();
        $this->assertDatabaseCount('kas_besars', 1);
    }

    public function test_user_cannot_withdraw_and_token_is_required(): void
    {
        $this->post(route('kas-besar.keluar.store'), ['nominal_transaksi' => 100000])->assertSessionHasErrors('request_token');
        $this->actingAs(User::factory()->make(['id' => 2, 'role' => 'user', 'password' => 'password']));
        $this->post(route('kas-besar.keluar.store'), ['nominal_transaksi' => 100000, 'request_token' => (string) Str::uuid()])->assertForbidden();
    }

    public function test_form_shows_balance_and_missing_account_safely(): void
    {
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->seedBalance();
        DB::table('rekenings')->where('untuk', 'withdraw')->delete();
        $this->get(route('kas-besar.keluar'))->assertOk()->assertSee('1.000.000')->assertSee('name="request_token"', false)->assertSee('Rekening pengembalian deposit belum diatur');
    }
}
