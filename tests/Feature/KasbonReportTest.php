<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class KasbonReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'attendance.enabled' => false]);
        DB::purge('sqlite');
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn ($date) => (int) substr($date, 0, 4), 1);
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
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-31 10:00:00'));
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
    }

    private function loan(Karyawan $employee, array $attributes = []): KasBon
    {
        return KasBon::create([...['karyawan_id' => $employee->id, 'tanggal' => '2026-10-18', 'nominal' => 100000, 'sisa_kas' => 100000], ...$attributes]);
    }

    private function installment(Karyawan $employee, array $attributes = []): KasBonCicilan
    {
        return KasBonCicilan::create([...['karyawan_id' => $employee->id, 'tanggal' => '2026-08-01', 'nominal' => 900000, 'sisa_kas' => 900000, 'cicil_kali' => 3, 'cicilan_nominal' => 300000, 'mulai_bulan' => 8, 'mulai_tahun' => 2026], ...$attributes]);
    }

    private function rows(array $parameters = []): TestResponse
    {
        return $this->getJson(route('rekap.kas-bon', ['bulan' => 10, 'tahun' => 2026, ...$parameters]), ['X-Requested-With' => 'XMLHttpRequest']);
    }

    public function test_potong_summary_uses_its_own_period_and_excludes_void(): void
    {
        $employee = Karyawan::factory()->create();
        $this->loan($employee);
        $this->loan($employee, ['nominal' => 200000, 'total_bayar' => 200000, 'sisa_kas' => 0, 'lunas' => true]);
        $this->loan($employee, ['tanggal' => '2026-09-30', 'nominal' => 700000]);
        $this->loan($employee, ['void' => true, 'nominal' => 800000]);
        $this->installment($employee, ['total_bayar' => 300000, 'sisa_kas' => 600000]);
        $this->get(route('rekap.kas-bon'))->assertOk()->assertViewHas('summary', ['count' => 2, 'people' => 1, 'nominal' => 300000, 'paid' => 200000, 'remaining' => 100000])->assertSee('void-kasbon-modal')->assertSee('Cetak Potong Gaji')->assertDontSee('Void Storing');
        $this->rows()->assertOk()->assertJsonPath('recordsTotal', 2)->assertJsonPath('summary.nominal', 300000)->assertJsonPath('summary.paid', 200000)->assertJsonPath('summary.remaining', 100000);
    }

    public function test_scroll_search_and_totals_cover_all_matching_rows(): void
    {
        $employee = Karyawan::factory()->create(['nama' => 'Pegawai Utama']);
        for ($index = 0; $index < 105; $index++) {
            $this->loan($employee);
        }
        $other = Karyawan::factory()->create(['nama' => 'Nama Dicari']);
        $this->loan($other, ['nominal' => 500000, 'sisa_kas' => 500000]);
        $this->rows(['length' => 1000])->assertOk()->assertJsonCount(100, 'data')->assertJsonPath('recordsTotal', 106)->assertJsonPath('summary.nominal', 11000000);
        $this->rows(['start' => 100, 'length' => 25])->assertOk()->assertJsonCount(6, 'data')->assertJsonPath('data.0.number', 101);
        $this->rows(['search' => ['value' => 'Nama Dicari']])->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('recordsTotal', 106)->assertJsonPath('recordsFiltered', 1)->assertJsonPath('summary.nominal', 500000)->assertJsonPath('data.0.nama', 'Nama Dicari');
        $this->rows(['start' => 106])->assertJsonCount(0, 'data');
    }

    public function test_employee_and_repayment_filters_are_applied(): void
    {
        $employee = Karyawan::factory()->create();
        $other = Karyawan::factory()->create();
        $this->loan($employee);
        $this->loan($employee, ['lunas' => true, 'sisa_kas' => 0, 'total_bayar' => 100000]);
        $this->loan($other);
        $this->rows(['karyawan_id' => $employee->id, 'status' => 'lunas'])->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.lunas', true)->assertJsonPath('data.0.void_url', null)->assertJsonPath('summary.remaining', 0);
        $this->rows(['karyawan_id' => $employee->id, 'status' => 'belum'])->assertJsonCount(1, 'data')->assertJsonPath('summary.remaining', 100000);
    }

    public function test_installments_default_to_unpaid_across_periods_and_estimate_last_month_correctly(): void
    {
        $employee = Karyawan::factory()->create();
        $loan = $this->installment($employee);
        $this->installment($employee, ['lunas' => true, 'sisa_kas' => 0, 'total_bayar' => 900000]);
        $this->rows(['jenis' => 'cicilan'])->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $loan->id)->assertJsonPath('data.0.mulai', 'Agustus 2026')->assertJsonPath('data.0.selesai', 'Oktober 2026')->assertJsonPath('data.0.void_url', route('billing.kasbon.kas-bon-cicil.void', $loan->id))->assertJsonPath('summary.nominal', 900000);
        $this->rows(['jenis' => 'cicilan', 'status' => 'lunas'])->assertJsonCount(1, 'data')->assertJsonPath('data.0.void_url', null);
        $this->get(route('rekap.kas-bon', ['jenis' => 'cicilan']))->assertOk()->assertSee('Semua periode transaksi')->assertDontSee('Cetak Potong Gaji');
    }

    public function test_modal_uses_correct_endpoint_and_paid_potong_has_no_void_action(): void
    {
        $employee = Karyawan::factory()->create();
        $loan = $this->loan($employee);
        $this->rows()->assertJsonPath('data.0.void_url', route('rekap.kas-bon.void', $loan->id));
        $loan->update(['total_bayar' => 50000, 'sisa_kas' => 50000]);
        $this->rows()->assertJsonPath('data.0.void_url', null);
        $html = $this->get(route('rekap.kas-bon'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'id="void-kasbon-modal"'));
    }

    public function test_years_include_installment_only_year_and_employee_lookup_is_bounded(): void
    {
        $employee = Karyawan::factory()->create(['nama' => 'Target Pegawai']);
        $this->installment($employee, ['tanggal' => '2025-12-01']);
        $this->get(route('rekap.kas-bon'))->assertOk()->assertViewHas('years', fn ($years) => $years->map(fn ($year) => (int) $year)->all() === [2026, 2025]);
        Karyawan::factory()->count(30)->create();
        $this->getJson(route('rekap.kas-bon', ['lookup' => 'pegawai']))->assertOk()->assertJsonCount(25, 'results')->assertJsonPath('pagination.more', true);
        $this->getJson(route('rekap.kas-bon', ['lookup' => 'pegawai', 'q' => 'Target Pegawai']))->assertJsonCount(1, 'results')->assertJsonPath('results.0.text', 'Target Pegawai')->assertJsonPath('pagination.more', false);
    }

    public function test_print_preview_renders_filtered_potong_and_empty_periods(): void
    {
        $employee = Karyawan::factory()->create();
        $this->loan($employee);
        $this->get(route('rekap.kas-bon.preview', ['bulan' => 10, 'tahun' => 2026, 'status' => 'belum', 'karyawan_id' => $employee->id]))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get(route('rekap.kas-bon.preview', ['bulan' => 1, 'tahun' => 2026]))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    #[TestWith([['bulan' => 13], 'bulan'])]
    #[TestWith([['jenis' => 'invalid'], 'jenis'])]
    #[TestWith([['status' => 'invalid'], 'status'])]
    #[TestWith([['karyawan_id' => 99999], 'karyawan_id'])]
    public function test_invalid_filters_are_rejected(array $parameters, string $field): void
    {
        $this->rows($parameters)->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public function test_guest_and_operational_role_cannot_access_report(): void
    {
        auth()->forgetGuards();
        $this->get(route('rekap.kas-bon'))->assertRedirect('/login');
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'operasional', 'password' => 'password']));
        $this->get(route('rekap.kas-bon'))->assertForbidden();
    }
}
