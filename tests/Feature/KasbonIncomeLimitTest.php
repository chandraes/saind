<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\Karyawan;
use App\Models\KasBesar;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class KasbonIncomeLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'attendance.enabled' => false]);
        DB::purge('sqlite');
        foreach ([
            '2014_10_12_000000_create_users_table.php', '2023_08_12_155039_create_jabatans_table.php', '2023_08_12_155203_create_karyawans_table.php',
            '2023_10_18_020025_add_column_to_karyawans_table.php', '2023_10_29_134916_add_column_to_karyawans_table.php',

            '2023_10_26_144444_create_kas_bons_table.php', '2023_10_26_184552_add_void_column_to_kas_bons_table.php', '2023_10_27_145407_add_column_to_kas_bons_table.php',
            '2023_10_30_163244_create_kas_bon_cicilans_table.php',

            '2023_08_26_113058_create_jenis_transaksis_table.php', '2023_08_26_113809_create_tipe_transaksis_table.php', '2023_08_26_140300_create_kas_besars_table.php',
            '2023_09_03_061547_drop_tipe_transaksi_table_and_column.php', '2023_10_21_092404_change_uraian_column_in_kas_besars_table.php',
            '2023_09_06_160726_create_group_was_table.php', '2024_08_30_215313_create_pesan_was_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        DB::table('jenis_transaksis')->insert([['id' => 1, 'nama' => 'Masuk'], ['id' => 2, 'nama' => 'Keluar']]);
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
    }

    #[TestWith([false, 3250000, 0, true])]
    #[TestWith([false, 3250001, 0, false])]
    #[TestWith([false, 2250000, 1000000, true])]
    #[TestWith([false, 2250001, 1000000, false])]
    #[TestWith([true, 5850000, 0, true])]
    #[TestWith([true, 5850003, 0, false])]
    public function test_limits_use_basic_salary_and_both_allowances(bool $installment, int $amount, int $existing, bool $accepted): void
    {
        $employee = Karyawan::factory()->create(['gaji_pokok' => 5000000, 'tunjangan_jabatan' => 1000000, 'tunjangan_keluarga' => 500000]);
        if ($existing > 0) {
            KasBon::create(['karyawan_id' => $employee->id, 'tanggal' => now()->toDateString(), 'nominal' => $existing, 'sisa_kas' => $existing]);
        }
        KasBesar::create(['tanggal' => now()->toDateString(), 'uraian' => 'Saldo awal', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => 10000000, 'saldo' => 10000000, 'modal_investor_terakhir' => 10000000, 'transfer_ke' => '-', 'bank' => '-', 'no_rekening' => '-']);
        GroupWa::create(['untuk' => 'kas-besar', 'nama_group' => 'uji']);
        if ($accepted) {
            Mockery::mock('overload:App\\Services\\StarSender')->shouldReceive('sendGroup')->once()->andReturn(true);
        }
        $route = $installment ? 'billing.kasbon.kas-bon-cicil-store' : 'billing.kasbon.store';
        $payload = ['karyawan_id' => $employee->id, 'nominal' => number_format($amount, 0, ',', '.'), 'cicil_kali' => 3, 'mulai_bulan' => 10, 'mulai_tahun' => 2026];
        $response = $this->post(route($route), $payload);
        $table = $installment ? 'kas_bon_cicilans' : 'kas_bons';
        if ($accepted) {
            $response->assertRedirect(route('billing.index'))->assertSessionHas('success');
            $this->assertDatabaseHas($table, ['karyawan_id' => $employee->id, 'nominal' => $amount, 'sisa_kas' => $amount]);
            if ($installment) {
                $this->assertDatabaseHas($table, ['cicilan_nominal' => 1950000]);
            }
            $this->assertDatabaseHas('kas_besars', ['jenis_transaksi_id' => 2, 'nominal_transaksi' => $amount, 'saldo' => 10000000 - $amount]);
        } else {
            $response->assertSessionHas('error', $installment ? 'Cicilan melebihi 30% dari penghasilan setelah dikurangi kasbon potong gaji yang belum lunas.' : 'Kasbon sudah melebihi ketentuan!!');
            $this->assertDatabaseMissing($table, ['nominal' => $amount]);
            $this->assertDatabaseCount('kas_besars', 1);
        }
    }

    #[TestWith(['billing.kasbon.index'])]
    #[TestWith(['billing.kasbon.kas-bon-cicil'])]
    #[TestWith(['billing.kasbon.kas-bon-staff'])]
    public function test_kasbon_pages_render_with_form_values_and_income_guidance(string $route): void
    {
        $this->travelTo(Carbon::parse('2026-10-18 10:00:00'));
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
        $employee = Karyawan::factory()->create(['nama' => 'Pegawai Uji', 'gaji_pokok' => 5000000, 'tunjangan_jabatan' => 1000000, 'tunjangan_keluarga' => 500000]);
        $response = $this->withSession(['_old_input' => ['karyawan_id' => $employee->id, 'nominal' => '1.500.000', 'cicil_kali' => 3, 'mulai_bulan' => 11, 'mulai_tahun' => 2026]])->get(route($route))->assertOk();
        $response->assertSee('tunjangan jabatan', false)->assertSee('tunjangan keluarga', false);
        if ($route !== 'billing.kasbon.kas-bon-staff') {
            $response->assertSee('Pegawai Uji')->assertSee('value="1.500.000"', false)->assertSee('data-income="6500000"', false);
        }
    }

    #[TestWith([false, 'kas_besars'])]
    #[TestWith([true, 'kas_besars'])]
    #[TestWith([false, 'kas_bons'])]
    #[TestWith([true, 'kas_bon_cicilans'])]
    public function test_failed_loan_or_cash_insert_leaves_no_partial_transaction(bool $installment, string $failedTable): void
    {
        $employee = Karyawan::factory()->create(['gaji_pokok' => 5000000, 'tunjangan_jabatan' => 1000000, 'tunjangan_keluarga' => 500000]);
        $cash = KasBesar::create(['tanggal' => now()->toDateString(), 'uraian' => 'Saldo awal', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => 10000000, 'saldo' => 10000000, 'modal_investor_terakhir' => 10000000, 'transfer_ke' => '-', 'bank' => '-', 'no_rekening' => '-']);
        DB::statement("CREATE TRIGGER reject_kasbon_insert BEFORE INSERT ON {$failedTable} BEGIN SELECT RAISE(ABORT, 'Simulasi gagal simpan'); END");
        $payload = ['karyawan_id' => $employee->id, 'nominal' => '1.000.000', 'cicil_kali' => 2, 'mulai_bulan' => 10, 'mulai_tahun' => 2026];
        $route = $installment ? 'billing.kasbon.kas-bon-cicil-store' : 'billing.kasbon.store';

        $this->from('/billing/kasbon')->post(route($route), $payload)->assertRedirect('/billing/kasbon')->assertSessionHasInput('nominal', '1.000.000')->assertSessionHas('error', $installment ? 'Kasbon cicilan gagal disimpan. Tidak ada perubahan data; silakan coba kembali.' : 'Kasbon gagal disimpan. Tidak ada perubahan data; silakan coba kembali.');
        $this->assertDatabaseCount('kas_bons', 0);
        $this->assertDatabaseCount('kas_bon_cicilans', 0);
        $this->assertDatabaseCount('kas_besars', 1);
        $this->assertDatabaseHas('kas_besars', ['id' => $cash->id, 'saldo' => 10000000]);
        $this->assertDatabaseCount('pesan_was', 0);
        $this->assertSame(0, DB::transactionLevel());
    }

    #[TestWith([0])]
    #[TestWith([-1])]
    #[TestWith([-12])]
    public function test_zero_and_negative_installment_counts_are_rejected_without_writes(int $count): void
    {
        $employee = Karyawan::factory()->create();
        $this->from(route('billing.kasbon.kas-bon-cicil'))->post(route('billing.kasbon.kas-bon-cicil-store'), [
            'karyawan_id' => $employee->id, 'nominal' => '1.000.000', 'cicil_kali' => $count, 'mulai_bulan' => 10, 'mulai_tahun' => 2026,
        ])->assertRedirect(route('billing.kasbon.kas-bon-cicil'))->assertSessionHasErrors(['cicil_kali' => 'Jumlah cicilan minimal 1 kali.'])->assertSessionHasInput('cicil_kali', $count);
        $this->assertDatabaseCount('kas_bon_cicilans', 0);
        $this->assertDatabaseCount('kas_besars', 0);
        $this->assertDatabaseCount('pesan_was', 0);
    }

    #[TestWith(['aktif', true, 'Pegawai masih memiliki kasbon cicilan yang belum lunas. Lunasi terlebih dahulu sebelum membuat pinjaman baru.'])]
    #[TestWith(['nonaktif', false, 'Kasbon cicilan hanya dapat diajukan untuk pegawai aktif.'])]
    public function test_post_rechecks_employee_eligibility_inside_transaction(string $status, bool $pending, string $message): void
    {
        $employee = Karyawan::factory()->create(['status' => $status, 'gaji_pokok' => 5000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0]);
        if ($pending) {
            KasBonCicilan::create(['karyawan_id' => $employee->id, 'tanggal' => '2026-09-01', 'nominal' => 1000000, 'sisa_kas' => 500000, 'cicil_kali' => 2, 'cicilan_nominal' => 500000, 'mulai_bulan' => 9, 'mulai_tahun' => 2026]);
        }
        $this->post(route('billing.kasbon.kas-bon-cicil-store'), ['karyawan_id' => $employee->id, 'nominal' => '1.000.000', 'cicil_kali' => 2, 'mulai_bulan' => 10, 'mulai_tahun' => 2026])->assertSessionHasErrors(['karyawan_id' => $message])->assertSessionHas('error', $message)->assertSessionHasInput('nominal', '1.000.000');
        $this->assertDatabaseCount('kas_bon_cicilans', $pending ? 1 : 0);
        $this->assertDatabaseCount('kas_besars', 0);
        $this->assertDatabaseCount('pesan_was', 0);
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_repeated_post_creates_one_active_installment_and_repaid_loans_do_not_block(): void
    {
        $employee = Karyawan::factory()->create(['gaji_pokok' => 5000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0]);
        KasBonCicilan::create(['karyawan_id' => $employee->id, 'tanggal' => '2026-09-01', 'nominal' => 1000000, 'total_bayar' => 1000000, 'sisa_kas' => 0, 'lunas' => true, 'cicil_kali' => 2, 'cicilan_nominal' => 500000, 'mulai_bulan' => 9, 'mulai_tahun' => 2026]);
        KasBesar::create(['tanggal' => now()->toDateString(), 'uraian' => 'Saldo awal', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => 10000000, 'saldo' => 10000000, 'modal_investor_terakhir' => 10000000, 'transfer_ke' => '-', 'bank' => '-', 'no_rekening' => '-']);
        GroupWa::create(['untuk' => 'kas-besar', 'nama_group' => 'uji']);
        Mockery::mock('overload:App\\Services\\StarSender')->shouldReceive('sendGroup')->once()->andReturn(true);
        $payload = ['karyawan_id' => $employee->id, 'nominal' => '1.000.000', 'cicil_kali' => 2, 'mulai_bulan' => 10, 'mulai_tahun' => 2026];
        $this->post(route('billing.kasbon.kas-bon-cicil-store'), $payload)->assertSessionHas('success');
        $this->post(route('billing.kasbon.kas-bon-cicil-store'), $payload)->assertSessionHasErrors('karyawan_id')->assertSessionHas('error', 'Pegawai masih memiliki kasbon cicilan yang belum lunas. Lunasi terlebih dahulu sebelum membuat pinjaman baru.');
        $this->assertSame(1, KasBonCicilan::where('karyawan_id', $employee->id)->where('lunas', false)->count());
        $this->assertDatabaseCount('kas_bon_cicilans', 2);
        $this->assertDatabaseCount('kas_besars', 2);
        $this->assertDatabaseHas('kas_besars', ['jenis_transaksi_id' => 2, 'saldo' => 9000000]);
        $this->assertDatabaseCount('pesan_was', 1);
    }

    #[TestWith(['0'])]
    #[TestWith(['-100000'])]
    #[TestWith(['-100.000'])]
    #[TestWith(['0.000'])]
    #[TestWith(['1,50'])]
    #[TestWith(['1.5'])]
    #[TestWith(['1e3'])]
    #[TestWith(['bukan angka'])]
    #[TestWith([['1000000']])]
    public function test_invalid_nominal_is_rejected_by_both_backends_without_writes(mixed $nominal): void
    {
        $employee = Karyawan::factory()->create();
        foreach (['billing.kasbon.store', 'billing.kasbon.kas-bon-cicil-store'] as $route) {
            $this->post(route($route), ['karyawan_id' => $employee->id, 'nominal' => $nominal, 'cicil_kali' => 2, 'mulai_bulan' => 10, 'mulai_tahun' => 2026])->assertSessionHasErrors(['nominal' => 'Nominal harus berupa rupiah bulat lebih dari nol. Contoh: 1000000 atau 1.000.000.']);
        }
        $this->assertDatabaseCount('kas_bons', 0);
        $this->assertDatabaseCount('kas_bon_cicilans', 0);
        $this->assertDatabaseCount('kas_besars', 0);
        $this->assertDatabaseCount('pesan_was', 0);
    }

    private function crossLoanEmployee(): Karyawan
    {
        $this->travelTo(Carbon::parse('2026-10-19 10:00:00'));
        $employee = Karyawan::factory()->create(['gaji_pokok' => 5000000, 'tunjangan_jabatan' => 1000000, 'tunjangan_keluarga' => 500000]);
        KasBesar::create(['tanggal' => '2026-10-19', 'uraian' => 'Saldo awal', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => 20000000, 'saldo' => 20000000, 'modal_investor_terakhir' => 20000000, 'transfer_ke' => '-', 'bank' => '-', 'no_rekening' => '-']);

        return $employee;
    }

    #[TestWith([2600000, true])]
    #[TestWith([2600001, false])]
    public function test_ordinary_limit_deducts_final_installment_and_remaining_ordinary_loans(int $amount, bool $accepted): void
    {
        $employee = $this->crossLoanEmployee();
        KasBon::create(['karyawan_id' => $employee->id, 'tanggal' => '2026-09-01', 'nominal' => 1000000, 'total_bayar' => 400000, 'sisa_kas' => 600000]);
        KasBonCicilan::create(['karyawan_id' => $employee->id, 'tanggal' => '2026-08-01', 'nominal' => 1000000, 'total_bayar' => 900000, 'sisa_kas' => 100000, 'cicilan_nominal' => 500000, 'cicil_kali' => 2, 'mulai_bulan' => 8, 'mulai_tahun' => 2026]);
        $this->getJson(route('billing.kasbon.limit', ['karyawan_id' => $employee->id, 'bulan' => 10, 'tahun' => 2026]))->assertOk()->assertJsonPath('income', 6500000)->assertJsonPath('installment_due', 100000)->assertJsonPath('ordinary_due', 600000)->assertJsonPath('ordinary_limit', 3200000)->assertJsonPath('ordinary_available', 2600000)->assertJsonCount(2, 'loans');
        if ($accepted) {
            GroupWa::create(['untuk' => 'kas-besar', 'nama_group' => 'uji']);
            Mockery::mock('overload:App\\Services\\StarSender')->shouldReceive('sendGroup')->once()->andReturn(true);
        }
        $response = $this->post(route('billing.kasbon.store'), ['karyawan_id' => $employee->id, 'nominal' => $amount]);
        if ($accepted) {
            $response->assertSessionHas('success');
            $this->assertDatabaseHas('kas_bons', ['karyawan_id' => $employee->id, 'nominal' => 2600000]);
            $this->assertDatabaseHas('kas_besars', ['jenis_transaksi_id' => 2, 'saldo' => 17400000]);
        } else {
            $response->assertSessionHasErrors('nominal');
            $this->assertDatabaseCount('kas_bons', 1);
            $this->assertDatabaseCount('kas_besars', 1);
        }
    }

    #[TestWith([3300000, true])]
    #[TestWith([3300002, false])]
    public function test_installment_limit_deducts_older_unpaid_ordinary_loan_and_ignores_paid_void_future_loans(int $amount, bool $accepted): void
    {
        $employee = $this->crossLoanEmployee();
        foreach ([
            ['tanggal' => '2026-09-01', 'total_bayar' => 500000, 'sisa_kas' => 1000000],
            ['tanggal' => '2026-09-01', 'total_bayar' => 1500000, 'sisa_kas' => 0, 'lunas' => true],
            ['tanggal' => '2026-09-01', 'void' => true],
            ['tanggal' => '2026-11-01'],
        ] as $attributes) {
            KasBon::create(['karyawan_id' => $employee->id, 'nominal' => 1500000, 'sisa_kas' => 1500000, ...$attributes]);
        }
        $this->getJson(route('billing.kasbon.limit', ['karyawan_id' => $employee->id, 'bulan' => 10, 'tahun' => 2026]))->assertOk()->assertJsonPath('ordinary_due', 1000000)->assertJsonPath('installment_limit', 1650000)->assertJsonCount(2, 'loans')->assertJsonPath('loans.1.berlaku', false);
        if ($accepted) {
            GroupWa::create(['untuk' => 'kas-besar', 'nama_group' => 'uji']);
            Mockery::mock('overload:App\\Services\\StarSender')->shouldReceive('sendGroup')->once()->andReturn(true);
        }
        $response = $this->post(route('billing.kasbon.kas-bon-cicil-store'), ['karyawan_id' => $employee->id, 'nominal' => $amount, 'cicil_kali' => 2, 'mulai_bulan' => 10, 'mulai_tahun' => 2026]);
        if ($accepted) {
            $response->assertSessionHas('success');
            $this->assertDatabaseHas('kas_bon_cicilans', ['karyawan_id' => $employee->id, 'cicilan_nominal' => 1650000]);
            $this->assertDatabaseHas('kas_besars', ['jenis_transaksi_id' => 2, 'saldo' => 16700000]);
        } else {
            $response->assertSessionHasErrors('nominal');
            $this->assertDatabaseCount('kas_bon_cicilans', 0);
            $this->assertDatabaseCount('kas_besars', 1);
        }
    }

    public function test_summary_changes_with_installment_start_month_and_never_returns_negative_limits(): void
    {
        $employee = $this->crossLoanEmployee();
        KasBonCicilan::create(['karyawan_id' => $employee->id, 'tanggal' => '2026-10-01', 'nominal' => 7000000, 'sisa_kas' => 7000000, 'cicilan_nominal' => 7000000, 'cicil_kali' => 1, 'mulai_bulan' => 11, 'mulai_tahun' => 2026]);
        $this->getJson(route('billing.kasbon.limit', ['karyawan_id' => $employee->id, 'bulan' => 10, 'tahun' => 2026]))->assertJsonPath('installment_due', 0)->assertJsonPath('ordinary_available', 3250000)->assertJsonPath('loans.0.berlaku', false);
        $this->getJson(route('billing.kasbon.limit', ['karyawan_id' => $employee->id, 'bulan' => 11, 'tahun' => 2026]))->assertJsonPath('installment_due', 7000000)->assertJsonPath('ordinary_available', 0)->assertJsonPath('loans.0.berlaku', true);
        KasBon::create(['karyawan_id' => $employee->id, 'tanggal' => '2026-10-01', 'nominal' => 7000000, 'sisa_kas' => 7000000]);
        $this->getJson(route('billing.kasbon.limit', ['karyawan_id' => $employee->id, 'bulan' => 11, 'tahun' => 2026]))->assertJsonPath('installment_limit', 0);
    }

    public function test_limit_endpoint_requires_valid_employee_period_and_authorization(): void
    {
        $employee = Karyawan::factory()->create();
        $this->getJson(route('billing.kasbon.limit', ['karyawan_id' => $employee->id, 'bulan' => 13, 'tahun' => 2026]))->assertUnprocessable()->assertJsonValidationErrors('bulan');
        $this->getJson(route('billing.kasbon.limit', ['karyawan_id' => 999999, 'bulan' => 10, 'tahun' => 2026]))->assertUnprocessable()->assertJsonValidationErrors('karyawan_id');
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'operasional', 'password' => 'password']));
        $this->getJson(route('billing.kasbon.limit', ['karyawan_id' => $employee->id, 'bulan' => 10, 'tahun' => 2026]))->assertForbidden();
    }
}
