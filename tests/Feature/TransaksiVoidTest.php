<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\Transaksi;
use App\Models\UjDitahanDetail;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class TransaksiVoidTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::connection()->getPdo()->sqliteCreateFunction('GREATEST', fn ($left, $right) => max($left, $right), 2);
        foreach (['vendors', 'customers', 'rutes'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama')->nullable();
                $table->string('singkatan')->nullable();
                $table->decimal('jarak')->default(100);
            });
        }
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_lambung')->default('UJI');
            $table->string('status')->default('jalan');
            $table->integer('do_count')->default(2);
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->timestamps();
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            foreach (['vehicle_id', 'vendor_id', 'customer_id', 'rute_id', 'nomor_uang_jalan', 'jenis_transaksi_id'] as $column) {
                $table->unsignedBigInteger($column)->nullable();
            }
            $table->boolean('void')->default(false);
            foreach (['tanggal', 'kode_void', 'transfer_ke', 'bank', 'no_rekening'] as $column) {
                $table->string($column)->nullable();
            }
            $table->bigInteger('nominal_transaksi');
            $table->bigInteger('saldo');
            $table->timestamps();
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kas_uang_jalan_id');
            $table->boolean('void')->default(false);
            $table->boolean('nota_fisik')->default(false);
            $table->integer('status')->default(1);
            foreach (['alasan', 'nota_muat', 'nota_bongkar'] as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('rekenings', function (Blueprint $table): void {
            $table->id();
            foreach (['untuk', 'nama_rekening', 'nama_bank', 'nomor_rekening'] as $column) {
                $table->string($column);
            }
        });
        (require database_path('migrations/2026_08_07_071658_create_uj_ditahans_table.php'))->up();
        (require database_path('migrations/2026_08_07_071708_create_uj_ditahan_details_table.php'))->up();
        (require database_path('migrations/2026_08_07_153001_add_rekening_to_uj_ditahan_details_table.php'))->up();
        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('ban_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->integer('posisi_ban_id')->default(1);
            $table->timestamps();
            $table->decimal('ritase');
        });
        Schema::create('ban_log_transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transaksi_id');
            $table->unsignedBigInteger('ban_log_id');
            $table->decimal('nilai_ritase');
        });
        Schema::create('filter_oli_logs', function (Blueprint $table): void {
            $table->id();
            $table->decimal('ritase');
            $table->timestamps();
        });
        Schema::create('filter_oli_log_transaksis', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('transaksi_id');
            $table->unsignedBigInteger('filter_oli_log_id');
            $table->decimal('nilai_ritase');
        });
        Schema::create('filter_oli_ganti_invoice_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('filter_oli_log_id');
            $table->decimal('ritase');
            $table->timestamps();
        });
        (require database_path('migrations/2023_09_06_160726_create_group_was_table.php'))->up();
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
    }

    private function transaction(): Transaksi
    {
        DB::table('drivers')->insert([['id' => 1], ['id' => 2]]);
        foreach (['vendors', 'customers', 'rutes'] as $name) {
            DB::table($name)->insert(['id' => 1, 'nama' => 'Uji', 'singkatan' => 'UJI']);
        }
        DB::table('vehicles')->insert(['id' => 1, 'driver_id' => 2]);
        DB::table('rekenings')->insert(['untuk' => 'kas-uang-jalan', 'nama_rekening' => 'Kas', 'nama_bank' => 'BCA', 'nomor_rekening' => '123']);
        $cash = DB::table('kas_uang_jalans')->insertGetId(['vehicle_id' => 1, 'vendor_id' => 1, 'customer_id' => 1, 'rute_id' => 1, 'nomor_uang_jalan' => 12, 'nominal_transaksi' => 500000, 'saldo' => 1000000]);

        return Transaksi::factory()->create(['kas_uang_jalan_id' => $cash, 'nota_muat' => 'NM', 'nota_bongkar' => 'NB']);
    }

    private function held(Transaksi $transaction): int
    {
        $master = DB::table('uj_ditahans')->insertGetId(['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'total_masuk' => 100000, 'total_keluar' => 0, 'saldo' => 100000]);
        UjDitahanDetail::create(['uj_ditahan_id' => $master, 'transaksi_id' => $transaction->id, 'driver_id' => 1, 'jenis' => 'masuk', 'nominal' => 100000, 'bank' => 'BRI', 'no_rekening' => '456', 'nama_rekening' => 'Asal']);

        return $master;
    }

    #[TestWith(['transaksi.void.store'])]
    #[TestWith(['transaksi.tagihan.void.store'])]
    public function test_both_endpoints_reverse_cash_held_money_and_ritase_once(string $route): void
    {
        $transaction = $this->transaction();
        $master = $this->held($transaction);
        DB::table('ban_logs')->insert(['id' => 1, 'vehicle_id' => 1, 'ritase' => 5]);
        DB::table('ban_log_transaksis')->insert(['transaksi_id' => $transaction->id, 'ban_log_id' => 1, 'nilai_ritase' => 0.5]);
        DB::table('filter_oli_logs')->insert(['id' => 1, 'ritase' => 5]);
        DB::table('filter_oli_log_transaksis')->insert(['transaksi_id' => $transaction->id, 'filter_oli_log_id' => 1, 'nilai_ritase' => 0.5]);
        DB::table('filter_oli_ganti_invoice_details')->insert(['filter_oli_log_id' => 1, 'ritase' => 5]);

        $this->post(route($route, $transaction), ['alasan' => 'Dibatalkan'])->assertRedirect(route('billing.index'));
        $this->assertTrue(session()->has('success'), (string) session('error'));
        $this->assertDatabaseHas('transaksis', ['id' => $transaction->id, 'void' => 1, 'nota_muat' => null, 'nota_bongkar' => null]);
        $this->assertDatabaseHas('kas_uang_jalans', ['kode_void' => 'UJ12', 'nominal_transaksi' => 500000, 'saldo' => 1500000]);
        $this->assertDatabaseHas('uj_ditahan_details', ['uj_ditahan_id' => $master, 'transaksi_id' => $transaction->id, 'jenis' => 'keluar', 'nominal' => 100000, 'driver_id' => 1, 'bank' => 'BRI', 'no_rekening' => '456']);
        $this->assertDatabaseHas('uj_ditahans', ['id' => $master, 'saldo' => 0, 'total_keluar' => 100000]);
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 1, 'status' => 'aktif']);
        $this->assertDatabaseHas('ban_logs', ['id' => 1, 'ritase' => 4.5]);
        $this->assertDatabaseHas('filter_oli_logs', ['id' => 1, 'ritase' => 4.5]);
        $this->assertDatabaseHas('filter_oli_ganti_invoice_details', ['filter_oli_log_id' => 1, 'ritase' => 4.5]);
        $this->assertDatabaseCount('ban_log_transaksis', 0);
        $this->assertDatabaseCount('filter_oli_log_transaksis', 0);
        $otherRoute = $route === 'transaksi.void.store' ? 'transaksi.tagihan.void.store' : 'transaksi.void.store';
        $this->post(route($otherRoute, $transaction), ['alasan' => 'Ulang'])->assertSessionHas('error', 'Transaksi sudah di void sebelumnya!!');
        $this->assertDatabaseCount('kas_uang_jalans', 2);
        $this->assertDatabaseCount('uj_ditahan_details', 2);
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 1]);
        $this->assertSame(0, DB::transactionLevel());
    }

    #[TestWith(['transaksi.void.store'])]
    #[TestWith(['transaksi.tagihan.void.store'])]
    public function test_detail_failure_rolls_back_the_entire_void(string $route): void
    {
        $transaction = $this->transaction();
        $master = $this->held($transaction);
        DB::statement("CREATE TRIGGER reject_void_detail BEFORE INSERT ON uj_ditahan_details WHEN NEW.jenis = 'keluar' BEGIN SELECT RAISE(ABORT, 'Gagal detail'); END");
        $this->post(route($route, $transaction), ['alasan' => 'Batal'])->assertSessionHas('error');
        $this->assertDatabaseHas('transaksis', ['id' => $transaction->id, 'void' => 0, 'nota_muat' => 'NM']);
        $this->assertDatabaseCount('kas_uang_jalans', 1);
        $this->assertDatabaseCount('uj_ditahan_details', 1);
        $this->assertDatabaseHas('uj_ditahans', ['id' => $master, 'saldo' => 100000, 'total_keluar' => 0]);
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 2, 'status' => 'jalan']);
        $this->assertSame(0, DB::transactionLevel());
    }

    #[TestWith(['transaksi.void.store'])]
    #[TestWith(['transaksi.tagihan.void.store'])]
    public function test_void_without_held_money_keeps_other_active_trip_and_does_not_reduce_received_do(string $route): void
    {
        $transaction = $this->transaction();
        $transaction->update(['nota_fisik' => 1]);
        Transaksi::factory()->create(['kas_uang_jalan_id' => $transaction->kas_uang_jalan_id]);
        $this->post(route($route, $transaction), ['alasan' => 'Batal'])->assertSessionHas('success');
        $this->assertDatabaseCount('uj_ditahan_details', 0);
        $this->assertDatabaseHas('vehicles', ['id' => 1, 'do_count' => 2, 'status' => 'jalan']);
        $this->assertDatabaseHas('kas_uang_jalans', ['kode_void' => 'UJ12', 'saldo' => 1500000]);
    }

    #[TestWith(['transaksi.void.store'])]
    #[TestWith(['transaksi.tagihan.void.store'])]
    public function test_invalid_reason_does_not_write(string $route): void
    {
        $transaction = $this->transaction();
        foreach (['', str_repeat('a', 201), ['array']] as $reason) {
            $this->post(route($route, $transaction), ['alasan' => $reason])->assertSessionHasErrors('alasan');
        }
        $this->assertDatabaseHas('transaksis', ['id' => $transaction->id, 'void' => 0]);
        $this->assertDatabaseCount('kas_uang_jalans', 1);
    }

    public function test_held_money_notification_is_sent_without_the_main_group(): void
    {
        $transaction = $this->transaction();
        $this->held($transaction);
        GroupWa::create(['untuk' => 'kas-uj-ditahan', 'nama_group' => 'held-test']);
        $messenger = new class extends GroupWa
        {
            protected $table = 'group_was';

            public array $sent = [];

            public function sendWa($tujuan, $pesan)
            {
                $this->sent[] = ['destination' => $tujuan, 'message' => $pesan];
            }
        };
        $this->app->instance(GroupWa::class, $messenger);
        $this->post(route('transaksi.tagihan.void.store', $transaction), ['alasan' => 'Batal'])->assertSessionHas('success');
        $this->assertCount(1, $messenger->sent);
        $this->assertSame('held-test', $messenger->sent[0]['destination']);
        $this->assertStringContainsString('*Void UJ Ditahan*', $messenger->sent[0]['message']);
        $this->assertStringContainsString('100.000', $messenger->sent[0]['message']);
    }

    public function test_notification_failure_does_not_undo_void_or_block_held_notification(): void
    {
        $transaction = $this->transaction();
        $master = $this->held($transaction);
        GroupWa::create(['untuk' => 'kas-uang-jalan', 'nama_group' => 'main-test']);
        GroupWa::create(['untuk' => 'kas-uj-ditahan', 'nama_group' => 'held-test']);
        $messenger = new class extends GroupWa
        {
            protected $table = 'group_was';

            public array $sent = [];

            public function sendWa($tujuan, $pesan)
            {
                if ($tujuan === 'main-test') {
                    throw new \RuntimeException('Simulasi WA gagal');
                }
                $this->sent[] = $tujuan;
            }
        };
        $this->app->instance(GroupWa::class, $messenger);
        $this->post(route('transaksi.void.store', $transaction), ['alasan' => 'Batal'])->assertSessionHas('success');
        $this->assertSame(['held-test'], $messenger->sent);
        $this->assertDatabaseHas('transaksis', ['id' => $transaction->id, 'void' => 1]);
        $this->assertDatabaseHas('uj_ditahans', ['id' => $master, 'saldo' => 0, 'total_keluar' => 100000]);
    }

    public function test_outgoing_detail_is_not_used_as_the_source_of_held_money(): void
    {
        $transaction = $this->transaction();
        $master = DB::table('uj_ditahans')->insertGetId(['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'saldo' => 75000]);
        UjDitahanDetail::create(['uj_ditahan_id' => $master, 'transaksi_id' => $transaction->id, 'jenis' => 'keluar', 'nominal' => 25000]);
        UjDitahanDetail::create(['uj_ditahan_id' => $master, 'transaksi_id' => $transaction->id, 'jenis' => 'masuk', 'nominal' => 100000]);
        $this->post(route('transaksi.void.store', $transaction), ['alasan' => 'Batal'])->assertSessionHas('success');
        $this->assertDatabaseHas('uj_ditahan_details', ['uj_ditahan_id' => $master, 'jenis' => 'keluar', 'nominal' => 100000]);
        $this->assertDatabaseHas('uj_ditahans', ['id' => $master, 'saldo' => -25000, 'total_keluar' => 100000]);
    }

    #[TestWith(['transaksi.void.store'])]
    #[TestWith(['transaksi.tagihan.void.store'])]
    public function test_guest_and_operational_role_cannot_void(string $route): void
    {
        $transaction = $this->transaction();
        auth()->forgetGuards();
        $this->post(route($route, $transaction), ['alasan' => 'Batal'])->assertRedirect('/login');
        $this->actingAs(User::factory()->make(['id' => 2, 'role' => 'operasional', 'password' => 'password']));
        $this->post(route($route, $transaction), ['alasan' => 'Batal'])->assertForbidden();
        $this->assertDatabaseHas('transaksis', ['id' => $transaction->id, 'void' => 0]);
        $this->assertDatabaseCount('kas_uang_jalans', 1);
    }
}
