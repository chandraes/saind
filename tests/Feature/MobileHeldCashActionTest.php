<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UjDitahanWhatsAppService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;

/** Modul 5c: Admin & SU melakukan cutoff UJ Ditahan dan membatalkan pencairan dari mobile. */
class MobileHeldCashActionTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::table('vehicles', fn (Blueprint $table) => $table->unsignedBigInteger('driver_id')->nullable());
        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            foreach (['bank', 'no_rek', 'nama_rek', 'no_hp'] as $field) {
                $table->string($field)->nullable();
            }
        });
        Schema::create('uj_ditahan_payout_cancellations', function (Blueprint $table): void {
            $table->id();
            $table->string('fund', 12);
            foreach (['detail_id', 'account_id', 'cancelled_by'] as $field) {
                $table->unsignedBigInteger($field);
            }
            $table->unsignedBigInteger('reversal_detail_id')->nullable();
            $table->bigInteger('nominal');
            $table->text('reason');
            $table->timestamps();
        });
        foreach (['sparepart' => 'uj_ditahans', 'ban' => 'uj_ditahan_bans'] as $fund => $accounts) {
            $foreign = $fund === 'ban' ? 'uj_ditahan_ban_id' : 'uj_ditahan_id';
            Schema::create($accounts, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('vehicle_id');
                $table->integer('bulan');
                $table->integer('tahun');
                foreach (['total_masuk', 'total_keluar', 'saldo'] as $field) {
                    $table->bigInteger($field)->default(0);
                }
                $table->timestamps();
            });
            Schema::create($fund === 'ban' ? 'uj_ditahan_ban_details' : 'uj_ditahan_details', function (Blueprint $table) use ($foreign): void {
                $table->id();
                $table->unsignedBigInteger($foreign);
                foreach (['driver_id', 'transaksi_id', 'aki_ganti_invoice_id', 'filter_oli_ganti_invoice_id', 'ban_ganti_invoice_id'] as $field) {
                    $table->unsignedBigInteger($field)->nullable();
                }
                $table->string('jenis');
                $table->bigInteger('nominal');
                foreach (['keterangan', 'payout_origin', 'file_pdf', 'bank', 'no_rekening', 'nama_rekening', 'request_token'] as $field) {
                    $table->string($field)->nullable();
                }
                $table->timestamps();
            });
            DB::table($accounts)->insert(['id' => 1, 'vehicle_id' => 1, 'bulan' => 11, 'tahun' => 2026, 'total_masuk' => 900000, 'total_keluar' => 300000, 'saldo' => 600000]);
            DB::table($fund === 'ban' ? 'uj_ditahan_ban_details' : 'uj_ditahan_details')->insert([
                [$foreign => 1, 'jenis' => 'masuk', 'nominal' => 900000, 'keterangan' => 'Setoran', 'payout_origin' => null, 'file_pdf' => null, 'created_at' => '2026-11-03 08:00:00'],
                [$foreign => 1, 'jenis' => 'keluar', 'nominal' => 300000, 'keterangan' => 'Pencairan', 'payout_origin' => 'manual', 'file_pdf' => 'bukti.pdf', 'created_at' => '2026-11-04 08:00:00'],
            ]);
        }
        DB::table('vendors')->insert(['id' => 1, 'nama' => 'Vendor A']);
        DB::table('drivers')->insert(['id' => 1, 'nama' => 'Driver Satu', 'bank' => 'BRI', 'no_rek' => '0001', 'nama_rek' => 'Driver Satu']);
        DB::table('vehicles')->insert(['id' => 1, 'vendor_id' => 1, 'driver_id' => 1, 'nomor_lambung' => '288']);
    }

    private function headers(string $role): array
    {
        $user = User::factory()->create(['username' => Str::uuid()->toString(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => $role === 'vendor-induk' ? 1 : null]);
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    private function cutoff(int $balance, ?string $token = null): array
    {
        return ['month' => 11, 'year' => 2026, 'balance' => $balance, 'request_token' => $token ?? (string) Str::uuid()];
    }

    #[TestWith(['sparepart', 'uj_ditahans', 'uj_ditahan_details'])]
    #[TestWith(['ban', 'uj_ditahan_bans', 'uj_ditahan_ban_details'])]
    public function test_admin_cuts_off_a_period_to_the_driver_once(string $fund, string $accounts, string $ledger): void
    {
        $this->mock(UjDitahanWhatsAppService::class)->shouldReceive('payout')->once();
        $headers = $this->headers('admin');
        $this->getJson("/api/v1/vehicles/1/held-cash/$fund?month=11&year=2026", $headers)->assertOk()
            ->assertJsonPath('meta.account_balance', 600000)->assertJsonPath('data.1.cancellable', true)->assertJsonPath('data.0.cancellable', false);

        $this->postJson("/api/v1/vehicles/1/held-cash/$fund/cutoff", $this->cutoff(500000), $headers)
            ->assertUnprocessable()->assertJsonPath('errors.balance.0', 'Saldo periode berubah sejak dibuka. Muat ulang dan periksa kembali.');
        $this->postJson("/api/v1/vehicles/1/held-cash/$fund/cutoff", $this->cutoff(600000), $headers)
            ->assertOk()->assertJsonPath('message', 'Cutoff berhasil. Saldo periode menjadi nol dan dicairkan ke rekening driver.')->assertJsonMissingPath('warning');
        $this->assertDatabaseHas($accounts, ['id' => 1, 'saldo' => 0, 'total_keluar' => 900000]);
        $this->assertDatabaseHas($ledger, ['jenis' => 'keluar', 'nominal' => 600000, 'keterangan' => 'Cutoff', 'bank' => 'BRI', 'no_rekening' => '0001', 'nama_rekening' => 'Driver Satu']);

        // Diketuk ulang: saldo sudah nol, tidak ada cutoff kedua.
        $this->postJson("/api/v1/vehicles/1/held-cash/$fund/cutoff", $this->cutoff(0), $headers)->assertUnprocessable();
        $this->assertSame(1, DB::table($ledger)->where('keterangan', 'Cutoff')->count());
    }

    #[TestWith(['sparepart', 'uj_ditahans', 'uj_ditahan_details'])]
    #[TestWith(['ban', 'uj_ditahan_bans', 'uj_ditahan_ban_details'])]
    public function test_su_cancels_a_manual_payout_once(string $fund, string $accounts, string $ledger): void
    {
        $this->mock(UjDitahanWhatsAppService::class)->shouldReceive('payoutCancellation')->once()->andThrow(new \RuntimeException('WA mati'));
        $headers = $this->headers('su');
        $this->postJson("/api/v1/held-cash/$fund/details/2/cancel", ['reason' => ''], $headers)->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->postJson("/api/v1/held-cash/$fund/details/1/cancel", ['reason' => 'Salah'], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('payout');
        $this->postJson("/api/v1/held-cash/$fund/details/2/cancel", ['reason' => 'Rekening salah'], $headers)->assertOk()
            ->assertJsonPath('message', 'Pencairan dibatalkan. Saldo dikembalikan ke periode asal dan histori tetap tersimpan.')
            ->assertJsonPath('warning', 'Transaksi sudah tersimpan, tetapi sebagian notifikasi WA belum berhasil dikirim. Jangan ulangi.');
        $this->assertDatabaseHas($accounts, ['id' => 1, 'saldo' => 900000, 'total_masuk' => 1200000]);
        $this->assertDatabaseHas('uj_ditahan_payout_cancellations', ['fund' => $fund, 'detail_id' => 2, 'nominal' => 300000, 'reason' => 'Rekening salah']);
        $this->assertDatabaseHas($ledger, ['jenis' => 'masuk', 'payout_origin' => 'payout_reversal', 'nominal' => 300000]);
        $this->getJson("/api/v1/vehicles/1/held-cash/$fund?month=11&year=2026", $headers)->assertOk()->assertJsonPath('data.1.cancellable', false);
        $this->postJson("/api/v1/held-cash/$fund/details/2/cancel", ['reason' => 'Lagi'], $headers)
            ->assertUnprocessable()->assertJsonPath('errors.payout.0', 'Pencairan ini sudah dibatalkan.');
    }

    private function withdrawal(UploadedFile $proof, array $overrides = []): array
    {
        return $overrides + ['month' => 11, 'year' => 2026, 'balance' => 600000, 'nominal' => 250000, 'keterangan' => 'Pencairan servis',
            'bank' => 'BRI', 'no_rekening' => '0001', 'nama_rekening' => 'Driver Satu', 'request_token' => (string) Str::uuid(), 'bukti' => $proof];
    }

    #[TestWith(['sparepart', 'uj_ditahans', 'uj_ditahan_details'])]
    #[TestWith(['ban', 'uj_ditahan_bans', 'uj_ditahan_ban_details'])]
    public function test_admin_withdraws_with_a_pdf_proof(string $fund, string $accounts, string $ledger): void
    {
        Storage::fake('local');
        $this->mock(UjDitahanWhatsAppService::class)->shouldReceive('payout')->once();
        $headers = $this->headers('admin');
        $this->getJson("/api/v1/vehicles/1/held-cash/$fund?month=11&year=2026", $headers)->assertOk()
            ->assertJsonPath('meta.driver_account', ['bank' => 'BRI', 'number' => '0001', 'name' => 'Driver Satu']);
        $pdf = fn () => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf');

        $this->post("/api/v1/vehicles/1/held-cash/$fund/withdraw", $this->withdrawal($pdf(), ['balance' => 1]), $headers)
            ->assertUnprocessable()->assertJsonPath('errors.balance.0', 'Saldo periode berubah sejak dibuka. Muat ulang dan periksa kembali.');
        $this->post("/api/v1/vehicles/1/held-cash/$fund/withdraw", $this->withdrawal($pdf(), ['nominal' => 700000]), $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('nominal');
        $this->post("/api/v1/vehicles/1/held-cash/$fund/withdraw", $this->withdrawal($pdf()), $headers)
            ->assertOk()->assertJsonPath('message', 'Pencairan UJ Ditahan berhasil dicatat.');
        $this->assertDatabaseHas($accounts, ['id' => 1, 'saldo' => 350000, 'total_keluar' => 550000]);
        $row = DB::table($ledger)->where('keterangan', 'Pencairan servis')->first();
        $this->assertSame('manual', $row->payout_origin);
        $this->assertSame(250000, (int) $row->nominal);
        Storage::disk('local')->assertExists($row->file_pdf);

        // Dikirim ulang dengan saldo lama: ditolak, tidak dicairkan dua kali.
        $this->post("/api/v1/vehicles/1/held-cash/$fund/withdraw", $this->withdrawal($pdf()), $headers)->assertUnprocessable();
        $this->assertSame(1, DB::table($ledger)->where('keterangan', 'Pencairan servis')->count());
    }

    public function test_a_photo_proof_is_stored_as_pdf(): void
    {
        Storage::fake('local');
        $this->mock(UjDitahanWhatsAppService::class)->shouldReceive('payout')->once();
        $this->post('/api/v1/vehicles/1/held-cash/sparepart/withdraw', $this->withdrawal(UploadedFile::fake()->image('bukti.jpg', 400, 600)), $this->headers('su'))->assertOk();
        $path = DB::table('uj_ditahan_details')->where('keterangan', 'Pencairan servis')->value('file_pdf');
        $this->assertStringEndsWith('.pdf', $path);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($path));
        $this->post('/api/v1/vehicles/1/held-cash/sparepart/withdraw', $this->withdrawal(UploadedFile::fake()->create('bukti.txt', 1, 'text/plain'), ['balance' => 350000]), $this->headers('admin'))
            ->assertUnprocessable()->assertJsonValidationErrors('bukti');
    }

    public function test_an_older_balance_must_be_used_first(): void
    {
        DB::table('uj_ditahans')->insert(['vehicle_id' => 1, 'bulan' => 10, 'tahun' => 2026, 'saldo' => 1000]);
        $this->post('/api/v1/vehicles/1/held-cash/sparepart/withdraw', $this->withdrawal(UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf')), $this->headers('admin'))
            ->assertUnprocessable()->assertJsonPath('errors.nominal.0', 'Silahkan gunakan Saldo UJ Ditahan pada bulan sebelumnya terlebih dahulu.');
        $this->assertDatabaseHas('uj_ditahans', ['id' => 1, 'saldo' => 600000]);
    }

    public function test_other_roles_cannot_cut_off_or_cancel(): void
    {
        foreach (['operasional', 'general-manager', 'vendor-induk'] as $role) {
            $headers = $this->headers($role);
            $this->postJson('/api/v1/vehicles/1/held-cash/sparepart/cutoff', $this->cutoff(600000), $headers)->assertForbidden();
            $this->postJson('/api/v1/held-cash/sparepart/details/2/cancel', ['reason' => 'x'], $headers)->assertForbidden();
            $this->post('/api/v1/vehicles/1/held-cash/sparepart/withdraw', [], $headers)->assertForbidden();
            $this->getJson('/api/v1/vehicles/1/held-cash/sparepart?month=11&year=2026', $headers)
                ->assertJsonMissingPath('meta.account_balance')->assertJsonMissingPath('meta.driver_account')->assertJsonMissingPath('data.0.cancellable');
        }
        $this->assertDatabaseHas('uj_ditahans', ['id' => 1, 'saldo' => 600000]);
    }
}
