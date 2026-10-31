<?php

namespace Tests\Feature;

use App\Models\BanGantiCart;
use App\Models\BanGantiInvoice;
use App\Models\GroupWa;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class BanMaintenancePaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_lambung')->default('B01');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->timestamps();
        });
        foreach (['kas_besars', 'kas_vendors', 'transaksis', 'posisi_bans', 'drivers', 'vendors'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name): void {
                $table->id();
                if ($name === 'posisi_bans' || $name === 'vendors') {
                    $table->string('nama')->default('Posisi 1');
                }
                if ($name === 'transaksis') {
                    $table->unsignedBigInteger('kas_uang_jalan_id')->nullable();
                    $table->boolean('void')->default(false);
                }
                $table->timestamps();
            });
        }
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('rute_id');
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->integer('jarak');
        });
        Schema::create('ban_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('posisi_ban_id');
            $table->string('merk');
            $table->string('no_seri');
            $table->integer('kondisi');
            $table->decimal('ritase', 10, 1)->default(0);
            $table->timestamps();
        });
        Schema::create('ban_log_transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ban_log_id');
            $table->unsignedBigInteger('transaksi_id');
            $table->decimal('nilai_ritase', 10, 1);
            $table->timestamps();
        });
        foreach (['2026_09_07_195225_create_ban_ganti_carts_table.php', '2026_09_07_195302_create_ban_ganti_invoices_table.php', '2026_09_11_055543_add_status_column_to_ban_ganti_invoices_table.php', '2026_09_15_130953_add_ritase_column_to_ban_ganti_invoice_details_table.php', '2026_10_29_102252_create_uj_ditahan_bans_table.php', '2026_10_29_102253_create_uj_ditahan_ban_details_table.php', '2026_10_29_121916_add_request_token_to_uj_ditahan_ban_details_table.php', '2026_10_29_130509_add_ban_invoice_reference_to_uj_ditahan_ban_details.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('nama_driver')->nullable();
            $table->string('nama_pengurus')->nullable();
        });
        DB::table('vehicles')->insert(['id' => 1]);
        DB::table('posisi_bans')->insert(['id' => 1]);
        $this->actor('admin');
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    private function actor(string $role): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role, 'password' => 'password']));
    }

    private function invoice(string $payment = 'dibayar_sendiri', int $nominal = 0): BanGantiInvoice
    {
        $invoice = BanGantiInvoice::create(['no_invoice' => 'TEST-'.uniqid(), 'vehicle_id' => 1, 'pembayaran' => $payment, 'total_nominal' => $nominal, 'tanggal' => now()->toDateString(), 'status' => 'pending', 'nama_bank' => 'BCA', 'nomor_rekening' => '123', 'nama_rekening' => 'Bengkel']);
        $invoice->details()->create(['posisi_ban_id' => 1, 'sumber_ban' => 'baru', 'merk' => 'Uji', 'no_seri' => 'S01', 'kondisi' => 100]);

        return $invoice;
    }

    public function test_user_cannot_approve_or_reject_ban_invoice(): void
    {
        $invoice = $this->invoice();
        $this->actor('user');
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertForbidden();
        $this->post(route('billing.otorisasi-maintenance.ban-luar.reject', $invoice))->assertForbidden();
        $this->assertSame('pending', $invoice->fresh()->status);
        $this->assertDatabaseCount('ban_logs', 0);
    }

    public function test_approved_invoice_cannot_be_rejected_or_approved_again(): void
    {
        $invoice = $this->invoice();
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertSessionHas('success');
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertSessionHas('error');
        $this->post(route('billing.otorisasi-maintenance.ban-luar.reject', $invoice))->assertSessionHas('error');
        $this->assertSame('approved', $invoice->fresh()->status);
        $this->assertDatabaseCount('ban_logs', 1);
    }

    private function balance(int $month, int $amount): int
    {
        return DB::table('uj_ditahan_bans')->insertGetId(['vehicle_id' => 1, 'bulan' => $month, 'tahun' => 2026, 'saldo' => $amount, 'total_masuk' => max(0, $amount), 'total_keluar' => 0]);
    }

    private function cart(): void
    {
        BanGantiCart::create(['vehicle_id' => 1, 'posisi_ban_id' => 1, 'sumber_ban' => 'baru', 'merk' => 'Uji', 'no_seri' => 'S01', 'kondisi' => 100]);
    }

    private function payload(string $payment = 'uj_ditahan_ban', mixed $nominal = '150000'): array
    {
        return ['vehicle_id' => 1, 'pembayaran' => $payment, 'total_nominal' => $nominal, 'nama_bank' => 'BCA', 'nomor_rekening' => '123', 'nama_rekening' => 'Bengkel'];
    }

    public function test_checkout_checks_balance_without_debit_and_approval_consumes_oldest_months_once(): void
    {
        $old = $this->balance(9, 100000);
        $current = $this->balance(10, 100000);
        $this->cart();
        $this->actor('user');
        $this->post(route('billing.form-maintenance.ban-luar.checkout'), $this->payload())->assertSessionHas('success');
        $this->assertDatabaseHas('uj_ditahan_bans', ['id' => $old, 'saldo' => 100000]);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 0);
        $invoice = BanGantiInvoice::firstOrFail();
        $this->assertSame('pending', $invoice->status);
        $this->actor('admin');
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertSessionHas('success');
        $this->assertDatabaseHas('uj_ditahan_bans', ['id' => $old, 'saldo' => 0, 'total_keluar' => 100000]);
        $this->assertDatabaseHas('uj_ditahan_bans', ['id' => $current, 'saldo' => 50000, 'total_keluar' => 50000]);
        $this->assertDatabaseHas('uj_ditahan_ban_details', ['ban_ganti_invoice_id' => $invoice->id, 'nominal' => 100000, 'bank' => 'BCA']);
        $this->assertDatabaseCount('kas_besars', 0);
        $this->assertDatabaseCount('kas_vendors', 0);
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertSessionHas('error');
        $this->assertDatabaseCount('ban_logs', 1);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 2);
    }

    public function test_insufficient_checkout_keeps_cart_and_creates_no_invoice(): void
    {
        $this->balance(10, 149999);
        $this->cart();
        $this->post(route('billing.form-maintenance.ban-luar.checkout'), $this->payload())->assertSessionHasErrors('total_nominal');
        $this->assertDatabaseCount('ban_ganti_carts', 1);
        $this->assertDatabaseCount('ban_ganti_invoices', 0);
    }

    public function test_approval_rechecks_available_balance_and_keeps_invoice_and_logs_unchanged(): void
    {
        $this->balance(10, 100000);
        $invoice = $this->invoice('uj_ditahan_ban', 150000);
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertSessionHas('error');
        $this->assertSame('pending', $invoice->fresh()->status);
        $this->assertDatabaseCount('ban_logs', 0);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 0);
        $this->assertDatabaseHas('uj_ditahan_bans', ['saldo' => 100000]);
    }

    public function test_log_failure_rolls_back_wallet_debits_and_invoice_status(): void
    {
        $this->balance(10, 200000);
        $invoice = $this->invoice('uj_ditahan_ban', 150000);
        DB::unprepared("CREATE TRIGGER reject_ban_log BEFORE INSERT ON ban_logs BEGIN SELECT RAISE(ABORT, 'simulated failure'); END");
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertSessionHas('error');
        $this->assertSame('pending', $invoice->fresh()->status);
        $this->assertDatabaseHas('uj_ditahan_bans', ['saldo' => 200000, 'total_keluar' => 0]);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 0);
    }

    public function test_rejection_never_debits_wallet_and_cannot_be_approved_later(): void
    {
        $this->balance(10, 200000);
        $invoice = $this->invoice('uj_ditahan_ban', 150000);
        $this->post(route('billing.otorisasi-maintenance.ban-luar.reject', $invoice))->assertSessionHas('success');
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertSessionHas('error');
        $this->assertDatabaseHas('uj_ditahan_bans', ['saldo' => 200000]);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 0);
    }

    public function test_user_cannot_submit_self_paid_but_admin_can(): void
    {
        $this->cart();
        $this->actor('user');
        $data = ['vehicle_id' => 1, 'pembayaran' => 'dibayar_sendiri'];
        $this->post(route('billing.form-maintenance.ban-luar.checkout'), $data)->assertSessionHasErrors('pembayaran');
        $this->assertDatabaseCount('ban_ganti_carts', 1);
        $this->actor('su');
        $this->post(route('billing.form-maintenance.ban-luar.checkout'), $data)->assertSessionHas('success');
        $this->assertDatabaseHas('ban_ganti_invoices', ['pembayaran' => 'dibayar_sendiri', 'total_nominal' => 0]);
    }

    #[TestWith([0])]
    #[TestWith([-1])]
    #[TestWith(['1.000'])]
    #[TestWith(['100abc'])]
    public function test_paid_checkout_requires_canonical_positive_rupiah(mixed $nominal): void
    {
        $this->cart();
        foreach (['kas_besar', 'uj_ditahan_ban'] as $payment) {
            $this->post(route('billing.form-maintenance.ban-luar.checkout'), $this->payload($payment, $nominal))->assertSessionHasErrors('total_nominal');
        }
        $this->assertDatabaseCount('ban_ganti_invoices', 0);
    }

    public function test_uj_ban_checkout_requires_destination_account(): void
    {
        $this->cart();
        $this->balance(10, 200000);
        $data = $this->payload();
        unset($data['nama_bank'], $data['nomor_rekening'], $data['nama_rekening']);
        $this->post(route('billing.form-maintenance.ban-luar.checkout'), $data)->assertSessionHasErrors(['nama_bank', 'nomor_rekening', 'nama_rekening']);
    }

    public function test_cart_and_authorization_display_ban_balance_and_role_specific_payment(): void
    {
        $this->balance(9, 100000);
        $this->balance(10, 50000);
        $this->cart();
        $this->actor('user');
        $this->get(route('billing.form-maintenance.ban-luar.confirm'))->assertOk()->assertSee('150.000')->assertSee('UJ Ditahan Ban')->assertDontSee('value="dibayar_sendiri"', false)->assertSee('table-success', false);
        $this->actor('su');
        $this->get(route('billing.form-maintenance.ban-luar.confirm'))->assertOk()->assertSee('value="dibayar_sendiri"', false);
        $invoice = $this->invoice('uj_ditahan_ban', 120000);
        $this->get(route('billing.otorisasi-maintenance'))->assertOk()->assertSee('UJ Ditahan Ban')->assertSee('150.000');
        $this->get(route('rekap.maintenance.ban-luar.show', $invoice))->assertOk()->assertSee('UJ Ditahan Ban')->assertSee('150.000')->assertSee('Bengkel');
    }

    public function test_kas_besar_checkout_remains_available_without_ban_balance(): void
    {
        $this->cart();
        $this->actor('user');
        $this->post(route('billing.form-maintenance.ban-luar.checkout'), $this->payload('kas_besar'))->assertSessionHas('success');
        $this->assertDatabaseHas('ban_ganti_invoices', ['pembayaran' => 'kas_besar', 'total_nominal' => 150000]);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 0);
    }

    private function configureWhatsApp(?string $phone): void
    {
        Schema::table('drivers', function (Blueprint $table): void {
            $table->string('no_hp')->nullable();
        });
        Schema::create('group_was', function (Blueprint $table): void {
            $table->id();
            $table->string('untuk');
            $table->string('nama_group');
        });
        Schema::create('uj_ditahans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->integer('saldo');
        });
        DB::table('group_was')->insert(['untuk' => 'kas-uj-ditahan', 'nama_group' => 'uj-ditahan-test']);
        DB::table('drivers')->insert(['id' => 1, 'no_hp' => $phone]);
        DB::table('vehicles')->where('id', 1)->update(['driver_id' => 1]);
        DB::table('vehicles')->insert(['id' => 2, 'nomor_lambung' => 'OTHER']);
        DB::table('uj_ditahans')->insert([['vehicle_id' => 1, 'saldo' => 70000], ['vehicle_id' => 2, 'saldo' => 999000]]);
        DB::table('uj_ditahan_bans')->insert(['vehicle_id' => 2, 'bulan' => 9, 'tahun' => 2026, 'saldo' => 999000]);
    }

    public function test_ban_payment_notifies_group_and_driver_after_commit_with_both_vehicle_balances(): void
    {
        $this->configureWhatsApp('+62 812-3456-7890');
        $this->balance(9, 100000);
        $this->balance(10, 100000);
        $this->cart();
        $sent = [];
        $this->mock(GroupWa::class)->shouldReceive('sendWa')->twice()->andReturnUsing(function (string $to, string $text) use (&$sent): void {
            $sent[] = ['to' => $to, 'text' => $text, 'transaction_level' => DB::transactionLevel()];
        });
        $this->post(route('billing.form-maintenance.ban-luar.checkout'), $this->payload())->assertSessionHas('success');
        $this->assertSame([], $sent);
        $invoice = BanGantiInvoice::firstOrFail();
        $url = route('billing.otorisasi-maintenance.ban-luar.approve', $invoice);
        $this->post($url)->assertSessionHas('success')->assertSessionMissing('warning');
        $this->assertSame(['uj-ditahan-test', '6281234567890'], array_column($sent, 'to'));
        $this->assertSame([0, 0], array_column($sent, 'transaction_level'));
        $this->assertSame($sent[0]['text'], $sent[1]['text']);
        $text = $sent[0]['text'];
        foreach (['*FORM PENGGANTIAN BAN LUAR*', 'Invoice : '.$invoice->no_invoice, 'Pembayaran : UJ Ditahan Ban', '*Rp. 150.000*', 'Bank     : BCA', 'Nama    : Bengkel', 'No. Rek : 123', "Total UJ Ditahan Sparepart: B01\nRp. 70.000", "Total UJ Ditahan Ban: B01\nRp. 50.000", "Grand Total UJ Ditahan: B01\nRp. 120.000"] as $expected) {
            $this->assertStringContainsString($expected, $text);
        }
        $this->assertStringNotContainsString('999.000', $text);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 2);
        $this->post($url)->assertSessionHas('error');
        $this->assertCount(2, $sent);
    }

    #[TestWith([null])]
    #[TestWith(['0812ABC567890'])]
    public function test_ban_notification_skips_missing_or_invalid_driver_number(?string $phone): void
    {
        $this->configureWhatsApp($phone);
        $this->balance(10, 200000);
        $invoice = $this->invoice('uj_ditahan_ban', 150000);
        $this->mock(GroupWa::class)->shouldReceive('sendWa')->once()->with('uj-ditahan-test', \Mockery::type('string'));
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertSessionHas('success')->assertSessionMissing('warning');
    }

    public function test_group_failure_still_attempts_driver_and_does_not_reverse_ban_approval(): void
    {
        $this->configureWhatsApp('0812-3456-7890');
        $this->balance(10, 200000);
        $invoice = $this->invoice('uj_ditahan_ban', 150000);
        $messenger = $this->mock(GroupWa::class);
        $messenger->shouldReceive('sendWa')->once()->with('uj-ditahan-test', \Mockery::type('string'))->andThrow(new \RuntimeException('Simulated group failure'));
        $messenger->shouldReceive('sendWa')->once()->with('081234567890', \Mockery::type('string'));
        $this->post(route('billing.otorisasi-maintenance.ban-luar.approve', $invoice))->assertSessionHas('success')->assertSessionHas('warning');
        $this->assertSame('approved', $invoice->fresh()->status);
        $this->assertDatabaseHas('uj_ditahan_bans', ['vehicle_id' => 1, 'saldo' => 50000]);
        $this->assertDatabaseHas('uj_ditahans', ['vehicle_id' => 1, 'saldo' => 70000]);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 1);
    }
}
