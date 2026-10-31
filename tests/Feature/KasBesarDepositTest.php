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
class KasBesarDepositTest extends KasBesarDepositTestCase
{
    #[TestWith(['0'])]
    #[TestWith(['-10'])]
    #[TestWith(['1,5'])]
    #[TestWith(['1.000'])]
    #[TestWith(['abc'])]
    public function test_invalid_canonical_amount_is_rejected(string $amount): void
    {
        $this->post(route('kas-besar.masuk.store'), ['nominal_transaksi' => $amount, 'request_token' => (string) Str::uuid()])->assertSessionHasErrors('nominal_transaksi');
        $this->assertDatabaseCount('kas_besars', 0);
    }

    public function test_distinct_deposits_have_distinct_messages_and_duplicate_token_is_rejected(): void
    {
        $this->migrateToken();
        Mockery::mock('overload:App\\Services\\StarSender')->shouldReceive('sendGroup')->once()->andReturn(true);
        $token = (string) Str::uuid();
        $this->post(route('kas-besar.masuk.store'), ['nominal_transaksi' => '1000000', 'request_token' => $token])->assertSessionHas('success');
        $this->post(route('kas-besar.masuk.store'), ['nominal_transaksi' => '1000000', 'request_token' => $token])->assertSessionHasErrors('request_token');
        $this->post(route('kas-besar.masuk.store'), ['nominal_transaksi' => '2000000', 'request_token' => (string) Str::uuid()])->assertSessionHas('success');
        $this->assertDatabaseCount('kas_besars', 2);
        $this->assertDatabaseHas('kas_besars', ['nomor_kode_deposit' => 2, 'uraian' => 'Deposit', 'saldo' => 3000000, 'modal_investor_terakhir' => -3000000]);
        $messages = DB::table('pesan_was')->orderBy('id')->get();
        $this->assertCount(2, $messages);
        $this->assertStringContainsString('*D01*', $messages[0]->pesan);
        $this->assertStringContainsString('*D02*', $messages[1]->pesan);
        $this->assertSame(2, DB::table('pesan_was')->where('status', 1)->count());
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_failed_wa_preserves_deposit_and_pending_message(bool $throws): void
    {
        $this->migrateToken();
        $mock = Mockery::mock('overload:App\\Services\\StarSender')->shouldReceive('sendGroup')->once();
        if ($throws) {
            $mock->andThrow(new \RuntimeException('Provider unavailable'));
        } else {
            $mock->andReturn(false);
        }
        $this->post(route('kas-besar.masuk.store'), ['nominal_transaksi' => 1000000, 'request_token' => (string) Str::uuid()])->assertSessionHas('success')->assertSessionHas('warning');
        $this->assertDatabaseCount('kas_besars', 1);
        $this->assertDatabaseHas('pesan_was', ['status' => 0, 'tujuan' => 'deposit-test']);
        $this->assertSame(0, DB::transactionLevel());
    }

    #[TestWith(['rekenings'])]
    #[TestWith(['group_was'])]
    public function test_missing_configuration_does_not_save_deposit(string $table): void
    {
        $this->migrateToken();
        DB::table($table)->delete();
        $this->post(route('kas-besar.masuk.store'), ['nominal_transaksi' => 1000000, 'request_token' => (string) Str::uuid()])->assertSessionHasErrors();
        $this->assertDatabaseCount('kas_besars', 0);
        $this->assertDatabaseCount('pesan_was', 0);
    }

    public function test_form_has_canonical_amount_token_and_handles_missing_account(): void
    {
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        DB::table('rekenings')->delete();
        $this->get(route('kas-besar.masuk'))->assertOk()->assertSee('name="request_token"', false)->assertSee('name="nominal_transaksi"', false)->assertSee('Rekening Kas Besar belum diatur');
    }

    public function test_failed_message_record_rolls_back_the_deposit(): void
    {
        $this->migrateToken();
        PesanWa::creating(function (): void {
            throw new \RuntimeException('Simulated message storage failure');
        });
        $this->post(route('kas-besar.masuk.store'), ['nominal_transaksi' => 1000000, 'request_token' => (string) Str::uuid()])->assertStatus(500);
        $this->assertDatabaseCount('kas_besars', 0);
        $this->assertDatabaseCount('pesan_was', 0);
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_token_is_required_and_unauthorized_role_cannot_save(): void
    {
        $this->post(route('kas-besar.masuk.store'), ['nominal_transaksi' => 1000000])->assertSessionHasErrors('request_token');
        $this->actingAs(User::factory()->make(['id' => 2, 'role' => 'investor', 'password' => 'password']));
        $this->post(route('kas-besar.masuk.store'), ['nominal_transaksi' => 1000000, 'request_token' => (string) Str::uuid()])->assertForbidden();
        $this->assertDatabaseCount('kas_besars', 0);
    }

    public function test_migration_preserves_existing_cash_records(): void
    {
        DB::table('kas_besars')->insert(['id' => 15, 'tanggal' => '2026-10-01', 'uraian' => 'Saldo awal', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => 5000000, 'saldo' => 5000000, 'modal_investor_terakhir' => -5000000, 'transfer_ke' => '-', 'bank' => '-', 'no_rekening' => '-']);
        $this->migrateToken();
        $this->assertDatabaseCount('kas_besars', 1);
        $this->assertDatabaseHas('kas_besars', ['id' => 15, 'saldo' => 5000000, 'deposit_request_token' => null]);
    }
}
