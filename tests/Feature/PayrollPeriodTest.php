<?php

namespace Tests\Feature;

use App\Exports\GajiPreviewExport;
use App\Models\Direksi;
use App\Models\Karyawan;
use App\Models\KasBesar;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PayrollPeriodTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'attendance.enabled' => false, 'cache.default' => 'array']);
        DB::purge('sqlite');
        foreach ([
            '2014_10_12_000000_create_users_table.php', '2023_08_12_155039_create_jabatans_table.php', '2023_08_12_155203_create_karyawans_table.php',
            '2023_10_18_020025_add_column_to_karyawans_table.php', '2023_10_29_134916_add_column_to_karyawans_table.php', '2023_10_26_192359_create_direksis_table.php',
            '2024_08_28_181510_add_column_to_karyawans_table.php',
            '2023_10_26_144444_create_kas_bons_table.php', '2023_10_26_184552_add_void_column_to_kas_bons_table.php', '2023_10_27_145407_add_column_to_kas_bons_table.php',
            '2023_10_30_163244_create_kas_bon_cicilans_table.php', '2023_10_26_153357_create_rekap_gajis_table.php',
            '2023_10_29_185538_change_column_in_rekap_gajis_table.php', '2023_10_30_200911_add_column_to_rekap_gaji_details_table.php',
            '2023_08_26_113058_create_jenis_transaksis_table.php', '2023_08_26_113809_create_tipe_transaksis_table.php', '2023_08_26_140300_create_kas_besars_table.php',
            '2023_09_03_061547_drop_tipe_transaksi_table_and_column.php', '2023_10_21_092404_change_uraian_column_in_kas_besars_table.php',
            '2023_09_06_160726_create_group_was_table.php', '2026_10_20_124833_create_payroll_attendance_periods_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        DB::table('payroll_attendance_settings')->where('id', 1)->update(['enabled' => false]);
        DB::table('jenis_transaksis')->insert([['id' => 1, 'nama' => 'Masuk'], ['id' => 2, 'nama' => 'Keluar']]);
        Http::preventStrayRequests();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-31 10:00:00'));
    }

    private function login(string $role = 'admin'): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role, 'password' => 'password']));
    }

    private function employee(array $attributes = []): Karyawan
    {
        return Karyawan::factory()->create(array_merge(['gaji_pokok' => 5000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0, 'apa_bpjs_tk' => 0, 'apa_bpjs_kesehatan' => 0], $attributes));
    }

    private function cash(int $balance = 100000000): KasBesar
    {
        return KasBesar::create(['tanggal' => '2026-10-31', 'uraian' => 'Saldo awal', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => $balance, 'saldo' => $balance, 'modal_investor_terakhir' => 100000000, 'transfer_ke' => '-', 'bank' => '-', 'no_rekening' => '-']);
    }

    private function loan(Karyawan $employee, string $date, int $amount, array $attributes = []): KasBon
    {
        return KasBon::create(array_merge(['karyawan_id' => $employee->id, 'tanggal' => $date, 'nominal' => $amount, 'sisa_kas' => $amount], $attributes));
    }

    private function installment(Karyawan $employee, array $attributes = []): KasBonCicilan
    {
        return KasBonCicilan::create(array_merge(['karyawan_id' => $employee->id, 'tanggal' => '2026-08-01', 'nominal' => 1050000, 'sisa_kas' => 50000, 'total_bayar' => 1000000, 'cicil_kali' => 3, 'cicilan_nominal' => 350000, 'mulai_bulan' => 8, 'mulai_tahun' => 2026], $attributes));
    }

    public function test_late_payroll_only_pays_eligible_debts_and_uses_actual_cash_date(): void
    {
        $this->login();
        $employee = $this->employee();
        $old = $this->loan($employee, '2026-08-31', 100000);
        $september = $this->loan($employee, '2026-09-30', 200000);
        $october = $this->loan($employee, '2026-10-01', 300000);
        $void = $this->loan($employee, '2026-09-01', 400000, ['void' => true]);
        $installment = $this->installment($employee);
        $future = $this->installment($employee, ['sisa_kas' => 100000, 'mulai_bulan' => 10]);
        $createdLater = $this->installment($employee, ['tanggal' => '2026-10-01', 'sisa_kas' => 100000]);
        $this->cash();

        $this->get('/billing/gaji?bulan=9&tahun=2026')->assertOk()->assertSee('September')->assertSee('350.000');
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026, 'total' => 1])->assertRedirect('/billing')->assertSessionHas('success');

        $this->assertDatabaseHas('rekap_gajis', ['bulan' => 9, 'tahun' => 2026, 'total' => 4650000]);
        $this->assertDatabaseHas('rekap_gaji_details', ['kasbon' => 350000, 'sisa_gaji_dibayar' => 4650000]);
        $this->assertDatabaseHas('kas_besars', ['tanggal' => '2026-10-31', 'nominal_transaksi' => 4650000, 'saldo' => 95350000]);
        foreach ([$old, $september, $installment] as $loan) {
            $this->assertEquals(1, $loan->fresh()->lunas);
            $this->assertEquals(0, $loan->fresh()->sisa_kas);
        }
        foreach ([$october, $void, $future, $createdLater] as $loan) {
            $this->assertEquals(0, $loan->fresh()->lunas);
        }
        $this->assertEquals(1050000, $installment->fresh()->total_bayar);
        Http::assertNothingSent();
    }

    public function test_duplicate_submission_does_not_pay_or_deduct_twice(): void
    {
        $this->login();
        $this->employee();
        $this->cash();
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertSessionHas('success');
        $this->post('/billing/gaji/store', ['bulan' => '09', 'tahun' => 2026])->assertSessionHasErrors('bulan');
        $this->assertSame(1, RekapGaji::count());
        $this->assertSame(1, RekapGajiDetail::count());
        $this->assertSame(2, KasBesar::count());
    }

    #[TestWith(['index'])]
    #[TestWith(['preview-pdf'])]
    #[TestWith(['preview-excel'])]
    #[TestWith(['store'])]
    public function test_all_endpoints_reject_invalid_and_future_periods(string $endpoint): void
    {
        $this->login();
        foreach ([[13, 2026], [0, 2026], [11, 2026], [1, 2027]] as [$month, $year]) {
            $response = $endpoint === 'store' ? $this->post('/billing/gaji/store', ['bulan' => $month, 'tahun' => $year]) : $this->get('/billing/gaji'.($endpoint === 'index' ? '' : '/'.$endpoint).'?bulan='.$month.'&tahun='.$year);
            $response->assertSessionHasErrors('bulan');
        }
        $this->assertSame(0, RekapGaji::count());
    }

    public function test_previous_december_is_valid_when_submitted_in_january(): void
    {
        $this->travelTo(Carbon::parse('2027-01-31 10:00:00'));
        $this->login();
        $employee = $this->employee();
        $this->cash();
        $this->loan($employee, '2026-12-31', 100000);
        $this->loan($employee, '2027-01-01', 200000);
        $this->post('/billing/gaji/store', ['bulan' => 12, 'tahun' => 2026])->assertSessionHas('success');
        $this->assertDatabaseHas('rekap_gajis', ['bulan' => 12, 'tahun' => 2026, 'total' => 4900000]);
    }

    public function test_insufficient_cash_rolls_back_payroll_and_debt_changes(): void
    {
        $this->login();
        $employee = $this->employee();
        $loan = $this->loan($employee, '2026-09-15', 100000);
        $this->cash(100);
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertSessionHasErrors('bulan');
        $this->assertSame(0, RekapGaji::count());
        $this->assertSame(0, RekapGajiDetail::count());
        $this->assertEquals(0, $loan->fresh()->lunas);
        $this->assertEquals(100000, $loan->fresh()->sisa_kas);
        $this->assertSame(1, KasBesar::count());
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_deductions_cannot_make_an_employees_pay_negative(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->loan($employee, '2026-09-15', 6000000);
        $this->cash();
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertSessionHasErrors('bulan');
        $this->assertSame(0, RekapGaji::count());
        $this->assertSame(1, KasBesar::count());
    }

    public function test_preview_and_excel_exclude_staff_hired_after_the_selected_period(): void
    {
        $this->login();
        $employee = $this->employee(['nama' => 'Pegawai September']);
        $this->employee(['nama' => 'Pegawai Oktober', 'mulai_bekerja' => '2026-10-01']);
        $this->loan($employee, '2026-09-30', 100000);
        $this->get('/billing/gaji?bulan=9&tahun=2026')->assertOk()->assertSee('4.900.000');
        $this->getJson('/billing/gaji?bulan=9&tahun=2026', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.nama', 'Pegawai September')->assertJsonPath('data.0.total', 4900000);
        $export = new GajiPreviewExport(9, 2026, app(PayrollService::class));
        $html = $export->view()->render();
        $this->assertStringContainsString('Pegawai September', $html);
        $this->assertStringNotContainsString('Pegawai Oktober', $html);
        $this->assertStringContainsString('4900000', $html);
    }

    public function test_scrolling_loads_bounded_batches_and_searches_across_all_staff(): void
    {
        $this->login();
        Karyawan::factory()->count(105)->create(['gaji_pokok' => 5000000]);
        $target = $this->employee(['nama' => 'Penerima Terakhir']);
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];
        $this->getJson('/billing/gaji?bulan=9&tahun=2026&start=0&length=500', $headers)->assertOk()->assertJsonPath('recordsTotal', 106)->assertJsonCount(100, 'data');
        $this->getJson('/billing/gaji?bulan=9&tahun=2026&start=100&length=25', $headers)->assertOk()->assertJsonCount(6, 'data')->assertJsonPath('data.0.number', 101)->assertJsonPath('data.5.nama', $target->nama);
        $this->getJson('/billing/gaji?bulan=9&tahun=2026&search[value]=Penerima%20Terakhir', $headers)->assertOk()->assertJsonPath('recordsTotal', 106)->assertJsonPath('recordsFiltered', 1)->assertJsonCount(1, 'data')->assertJsonPath('data.0.nama', $target->nama);
        $this->getJson('/billing/gaji?bulan=9&tahun=2026&start=106', $headers)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_another_payroll_in_progress_is_rejected_without_writes(): void
    {
        $this->login();
        $this->employee();
        $this->cash();
        $lock = Cache::lock('payroll-generation', 300);
        $lock->get();
        try {
            $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertSessionHas('error');
            $this->assertSame(0, RekapGaji::count());
            $this->assertSame(1, KasBesar::count());
        } finally {
            $lock->release();
        }
    }

    public function test_guest_and_operational_role_cannot_submit_payroll(): void
    {
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertRedirect('/login');
        $this->login('operasional');
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertForbidden();
        $this->assertSame(0, RekapGaji::count());
    }

    public function test_components_round_to_whole_rupiah_identically_in_preview_and_storage(): void
    {
        $this->login();
        $employee = $this->employee(['gaji_pokok' => 5000050, 'apa_bpjs_tk' => 1, 'apa_bpjs_kesehatan' => 1]);
        $this->cash();
        $calc = app(PayrollService::class)->calculateComponent($employee);
        $this->assertSame(100001, $calc['pot_tk']);
        $this->assertSame(50001, $calc['pot_k']);
        $this->assertSame(4850048, $calc['pendapatan_bersih']);
        $this->get('/billing/gaji?bulan=9&tahun=2026')->assertOk()->assertSee('4.850.048');
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertSessionHas('success');
        $this->assertDatabaseHas('rekap_gaji_details', ['pendapatan_bersih' => 4850048, 'potongan_bpjs_tk' => 100001, 'potongan_bpjs_kesehatan' => 50001]);
        $this->assertDatabaseHas('rekap_gajis', ['total' => 4850048]);
    }

    public function test_pdf_and_excel_downloads_use_the_selected_period_and_director_pay(): void
    {
        $this->login('su');
        $attributes = Karyawan::factory()->make(['nama' => 'Direktur Uji', 'gaji_pokok' => 7000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0])->getAttributes();
        unset($attributes['kode'], $attributes['nomor'], $attributes['jabatan_id']);
        Direksi::create([...$attributes, 'jabatan' => 'Direktur']);
        $this->get('/billing/gaji?bulan=9&tahun=2026')->assertOk()->assertSee('7.000.000');
        $this->getJson('/billing/gaji?bulan=9&tahun=2026', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('data.0.nama', 'Direktur Uji')->assertJsonPath('data.0.jabatan', 'Direktur');
        $pdf = $this->get('/billing/gaji/preview-pdf?bulan=9&tahun=2026')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->get('/billing/gaji/preview-excel?bulan=9&tahun=2026')->assertOk()->assertDownload('Preview_Gaji_09_2026.xlsx');
        $this->cash();
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertSessionHas('success');
        $this->assertDatabaseHas('rekap_gaji_details', ['nik' => 'Direksi', 'nama' => 'Direktur Uji', 'kasbon' => 0, 'sisa_gaji_dibayar' => 7000000]);
    }

    public function test_missing_cash_balance_does_not_create_payroll(): void
    {
        $this->login();
        $employee = $this->employee();
        $loan = $this->loan($employee, '2026-09-30', 100000);
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertSessionHasErrors('bulan');
        $this->assertSame(0, RekapGaji::count());
        $this->assertSame(0, KasBesar::count());
        $this->assertEquals(100000, $loan->fresh()->sisa_kas);
    }

    public function test_period_without_eligible_people_does_not_create_an_empty_payroll(): void
    {
        $this->login();
        $this->employee(['mulai_bekerja' => '2026-10-01']);
        $this->cash();
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertSessionHasErrors('bulan');
        $this->assertSame(0, RekapGaji::count());
        $this->assertSame(1, KasBesar::count());
    }

    #[TestWith(['index'])]
    #[TestWith(['store'])]
    #[TestWith(['preview-pdf'])]
    #[TestWith(['preview-excel'])]
    public function test_older_period_is_rejected_when_a_later_payroll_exists(string $endpoint): void
    {
        $this->login();
        RekapGaji::create(['bulan' => 10, 'tahun' => 2026, 'uraian' => 'Gaji Oktober', 'total' => 5000000]);
        $response = $endpoint === 'store' ? $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026]) : $this->get('/billing/gaji'.($endpoint === 'index' ? '' : '/'.$endpoint).'?bulan=9&tahun=2026');
        $response->assertSessionHasErrors(['bulan' => 'Gaji periode Oktober 2026 sudah dibuat. Periode sebelumnya tidak dapat dibuat karena saldo kasbon telah berubah. Proses gaji sesuai urutan periode.']);
        $this->assertSame(1, RekapGaji::count());
        $this->assertSame(0, KasBesar::count());
    }

    public function test_payroll_order_compares_year_as_well_as_month(): void
    {
        $this->login();
        RekapGaji::create(['bulan' => 1, 'tahun' => 2026, 'uraian' => 'Gaji Januari', 'total' => 5000000]);
        $this->post('/billing/gaji/store', ['bulan' => 12, 'tahun' => 2025])->assertSessionHasErrors('bulan');
        $this->employee();
        $this->cash();
        $this->post('/billing/gaji/store', ['bulan' => 9, 'tahun' => 2026])->assertSessionHas('success');
        $this->assertSame(2, RekapGaji::count());
        $this->assertDatabaseHas('rekap_gajis', ['bulan' => 9, 'tahun' => 2026, 'total' => 5000000]);
    }
}
