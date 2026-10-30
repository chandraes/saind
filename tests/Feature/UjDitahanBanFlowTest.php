<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\Transaksi;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UjDitahanBanFlowTest extends TestCase
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
        $this->prepareUangJalanTables();
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
    }

    private function fixture(): Transaksi
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

    private function prepareUangJalanTables(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            $table->string('pembayaran')->default('opname');
            $table->boolean('limit_tonase')->default(false);
            $table->string('no_hp')->nullable();
        });
        Schema::table('customers', function (Blueprint $table): void {
            $table->boolean('csr')->default(false);
        });
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->unsignedBigInteger('vendor_id')->default(1);
            $table->boolean('uj_ditahan')->default(true);
            $table->boolean('lock_uj')->default(false);
            $table->boolean('pembatasan_filter_oli')->default(false);
            foreach (['tanggal_pajak_stnk', 'tanggal_kir', 'tanggal_kimper', 'tanggal_sim'] as $field) {
                $table->date($field)->nullable();
            }
        });
        Schema::table('transaksis', function (Blueprint $table): void {
            foreach (['harga_customer', 'harga_vendor', 'harga_csr', 'tonase', 'gross_muat', 'tarra_muat'] as $field) {
                $table->decimal($field)->default(0);
            }
            $table->date('tanggal_muat')->nullable();
        });
        Schema::table('filter_oli_logs', function (Blueprint $table): void {
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('kategori_filter_oli_mesin_id');
        });
        Schema::table('drivers', function (Blueprint $table): void {
            $table->string('no_hp')->nullable();
        });
        foreach (['konfigurasis', 'pengaturans'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('kode')->nullable();
                $table->string('untuk')->nullable();
                $table->integer('status')->default(0);
                $table->integer('nilai')->default(0);
            });
        }
        Schema::create('customer_tagihans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('rute_id');
            $table->integer('harga_tagihan')->default(1000);
            $table->integer('opname')->default(900);
        });
        Schema::create('customer_rute', function (Blueprint $table): void {
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('rute_id');
        });
        Schema::create('vendor_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('rute_id');
            $table->integer('hk_uang_jalan');
            $table->integer('uj_ditahan');
            $table->integer('uj_ditahan_ban')->default(0);
        });
        foreach (['2026_10_29_102252_create_uj_ditahan_bans_table.php', '2026_10_29_102253_create_uj_ditahan_ban_details_table.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        $messenger = new class extends GroupWa
        {
            protected $table = 'group_was';

            public array $sent = [];

            public function sendWa($tujuan, $pesan)
            {
                $this->sent[] = ['to' => $tujuan, 'text' => $pesan];
            }
        };
        $this->app->instance(GroupWa::class, $messenger);
    }

    private function setupExit(): array
    {
        $this->fixture();
        DB::table('vehicles')->where('id', 1)->update(['tanggal_pajak_stnk' => today()->addYear()->toDateString()]);
        DB::table('customer_tagihans')->insert(['customer_id' => 1, 'rute_id' => 1]);
        DB::table('customer_rute')->insert(['customer_id' => 1, 'rute_id' => 1]);
        DB::table('vendor_uang_jalans')->insert(['vendor_id' => 1, 'rute_id' => 1, 'hk_uang_jalan' => 300000, 'uj_ditahan' => 50000, 'uj_ditahan_ban' => 20000]);
        DB::table('rekenings')->insert(['untuk' => 'uang-jalan-ditahan', 'nama_rekening' => 'Ditahan', 'nama_bank' => 'BRI', 'nomor_rekening' => '789']);
        GroupWa::create(['untuk' => 'kas-uang-jalan', 'nama_group' => 'uj-test']);
        GroupWa::create(['untuk' => 'kas-uj-ditahan', 'nama_group' => 'ditahan-test']);

        return ['vehicle_id' => 1, 'customer_id' => 1, 'rute_id' => 1, 'p_vendor' => 1, 'nominal_transaksi' => '300000', 'transfer_ke' => 'Driver', 'bank' => 'BCA', 'no_rekening' => '123', 'nota_muat' => 'NEW', 'tonase' => 30, 'tanggal_muat' => today()->toDateString()];
    }

    public function test_exit_credits_both_funds_and_gross_cash_in_one_transaction(): void
    {
        $this->post(route('kas-uang-jalan.keluar.store'), $this->setupExit())->assertSessionHas('success');
        $this->assertDatabaseHas('kas_uang_jalans', ['nomor_uang_jalan' => 13, 'nominal_transaksi' => 370000, 'saldo' => 630000]);
        $this->assertDatabaseHas('uj_ditahans', ['vehicle_id' => 1, 'bulan' => now()->month, 'saldo' => 50000]);
        $this->assertDatabaseHas('uj_ditahan_bans', ['vehicle_id' => 1, 'bulan' => now()->month, 'saldo' => 20000]);
        $this->assertDatabaseHas('uj_ditahan_ban_details', ['jenis' => 'masuk', 'nominal' => 20000, 'transaksi_id' => 2, 'bank' => 'BRI']);
        $messages = implode(' ', array_column(app(GroupWa::class)->sent, 'text'));
        $this->assertStringContainsString('Form Uang Jalan Ditahan', $messages);
        $this->assertStringContainsString('300.000', $messages);
    }

    #[TestWith(['admin'])]
    #[TestWith(['user'])]
    public function test_exit_rejects_missing_ban_amount_before_any_writes(string $role): void
    {
        $data = $this->setupExit();
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role, 'password' => 'password']));
        DB::table('vendor_uang_jalans')->update(['uj_ditahan_ban' => 0]);
        $this->post(route('kas-uang-jalan.keluar.store'), $data)->assertSessionHas('error');
        $this->assertDatabaseCount('transaksis', 1);
        $this->assertDatabaseCount('uj_ditahans', 0);
        $this->assertDatabaseCount('uj_ditahan_bans', 0);
    }

    public function test_exit_rollback_includes_cash_and_sparepart_if_ban_insert_fails(): void
    {
        $data = $this->setupExit();
        DB::unprepared("CREATE TRIGGER fail_ban_credit BEFORE INSERT ON uj_ditahan_ban_details BEGIN SELECT RAISE(ABORT, 'failure'); END");
        $this->post(route('kas-uang-jalan.keluar.store'), $data)->assertSessionHas('error');
        $this->assertDatabaseCount('transaksis', 1);
        $this->assertDatabaseCount('kas_uang_jalans', 1);
        $this->assertDatabaseCount('uj_ditahans', 0);
        $this->assertDatabaseCount('uj_ditahan_bans', 0);
    }

    #[TestWith(['transaksi.void.store'])]
    #[TestWith(['transaksi.tagihan.void.store'])]
    public function test_void_reverses_ban_in_original_period_once(string $route): void
    {
        $transaction = $this->fixture();
        $master = UjDitahanBan::factory()->create(['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'total_masuk' => 20000, 'saldo' => 20000]);
        UjDitahanBanDetail::factory()->for($master, 'ujDitahanBan')->create(['transaksi_id' => $transaction->id, 'nominal' => 20000]);
        $this->post(route($route, $transaction), ['alasan' => 'Batal'])->assertSessionHas('success');
        $this->assertSame(0, $master->fresh()->saldo);
        $this->assertDatabaseHas('uj_ditahan_ban_details', ['uj_ditahan_ban_id' => $master->id, 'jenis' => 'keluar', 'nominal' => 20000]);
        $this->post(route($route, $transaction), ['alasan' => 'Ulang'])->assertSessionHas('error');
        $this->assertDatabaseCount('uj_ditahan_ban_details', 2);
    }

    public function test_flag_off_does_not_add_or_record_held_funds(): void
    {
        $data = $this->setupExit();
        DB::table('vehicles')->where('id', 1)->update(['uj_ditahan' => false]);
        DB::table('vendor_uang_jalans')->update(['uj_ditahan_ban' => 0]);
        $this->post(route('kas-uang-jalan.keluar.store'), $data)->assertSessionHas('success');
        $this->assertDatabaseHas('kas_uang_jalans', ['nomor_uang_jalan' => 13, 'nominal_transaksi' => 300000, 'saldo' => 700000]);
        $this->assertDatabaseCount('uj_ditahans', 0);
        $this->assertDatabaseCount('uj_ditahan_bans', 0);
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    public function test_admin_and_su_can_change_transfer_while_both_held_amounts_stay_fixed(string $role): void
    {
        $data = $this->setupExit();
        $data['nominal_transaksi'] = '350000';
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role, 'password' => 'password']));
        $this->post(route('kas-uang-jalan.keluar.store'), $data)->assertSessionHas('success');
        $this->assertDatabaseHas('kas_uang_jalans', ['nomor_uang_jalan' => 13, 'nominal_transaksi' => 420000]);
        $this->assertDatabaseHas('uj_ditahans', ['saldo' => 50000]);
        $this->assertDatabaseHas('uj_ditahan_bans', ['saldo' => 20000]);
    }

    public function test_user_cannot_change_transfer_and_valid_user_transfer_succeeds(): void
    {
        $data = $this->setupExit();
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'user', 'password' => 'password']));
        $data['nominal_transaksi'] = '350000';
        $this->post(route('kas-uang-jalan.keluar.store'), $data)->assertSessionHas('error');
        $this->assertDatabaseCount('uj_ditahan_bans', 0);
        $data['nominal_transaksi'] = '300000';
        $this->post(route('kas-uang-jalan.keluar.store'), $data)->assertSessionHas('success');
        $this->assertDatabaseHas('uj_ditahan_bans', ['saldo' => 20000]);
    }

    public function test_void_failure_in_ban_reversal_rolls_back_cash_sparepart_and_transaction(): void
    {
        $transaction = $this->fixture();
        $sparepart = DB::table('uj_ditahans')->insertGetId(['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'total_masuk' => 50000, 'saldo' => 50000]);
        UjDitahanDetail::create(['uj_ditahan_id' => $sparepart, 'transaksi_id' => $transaction->id, 'jenis' => 'masuk', 'nominal' => 50000]);
        $ban = UjDitahanBan::factory()->create(['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'total_masuk' => 20000, 'saldo' => 20000]);
        UjDitahanBanDetail::factory()->for($ban, 'ujDitahanBan')->create(['transaksi_id' => $transaction->id, 'nominal' => 20000]);
        DB::unprepared("CREATE TRIGGER fail_ban_void BEFORE INSERT ON uj_ditahan_ban_details WHEN NEW.jenis='keluar' BEGIN SELECT RAISE(ABORT, 'failure'); END");
        $this->post(route('transaksi.void.store', $transaction), ['alasan' => 'Batal'])->assertSessionHas('error');
        $this->assertDatabaseHas('transaksis', ['id' => $transaction->id, 'void' => false]);
        $this->assertDatabaseCount('kas_uang_jalans', 1);
        $this->assertDatabaseHas('uj_ditahans', ['id' => $sparepart, 'saldo' => 50000, 'total_keluar' => 0]);
        $this->assertSame(20000, $ban->fresh()->saldo);
        $this->assertDatabaseCount('uj_ditahan_details', 1);
        $this->assertDatabaseCount('uj_ditahan_ban_details', 1);
    }

    public function test_route_lookup_returns_both_agreed_held_amounts(): void
    {
        $this->setupExit();
        $this->getJson(route('kas-uang-jalan.get-rute', ['id' => 1, 'vehicle_id' => 1]))->assertOk()->assertJsonPath('rute.0.uj_ditahan', 50000)->assertJsonPath('rute.0.uj_ditahan_ban', 20000);
    }

    #[TestWith(['transaksi.void.store'])]
    #[TestWith(['transaksi.tagihan.void.store'])]
    public function test_exit_then_void_restores_both_funds_and_full_cash(string $route): void
    {
        $this->post(route('kas-uang-jalan.keluar.store'), $this->setupExit())->assertSessionHas('success');
        $transaction = Transaksi::orderByDesc('id')->firstOrFail();
        $this->post(route($route, $transaction), ['alasan' => 'Batal'])->assertSessionHas('success');
        $this->assertDatabaseHas('uj_ditahans', ['vehicle_id' => 1, 'saldo' => 0, 'total_keluar' => 50000]);
        $this->assertDatabaseHas('uj_ditahan_bans', ['vehicle_id' => 1, 'saldo' => 0, 'total_keluar' => 20000]);
        $this->assertDatabaseHas('kas_uang_jalans', ['kode_void' => 'UJ13', 'nominal_transaksi' => 370000, 'saldo' => 1000000]);
    }

    public function test_cash_must_cover_transfer_plus_both_held_amounts(): void
    {
        $data = $this->setupExit();
        DB::table('kas_uang_jalans')->update(['saldo' => 360000]);
        $this->post(route('kas-uang-jalan.keluar.store'), $data)->assertSessionHas('error');
        $this->assertDatabaseCount('transaksis', 1);
        $this->assertDatabaseCount('uj_ditahans', 0);
        $this->assertDatabaseCount('uj_ditahan_bans', 0);
    }

    public function test_exit_combines_held_notifications_and_limits_driver_grand_total_to_own_vehicle(): void
    {
        $data = $this->setupExit();
        DB::table('drivers')->where('id', 2)->update(['no_hp' => '081234567890']);
        DB::table('vehicles')->insert(['id' => 2, 'nomor_lambung' => 'OTHER']);
        foreach (['uj_ditahans', 'uj_ditahan_bans'] as $table) {
            DB::table($table)->insert(['vehicle_id' => 2, 'bulan' => now()->month, 'tahun' => now()->year, 'saldo' => 100000, 'total_masuk' => 100000, 'total_keluar' => 0]);
        }
        $this->post(route('kas-uang-jalan.keluar.store'), $data)->assertSessionHas('success');
        $sent = collect(app(GroupWa::class)->sent);
        foreach (['uj-test', 'ditahan-test', '081234567890'] as $recipient) {
            $held = $sent->where('to', $recipient)->filter(fn ($message) => str_contains($message['text'], '*Form Uang Jalan Ditahan*'));
            $this->assertCount(1, $held);
            $text = $held->first()['text'];
            $this->assertStringContainsString('Nilai :  *Rp. 70.000,-*', $text);
            $this->assertStringContainsString("Total Saldo Sparepart: UJI\nRp. 50.000", $text);
            $this->assertStringContainsString("Total Saldo Ban: UJI\nRp. 20.000", $text);
            $this->assertStringContainsString("Grand Total UJ Ditahan : \nRp. ".($recipient === '081234567890' ? '70.000' : '270.000'), $text);
            $this->assertStringContainsString('Tanggal KIMPER belum diinput.', $text);
        }
        $this->assertCount(2, $sent->where('to', 'uj-test'));
        $this->assertCount(1, $sent->where('to', 'ditahan-test'));
        $this->assertCount(2, $sent->where('to', '081234567890'));
        $all = $sent->pluck('text')->implode(' ');
        $this->assertStringNotContainsString('*Form UJ Ditahan Ban*', $all);
        $this->assertStringNotContainsString('*Form UJ Ditahan Sparepart*', $all);
    }

    #[TestWith(['transaksi.void.store'])]
    #[TestWith(['transaksi.tagihan.void.store'])]
    public function test_void_sends_one_combined_held_notification(string $route): void
    {
        $transaction = $this->fixture();
        $sparepart = DB::table('uj_ditahans')->insertGetId(['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'total_masuk' => 50000, 'saldo' => 50000]);
        UjDitahanDetail::create(['uj_ditahan_id' => $sparepart, 'transaksi_id' => $transaction->id, 'jenis' => 'masuk', 'nominal' => 50000]);
        $ban = UjDitahanBan::factory()->create(['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'total_masuk' => 20000, 'saldo' => 20000]);
        UjDitahanBanDetail::factory()->for($ban, 'ujDitahanBan')->create(['transaksi_id' => $transaction->id, 'nominal' => 20000]);
        GroupWa::create(['untuk' => 'kas-uj-ditahan', 'nama_group' => 'held-test']);
        $this->post(route($route, $transaction), ['alasan' => 'Salah rute'])->assertSessionHas('success');
        $sent = collect(app(GroupWa::class)->sent)->where('to', 'held-test');
        $this->assertCount(1, $sent);
        $text = $sent->first()['text'];
        $this->assertStringContainsString('*Void Uang Jalan Ditahan*', $text);
        $this->assertStringContainsString('Nilai :  *Rp. 70.000,-*', $text);
        $this->assertStringContainsString('Sparepart : Rp. 50.000', $text);
        $this->assertStringContainsString('Ban : Rp. 20.000', $text);
        $this->assertStringContainsString('Salah rute', $text);
        $this->assertSame(0, $ban->fresh()->saldo);
    }

    public function test_ban_only_void_still_sends_combined_notification(): void
    {
        $transaction = $this->fixture();
        $ban = UjDitahanBan::factory()->create(['vehicle_id' => 1, 'bulan' => 9, 'tahun' => 2026, 'total_masuk' => 20000, 'saldo' => 20000]);
        UjDitahanBanDetail::factory()->for($ban, 'ujDitahanBan')->create(['transaksi_id' => $transaction->id, 'nominal' => 20000]);
        GroupWa::create(['untuk' => 'kas-uj-ditahan', 'nama_group' => 'held-test']);
        $this->post(route('transaksi.void.store', $transaction), ['alasan' => 'Batal'])->assertSessionHas('success');
        $sent = collect(app(GroupWa::class)->sent)->where('to', 'held-test');
        $this->assertCount(1, $sent);
        $this->assertStringContainsString('*Void Uang Jalan Ditahan*', $sent->first()['text']);
        $this->assertStringContainsString('Nilai :  *Rp. 20.000,-*', $sent->first()['text']);
        $this->assertStringContainsString('Sparepart : Rp. 0', $sent->first()['text']);
    }
}
