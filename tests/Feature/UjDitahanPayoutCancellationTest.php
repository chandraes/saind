<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanPayoutCancellation;
use App\Models\User;
use App\Services\UjDitahanPayoutCancellationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UjDitahanPayoutCancellationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        (require database_path('migrations/2014_10_12_000000_create_users_table.php'))->up();
        Schema::create('vehicles', function (Blueprint $t): void {
            $t->id();
            $t->string('nomor_lambung');
            $t->unsignedBigInteger('driver_id')->nullable();
        });
        Schema::create('drivers', function (Blueprint $t): void {
            $t->id();
            $t->string('nama');
            $t->string('no_hp')->nullable();
        });
        foreach (['uj_ditahans', 'uj_ditahan_bans'] as $table) {
            Schema::create($table, function (Blueprint $t): void {
                $t->id();
                $t->unsignedBigInteger('vehicle_id');
                $t->integer('bulan');
                $t->integer('tahun');
                $t->bigInteger('saldo');
                $t->bigInteger('total_masuk');
                $t->bigInteger('total_keluar');
                $t->timestamps();
            });
        }
        foreach (['uj_ditahan_details' => 'uj_ditahan_id', 'uj_ditahan_ban_details' => 'uj_ditahan_ban_id'] as $table => $foreign) {
            Schema::create($table, function (Blueprint $t) use ($foreign): void {
                $t->id();
                $t->unsignedBigInteger($foreign);
                $t->string('jenis');
                $t->bigInteger('nominal');
                $t->string('keterangan')->nullable();
                $t->unsignedBigInteger('transaksi_id')->nullable();
                $t->unsignedBigInteger('driver_id')->nullable();
                $t->string('file_pdf')->nullable();
                $t->string('bank')->nullable();
                $t->string('no_rekening')->nullable();
                $t->string('nama_rekening')->nullable();
                $t->unsignedBigInteger('aki_ganti_invoice_id')->nullable();
                $t->unsignedBigInteger('filter_oli_ganti_invoice_id')->nullable();
                $t->unsignedBigInteger('ban_ganti_invoice_id')->nullable();
                $t->timestamps();
            });
        }
        (require database_path('migrations/2023_09_06_160726_create_group_was_table.php'))->up();
        DB::table('group_was')->insert(['untuk' => 'kas-uj-ditahan', 'nama_group' => 'uj-test']);
        DB::table('drivers')->insert(['id' => 1, 'nama' => 'Driver Uji', 'no_hp' => '081234567890']);
        DB::table('vehicles')->insert(['id' => 1, 'nomor_lambung' => 'UJI-01', 'driver_id' => 1]);
        $this->actingAs(User::factory()->create(['username' => 'cancel-admin', 'role' => 'admin', 'password' => 'password']));
        $messenger = new class extends GroupWa
        {
            public array $sent = [];

            public bool $fail = false;

            public function sendWa($to, $message)
            {
                if ($this->fail) {
                    throw new \RuntimeException('offline');
                }$this->sent[] = ['to' => $to, 'text' => $message];
            }
        };
        $this->app->instance(GroupWa::class, $messenger);
    }

    private function setupPayout(string $fund, bool $migrate = true): int
    {
        $table = $fund === 'ban' ? 'uj_ditahan_bans' : 'uj_ditahans';
        $details = $fund === 'ban' ? 'uj_ditahan_ban_details' : 'uj_ditahan_details';
        $foreign = $fund === 'ban' ? 'uj_ditahan_ban_id' : 'uj_ditahan_id';
        DB::table($table)->insert(['id' => 1, 'vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'total_masuk' => 1000000, 'total_keluar' => 400000, 'saldo' => 600000]);
        $id = DB::table($details)->insertGetId([$foreign => 1, 'jenis' => 'keluar', 'nominal' => 400000, 'keterangan' => 'Pencairan manual', 'file_pdf' => 'bukti.pdf', 'bank' => 'BCA', 'no_rekening' => '123', 'nama_rekening' => 'Driver', 'created_at' => now(), 'updated_at' => now()]);
        if ($migrate) {
            $this->migrateCancellation();
        }

        return $id;
    }

    private function migrateCancellation(): void
    {
        (require database_path('migrations/2026_11_01_174435_add_uj_ditahan_payout_cancellations.php'))->up();
        (require database_path('migrations/2026_11_01_175900_add_reversal_detail_to_uj_ditahan_payout_cancellations.php'))->up();
    }

    #[TestWith(['sparepart'])]
    #[TestWith(['ban'])]
    public function test_cancel_restores_original_period_once_and_sends_blue_wa(string $fund): void
    {
        $id = $this->setupPayout($fund);
        $table = $fund === 'ban' ? 'uj_ditahan_bans' : 'uj_ditahans';
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => $fund, 'detail' => $id]), ['reason' => 'Salah rekening'])->assertSessionHas('success');
        $this->assertDatabaseHas($table, ['id' => 1, 'bulan' => 9, 'saldo' => 1000000, 'total_keluar' => 400000, 'total_masuk' => 1400000]);
        $this->assertDatabaseHas('uj_ditahan_payout_cancellations', ['fund' => $fund, 'detail_id' => $id, 'reason' => 'Salah rekening', 'nominal' => 400000]);
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => $fund, 'detail' => $id]), ['reason' => 'Ulang'])->assertSessionHas('error');
        $this->assertDatabaseCount('uj_ditahan_payout_cancellations', 1);
        $this->assertCount(2, app(GroupWa::class)->sent);
        $text = app(GroupWa::class)->sent[0]['text'];
        $this->assertStringContainsString('🔵🔵🔵', $text);
        $this->assertStringContainsString('Form Pembatalan Pencairan UJ Ditahan', $text);
        $this->assertStringNotContainsString('🔴', $text);
        $this->assertStringContainsString('Salah rekening', $text);
    }

    #[TestWith(['sparepart', 'aki_ganti_invoice_id'])]
    #[TestWith(['sparepart', 'filter_oli_ganti_invoice_id'])]
    #[TestWith(['ban', 'ban_ganti_invoice_id'])]
    #[TestWith(['ban', 'transaksi_id'])]
    #[TestWith(['sparepart', 'file_pdf'])]
    public function test_maintenance_transaction_and_nonmanual_payout_cannot_be_cancelled(string $fund, string $field): void
    {
        $id = $this->setupPayout($fund, false);
        $table = $fund === 'ban' ? 'uj_ditahan_ban_details' : 'uj_ditahan_details';
        DB::table($table)->where('id', $id)->update([$field => $field === 'file_pdf' ? null : 15]);
        $this->migrateCancellation();
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => $fund, 'detail' => $id]), ['reason' => 'Tidak boleh'])->assertSessionHas('error');
        $this->assertDatabaseCount('uj_ditahan_payout_cancellations', 0);
    }

    public function test_user_is_forbidden_and_reason_is_required(): void
    {
        $id = $this->setupPayout('ban');
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => 'ban', 'detail' => $id]), [])->assertSessionHasErrors('reason');
        auth()->user()->update(['role' => 'user']);
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => 'ban', 'detail' => $id]), ['reason' => 'Batal'])->assertForbidden();
        $this->expectException(HttpException::class);
        app(UjDitahanPayoutCancellationService::class)->cancel('ban', $id, auth()->user(), 'Batal');
    }

    public function test_wa_failure_does_not_undo_cancellation(): void
    {
        $id = $this->setupPayout('sparepart');
        app(GroupWa::class)->fail = true;
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => 'sparepart', 'detail' => $id]), ['reason' => 'Salah nominal'])->assertSessionHas('success')->assertSessionHas('warning');
        $this->assertDatabaseHas('uj_ditahans', ['id' => 1, 'saldo' => 1000000]);
        $this->assertDatabaseCount('uj_ditahan_payout_cancellations', 1);
    }

    public function test_cutoff_and_incoming_are_not_classified_as_manual(): void
    {
        $id = $this->setupPayout('ban', false);
        DB::table('uj_ditahan_ban_details')->where('id', $id)->update(['keterangan' => 'Cutoff']);
        $this->migrateCancellation();
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => 'ban', 'detail' => $id]), ['reason' => 'Batal'])->assertSessionHas('error');
        $this->assertDatabaseCount('uj_ditahan_payout_cancellations', 0);
    }

    public function test_ban_details_exclude_cancelled_amount_and_user_has_no_cancel_action(): void
    {
        $id = $this->setupPayout('ban');
        $this->getJson(route('billing.uj-ditahan-ban.show', 1), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('totals.keluar', 400000)->assertJsonPath('data.0.cancel_url', route('billing.uj-ditahan.cancel', ['fund' => 'ban', 'detail' => $id]));
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => 'ban', 'detail' => $id]), ['reason' => 'Koreksi'])->assertSessionHas('success');
        $this->getJson(route('billing.uj-ditahan-ban.show', 1), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('totals.keluar', 400000)->assertJsonPath('totals.masuk', 400000)->assertJsonPath('data.0.nominal_masuk', 400000)->assertJsonPath('data.0.cancel_url', null)->assertJsonPath('data.1.nominal_keluar', 400000)->assertJsonPath('data.1.cancelled.reason', 'Koreksi')->assertJsonPath('data.1.cancel_url', null);
        auth()->user()->update(['role' => 'user']);
        $this->getJson(route('billing.uj-ditahan-ban.show', 1), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('data.0.cancel_url', null);
    }

    public function test_sparepart_details_show_audit_and_preserve_proof(): void
    {
        $id = $this->setupPayout('sparepart');
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->get(route('billing.uj-ditahan.show', 1))->assertOk()->assertSee('data-payout-cancel-url', false);
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => 'sparepart', 'detail' => $id]), ['reason' => 'Koreksi rekening'])->assertSessionHas('success');
        $this->get(route('billing.uj-ditahan.show', 1))->assertOk()->assertSee('Dibatalkan')->assertSee('Koreksi rekening')->assertDontSee('data-payout-cancel-url=', false);
        $this->assertDatabaseHas('uj_ditahan_details', ['id' => $id, 'nominal' => 400000, 'file_pdf' => 'bukti.pdf', 'jenis' => 'keluar']);
    }

    public function test_same_detail_id_in_different_funds_can_each_be_cancelled_by_su(): void
    {
        $this->setupPayout('sparepart', false);
        $this->setupPayout('ban', false);
        $this->migrateCancellation();
        auth()->user()->update(['role' => 'su']);
        foreach (['sparepart', 'ban'] as $fund) {
            $this->post(route('billing.uj-ditahan.cancel', ['fund' => $fund, 'detail' => 1]), ['reason' => 'Koreksi SU'])->assertSessionHas('success');
        }
        $this->assertDatabaseCount('uj_ditahan_payout_cancellations', 2);
        $this->assertDatabaseHas('uj_ditahans', ['saldo' => 1000000, 'total_keluar' => 400000, 'total_masuk' => 1400000]);
        $this->assertDatabaseHas('uj_ditahan_bans', ['saldo' => 1000000, 'total_keluar' => 400000, 'total_masuk' => 1400000]);
    }

    public function test_failed_audit_write_rolls_back_balance(): void
    {
        $id = $this->setupPayout('ban');
        UjDitahanPayoutCancellation::creating(function (): void {
            throw new \RuntimeException('Audit storage unavailable');
        });
        try {
            $this->post(route('billing.uj-ditahan.cancel', ['fund' => 'ban', 'detail' => $id]), ['reason' => 'Koreksi'])->assertSessionHas('error');
            $this->assertDatabaseHas('uj_ditahan_bans', ['id' => 1, 'saldo' => 600000, 'total_keluar' => 400000]);
            $this->assertDatabaseCount('uj_ditahan_payout_cancellations', 0);
            $this->assertSame(0, DB::transactionLevel());
        } finally {
            UjDitahanPayoutCancellation::flushEventListeners();
        }
    }

    #[TestWith(['sparepart'])]
    #[TestWith(['ban'])]
    public function test_reversal_row_matches_totals_and_cannot_be_cancelled(string $fund): void
    {
        $id = $this->setupPayout($fund);
        $accounts = $fund === 'ban' ? 'uj_ditahan_bans' : 'uj_ditahans';
        $details = $fund === 'ban' ? 'uj_ditahan_ban_details' : 'uj_ditahan_details';
        $foreign = $fund === 'ban' ? 'uj_ditahan_ban_id' : 'uj_ditahan_id';
        DB::table($details)->insert([$foreign => 1, 'jenis' => 'masuk', 'nominal' => 1000000, 'keterangan' => 'Saldo awal', 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()]);
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => $fund, 'detail' => $id]), ['reason' => 'Koreksi nominal'])->assertSessionHas('success');
        $audit = DB::table('uj_ditahan_payout_cancellations')->first();
        $this->assertNotNull($audit->reversal_detail_id);
        $this->assertDatabaseHas($details, ['id' => $audit->reversal_detail_id, $foreign => 1, 'jenis' => 'masuk', 'nominal' => 400000, 'payout_origin' => 'payout_reversal']);
        $this->assertDatabaseCount($details, 3);
        $account = DB::table($accounts)->first();
        $this->assertEquals($account->total_masuk, DB::table($details)->where('jenis', 'masuk')->sum('nominal'));
        $this->assertEquals($account->total_keluar, DB::table($details)->where('jenis', 'keluar')->sum('nominal'));
        $this->assertEquals($account->saldo, $account->total_masuk - $account->total_keluar);
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => $fund, 'detail' => $audit->reversal_detail_id]), ['reason' => 'Tidak boleh'])->assertSessionHas('error');
        $this->assertDatabaseCount($details, 3);
    }

    #[TestWith(['sparepart'])]
    #[TestWith(['ban'])]
    public function test_upgrade_of_earlier_cancellation_preserves_balance(string $fund): void
    {
        $id = $this->setupPayout($fund, false);
        (require database_path('migrations/2026_11_01_174435_add_uj_ditahan_payout_cancellations.php'))->up();
        $accounts = $fund === 'ban' ? 'uj_ditahan_bans' : 'uj_ditahans';
        $details = $fund === 'ban' ? 'uj_ditahan_ban_details' : 'uj_ditahan_details';
        DB::table($accounts)->where('id', 1)->update(['saldo' => 1000000, 'total_keluar' => 0]);
        DB::table('uj_ditahan_payout_cancellations')->insert(['fund' => $fund, 'detail_id' => $id, 'account_id' => 1, 'nominal' => 400000, 'reason' => 'Pembatalan versi sebelumnya', 'cancelled_by' => auth()->id(), 'created_at' => now(), 'updated_at' => now()]);
        (require database_path('migrations/2026_11_01_175900_add_reversal_detail_to_uj_ditahan_payout_cancellations.php'))->up();
        $this->assertDatabaseHas($accounts, ['id' => 1, 'saldo' => 1000000, 'total_masuk' => 1400000, 'total_keluar' => 400000]);
        $this->assertDatabaseHas($details, ['jenis' => 'masuk', 'nominal' => 400000, 'payout_origin' => 'payout_reversal']);
        $this->assertNotNull(DB::table('uj_ditahan_payout_cancellations')->value('reversal_detail_id'));
        $this->post(route('billing.uj-ditahan.cancel', ['fund' => $fund, 'detail' => $id]), ['reason' => 'Ulang'])->assertSessionHas('error');
        $this->assertDatabaseCount($details, 2);
    }

    public function test_failed_reversal_write_rolls_back_audit_and_balance(): void
    {
        $id = $this->setupPayout('ban');
        UjDitahanBanDetail::creating(function ($detail): void {
            if ($detail->payout_origin === 'payout_reversal') {
                throw new \RuntimeException('Reversal storage failure');
            }
        });
        try {
            $this->post(route('billing.uj-ditahan.cancel', ['fund' => 'ban', 'detail' => $id]), ['reason' => 'Koreksi'])->assertSessionHas('error');
            $this->assertDatabaseCount('uj_ditahan_payout_cancellations', 0);
            $this->assertDatabaseCount('uj_ditahan_ban_details', 1);
            $this->assertDatabaseHas('uj_ditahan_bans', ['saldo' => 600000, 'total_masuk' => 1000000, 'total_keluar' => 400000]);
        } finally {
            UjDitahanBanDetail::flushEventListeners();
        }
    }
}
