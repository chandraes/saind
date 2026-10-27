<?php

namespace Tests\Feature;

use App\Exports\GajiPreviewExport;
use App\Models\Karyawan;
use App\Models\KasBesar;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\User;
use App\Services\PayrollAttendanceService;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PayrollAttendanceTest extends TestCase
{
    private array $fakeSnapshot = [];

    private bool $fakeInstalled = false;

    private int $fakeStatus = 200;

    private bool $failLock = false;

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
            '2023_09_06_160726_create_group_was_table.php', '2026_10_20_124833_create_payroll_attendance_periods_table.php', '2026_10_10_160208_create_attendance_employee_syncs_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        (require glob(database_path('migrations/*_create_bpjs_health_minimums_table.php'))[0])->up();
        foreach (range(2020, 2030) as $year) {
            DB::table('bpjs_health_minimums')->insert(['year' => $year, 'minimum_wage' => 1, 'basis' => 'UMK', 'reference' => 'Fixture pengujian']);
        }
        DB::table('payroll_attendance_settings')->where('id', 1)->update(['enabled' => true]);
        DB::table('jenis_transaksis')->insert([['id' => 1, 'nama' => 'Masuk'], ['id' => 2, 'nama' => 'Keluar']]);
        Http::preventStrayRequests();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-31 10:00:00'));
    }

    private function employee(): Karyawan
    {
        return Karyawan::factory()->create(['gaji_pokok' => 5000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0, 'apa_bpjs_tk' => 0, 'apa_bpjs_kesehatan' => 0]);
    }

    private function login(string $role = 'admin'): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role, 'password' => 'password']));
    }

    private function snapshot(Karyawan $employee, array $changes = []): array
    {
        $days = [];
        for ($date = Carbon::parse('2026-09-26'); $date->toDateString() <= '2026-10-25'; $date = $date->addDay()) {
            $key = $date->toDateString();
            $days[] = array_replace(['date' => $key, 'status' => 'present', 'scheduled_minutes' => 540, 'break_minutes' => 60, 'late_minutes' => 0, 'early_minutes' => 0], $changes[$key] ?? []);
        }
        $snapshot = ['schema_version' => 1, 'monitoring_version' => 1, 'from' => '2026-09-26', 'to' => '2026-10-25', 'timezone' => 'Asia/Jakarta', 'source_employee_ids' => [(string) $employee->id], 'employees' => [['source_employee_id' => (string) $employee->id, 'employee_id' => 19, 'attendance_required' => true, 'days' => $days]], 'issues' => [], 'ready' => true, 'revision' => str_repeat('a', 64)];

        return $snapshot;
    }

    private function fake(array $snapshot): void
    {
        config(['attendance.enabled' => true, 'attendance.url' => 'https://attendance.test', 'attendance.token' => str_repeat('p', 64), 'attendance.ca_bundle' => null, 'attendance.send_immediately' => false]);
        $this->fakeSnapshot = $snapshot;
        if (! $this->fakeInstalled) {
            Http::fake(['https://attendance.test/api/v1/integration/payroll-snapshot' => function ($request) {
                if ($this->failLock && ! empty($request['revision'])) {
                    throw new ConnectionException('simulated timeout');
                }

                return Http::response(['data' => ! empty($request['revision']) ? $this->fakeSnapshot + ['lock_request_id' => $request['request_id']] : $this->fakeSnapshot], $this->fakeStatus);
            }]);
            $this->fakeInstalled = true;
        }
    }

    private function refresh(Karyawan $employee, array $snapshot): void
    {
        $this->fake($snapshot);
        $this->post('/billing/gaji/attendance/refresh', ['bulan' => 10, 'tahun' => 2026])->assertRedirect()->assertSessionHasNoErrors();
    }

    private function cash(): void
    {
        KasBesar::create(['tanggal' => '2026-10-31', 'uraian' => 'Saldo', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => 100000000, 'saldo' => 100000000, 'modal_investor_terakhir' => 100000000, 'transfer_ke' => '-', 'bank' => '-', 'no_rekening' => '-']);
    }

    public function test_proportional_half_day_full_day_exceptions_and_period_cap(): void
    {
        $this->login();
        $employee = $this->employee();
        $snapshot = $this->snapshot($employee, ['2026-09-26' => ['late_minutes' => 30, 'early_minutes' => 30], '2026-09-27' => ['status' => 'half_day', 'late_minutes' => 120], '2026-09-28' => ['status' => 'absent'], '2026-09-29' => ['status' => 'excused'], '2026-09-30' => ['status' => 'holiday']]);
        $this->refresh($employee, $snapshot);
        $calc = app(PayrollAttendanceService::class)->calculate($employee, 10, 2026);
        $this->assertSame(200000, $calc['daily']);
        $this->assertSame(325000, $calc['raw']);
        $this->assertSame(325000, $calc['amount']);
        $this->assertSame(100000, $calc['days'][1]['deduction']);
        $snapshot = $this->snapshot($employee);
        foreach ($snapshot['employees'][0]['days'] as &$day) {
            $day['status'] = 'absent';
        } unset($day);
        $this->refresh($employee, $snapshot);
        $calc = app(PayrollAttendanceService::class)->calculate($employee, 10, 2026);
        $this->assertSame(6000000, $calc['raw']);
        $this->assertSame(1250000, $calc['amount']);
        Http::assertSentCount(2);
    }

    public function test_save_uses_reviewed_revision_locks_remote_and_preserves_deduction_in_salary(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->cash();
        $snapshot = $this->snapshot($employee, ['2026-09-26' => ['status' => 'absent']]);
        $this->refresh($employee, $snapshot);
        $revision = app(PayrollService::class)->revision(10, 2026);
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $revision])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(4800000, RekapGaji::sole()->total);
        $detail = RekapGajiDetail::sole();
        $this->assertSame(200000, $detail->potongan_absensi);
        $this->assertSame(4800000, $detail->sisa_gaji_dibayar);
        $saved = json_decode($detail->rincian_absensi, true);
        $this->assertSame('2026-09-26', $saved['days'][0]['date']);
        $this->assertSame('absent', $saved['days'][0]['status']);
        $this->assertSame(200000, $saved['days'][0]['deduction']);
        $this->assertSame(200000, $saved['days'][0]['automatic']);
        $this->assertSame('Asia/Jakarta', $saved['timezone']);
        $this->get('/rekap-gaji-detail?bulan=10&tahun=2026')->assertOk()->assertSee('data-saved-attendance-detail', false);

        $this->assertNotNull(DB::table('payroll_attendance_periods')->sole()->sealed_at);
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $revision])->assertSessionHasErrors('bulan');
        $this->assertSame(1, RekapGaji::count());
        Http::assertSentCount(2);
    }

    public function test_missing_or_pending_snapshot_blocks_execution_without_payment(): void
    {
        config(['attendance.enabled' => true]);
        $this->login();
        $employee = $this->employee();
        $this->cash();
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026])->assertSessionHasErrors('attendance');
        $snapshot = $this->snapshot($employee);
        $snapshot['ready'] = false;
        $snapshot['issues'] = ['Cuti masih menunggu HR'];
        $this->refresh($employee, $snapshot);
        $revision = app(PayrollService::class)->revision(10, 2026);
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $revision])->assertSessionHasErrors('attendance');
        $this->assertSame(0, RekapGaji::count());
        Http::assertSentCount(1);
    }

    public function test_server_revision_conflict_blocks_payment_and_preserves_draft(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->cash();
        $this->refresh($employee, $this->snapshot($employee));
        $revision = app(PayrollService::class)->revision(10, 2026);
        $this->fakeStatus = 409;
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $revision])->assertSessionHasErrors('attendance');
        $this->assertSame(0, RekapGaji::count());
        $this->assertNull(DB::table('payroll_attendance_periods')->sole()->sealed_at);
        Http::assertSentCount(2);
    }

    public function test_financial_change_after_preview_requires_new_review(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->cash();
        $this->refresh($employee, $this->snapshot($employee));
        $revision = app(PayrollService::class)->revision(10, 2026);
        $employee->update(['gaji_pokok' => 6000000]);
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $revision])->assertSessionHasErrors('attendance');
        $this->assertSame(0, RekapGaji::count());
        Http::assertSentCount(1);
    }

    public function test_adjustments_and_switch_are_su_only_audited_and_daily_capped(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->refresh($employee, $this->snapshot($employee));
        $data = ['bulan' => 10, 'tahun' => 2026, 'employee_id' => $employee->id, 'date' => '2026-09-26', 'amount' => 100000, 'reason' => 'Penyesuaian pengujian oleh SU'];
        $this->post('/billing/gaji/attendance/adjustment', $data)->assertForbidden();
        $this->post('/billing/gaji/attendance/setting', ['enabled' => 0, 'reason' => 'Mematikan pengujian sementara'])->assertForbidden();
        $this->login('su');
        $this->post('/billing/gaji/attendance/adjustment', array_replace($data, ['amount' => 200001]))->assertUnprocessable();
        $this->post('/billing/gaji/attendance/adjustment', $data)->assertSessionHasNoErrors();
        $this->assertSame(100000, app(PayrollAttendanceService::class)->calculate($employee, 10, 2026)['amount']);
        $this->assertSame(1, DB::table('payroll_attendance_events')->where('action', 'deduction.adjusted')->count());
        $this->post('/billing/gaji/attendance/setting', ['enabled' => 0, 'reason' => 'Mematikan pengujian sementara'])->assertSessionHasNoErrors();
        $this->assertSame(0, app(PayrollAttendanceService::class)->calculate($employee, 10, 2026)['amount']);
        Http::assertSentCount(1);
    }

    public function test_snapshot_missing_day_is_rejected_and_old_draft_is_preserved(): void
    {
        $this->login();
        $employee = $this->employee();
        $snapshot = $this->snapshot($employee);
        $this->refresh($employee, $snapshot);
        array_pop($snapshot['employees'][0]['days']);
        $this->fake($snapshot);
        $this->post('/billing/gaji/attendance/refresh', ['bulan' => 10, 'tahun' => 2026])->assertSessionHasErrors('attendance');
        $this->assertCount(30, json_decode(DB::table('payroll_attendance_periods')->sole()->snapshot, true)['employees'][0]['days']);
        Http::assertSentCount(2);
    }

    public function test_preview_and_exports_use_same_net_deduction_and_no_remote_calls_on_read(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->refresh($employee, $this->snapshot($employee, ['2026-09-26' => ['status' => 'absent']]));
        $this->getJson('/billing/gaji?bulan=10&tahun=2026', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('data.0.potongan_absensi', 200000)->assertJsonPath('data.0.total', 4800000);
        $html = (new GajiPreviewExport(10, 2026, app(PayrollService::class)))->view()->render();
        $this->assertStringContainsString('4800000', $html);
        $this->assertStringContainsString('Potongan Absensi', $html);
        $this->get('/billing/gaji?bulan=10&tahun=2026')->assertOk()->assertSee('200.000');
        Http::assertSentCount(1);
    }

    public function test_uncertain_lock_timeout_is_retried_with_same_transaction_and_cannot_be_overwritten(): void
    {
        $this->login('su');
        $employee = $this->employee();
        $this->cash();
        $this->refresh($employee, $this->snapshot($employee));
        $requestId = DB::table('payroll_attendance_periods')->sole()->request_id;
        $revision = app(PayrollService::class)->revision(10, 2026);
        $this->failLock = true;
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $revision])->assertSessionHasErrors('attendance');
        $this->assertSame(0, RekapGaji::count());
        $this->post('/billing/gaji/attendance/refresh', ['bulan' => 10, 'tahun' => 2026])->assertSessionHasErrors('attendance');
        $this->post('/billing/gaji/attendance/setting', ['enabled' => 0, 'reason' => 'Tidak boleh melewati transaksi belum pasti'])->assertSessionHasErrors('attendance');
        $this->failLock = false;
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $revision])->assertSessionHasNoErrors();
        $this->assertSame($requestId, DB::table('payroll_attendance_periods')->sole()->request_id);
        $this->assertSame(1, RekapGaji::count());
        Http::assertSent(fn ($request) => ($request['request_id'] ?? null) === $requestId);
    }

    public function test_employee_pdf_download_uses_local_snapshot_and_saved_report_is_frozen(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->cash();
        $this->refresh($employee, $this->snapshot($employee, ['2026-09-26' => ['status' => 'absent']]));
        $url = '/billing/gaji/attendance/'.$employee->id.'/pdf?bulan=10&tahun=2026';
        $response = $this->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->get('/billing/gaji/attendance/999999/pdf?bulan=10&tahun=2026')->assertNotFound();
        $revision = app(PayrollService::class)->revision(10, 2026);
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $revision])->assertSessionHasNoErrors();
        $record = RekapGajiDetail::sole();
        $savedUrl = '/billing/gaji/attendance-saved/'.$record->id.'/pdf';
        $before = $this->get($savedUrl)->assertOk()->assertHeader('content-type', 'application/pdf');
        $employee->update(['gaji_pokok' => 10000000]);
        config(['attendance.enabled' => false]);
        $this->get($savedUrl)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertSame(200000, json_decode($record->fresh()->rincian_absensi, true)['amount']);
        $this->assertStringStartsWith('%PDF-', $before->getContent());
        $this->get('/billing/gaji/attendance-saved/999999/pdf')->assertNotFound();
        Http::assertSentCount(2);
        auth()->forgetGuards();
        $this->get($savedUrl)->assertRedirect('/login');
    }

    public function test_employee_pdf_without_snapshot_is_not_available(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->get('/billing/gaji/attendance/'.$employee->id.'/pdf?bulan=10&tahun=2026')->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_overnight_shift_at_period_end_keeps_its_start_date_and_next_day_checkout(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->refresh($employee, $this->snapshot($employee, ['2026-09-26' => ['schedule_mode' => 'weekly'], '2026-10-25' => [
            'schedule_mode' => 'shift', 'schedule_name' => 'Malam',
            'scheduled_start_at' => '2026-10-25T22:00:00+07:00',
            'scheduled_end_at' => '2026-10-26T06:00:00+07:00',
            'check_in_at' => '2026-10-25T22:30:00+07:00',
            'check_out_at' => '2026-10-26T05:40:00+07:00',
            'scheduled_minutes' => 480, 'break_minutes' => 60,
            'late_minutes' => 30, 'early_minutes' => 20,
        ]]));

        $detail = app(PayrollAttendanceService::class)->calculate($employee, 10, 2026);

        $this->assertSame(23810, $detail['amount']);
        $last = $detail['days'][29];
        $this->assertSame('2026-10-25', $last['date']);
        $this->assertSame('2026-10-26T05:40:00+07:00', $last['check_out_at']);
        $this->assertSame(420, $last['effective_minutes']);
        $this->assertSame('shift', $last['schedule_mode']);
        $this->assertSame('Malam', $last['schedule_name']);
        $this->assertCount(30, $detail['days']);
        Http::assertSent(fn ($request) => $request['from'] === '2026-09-26' && $request['to'] === '2026-10-25');
        $html = view('billing.gaji.attendance-pdf', ['name' => $employee->nama, 'period' => Carbon::parse('2026-10-01'), 'saved' => false, 'detail' => $detail])->render();
        $this->assertStringContainsString('25/10/2026 22:00', $html);
        $this->assertStringContainsString('26/10/2026 06:00', $html);
        $this->assertStringContainsString('26/10/2026 05:40', $html);
        $this->assertStringContainsString('Tanggal kerja mengikuti tanggal mulai jadwal', $html);
        $this->assertStringContainsString('Shift · Malam', $html);
        $this->assertStringContainsString('Jadwal mingguan', $html);
        $this->assertStringContainsString('Jenis jadwal belum tersedia', $html);
    }

    public function test_env_disabled_ignores_existing_deductions_and_saves_without_remote_lock(): void
    {
        $this->login();
        $employee = $this->employee();
        $this->cash();
        $snapshot = $this->snapshot($employee, ['2026-09-26' => ['status' => 'absent']]);
        $snapshot['ready'] = false;
        $snapshot['issues'] = ['Masih menunggu HR'];
        $this->refresh($employee, $snapshot);
        config(['attendance.enabled' => false]);
        $service = app(PayrollAttendanceService::class);
        $service->clear();
        $this->assertFalse($service->enabled());
        $this->assertSame(0, $service->calculate($employee, 10, 2026)['amount']);
        $this->get('/billing/gaji?bulan=10&tahun=2026')->assertOk()->assertViewHas('attendanceEnabled', false);
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(5000000, RekapGaji::sole()->total);
        $this->assertSame(0, RekapGajiDetail::sole()->potongan_absensi);
        $period = DB::table('payroll_attendance_periods')->sole();
        $this->assertNull($period->remote_locked_at);
        $this->assertNull($period->sealed_at);
        Http::assertSentCount(1);
    }

    public function test_env_disabled_allows_salary_without_attendance_snapshot(): void
    {
        $this->login();
        $this->employee();
        $this->cash();
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(5000000, RekapGaji::sole()->total);
        $this->assertSame(0, DB::table('payroll_attendance_periods')->count());
        Http::assertNothingSent();
    }

    public function test_running_monitoring_refresh_replaces_deductions_filters_and_never_locks(): void
    {
        $this->travelTo(Carbon::parse('2026-10-20 12:00:00'));
        $this->login();
        $employee = $this->employee();
        $other = $this->employee();
        $snapshot = $this->snapshot($employee, [
            '2026-09-26' => ['status' => 'absent'],
            '2026-10-19' => ['status' => 'pending_leave'],
            '2026-10-20' => ['status' => 'in_progress'],
            '2026-10-21' => ['status' => 'upcoming'],
        ]);
        $snapshot['source_employee_ids'][] = (string) $other->id;
        $snapshot['employees'][] = $this->snapshot($other)['employees'][0];
        $snapshot['ready'] = false;
        $snapshot['issues'] = ['Periode absensi masih berjalan.'];
        $snapshot['as_of'] = '2026-10-20T12:00:00+07:00';
        $snapshot['unfinished_shifts'] = 2;
        $snapshot['waiting_hr_days'] = 1;
        $this->refresh($employee, $snapshot);
        $calc = app(PayrollAttendanceService::class)->calculate($employee, 10, 2026);
        $this->assertSame(200000, $calc['amount']);
        $this->assertSame(1, $calc['waiting_hr_days']);
        $this->assertSame(2, $calc['unfinished_shifts']);
        $this->assertTrue($calc['provisional']);
        $this->getJson('/billing/gaji?bulan=10&tahun=2026&deductions_only=1', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', 2)->assertJsonPath('recordsFiltered', 1)->assertJsonCount(1, 'data')->assertJsonPath('data.0.potongan_absensi', 200000);
        $this->get('/billing/gaji?bulan=10&tahun=2026')->assertOk()->assertSee('Pantauan potongan sementara')->assertSee('20/10/2026 12:00');
        $snapshot['employees'][0]['days'][0]['status'] = 'excused';
        $snapshot['revision'] = str_repeat('b', 64);
        $this->refresh($employee, $snapshot);
        $this->assertSame(0, app(PayrollAttendanceService::class)->calculate($employee, 10, 2026)['amount']);
        $this->assertSame(1, DB::table('payroll_attendance_periods')->count());
        $period = DB::table('payroll_attendance_periods')->sole();
        $this->assertNull($period->lock_attempted_at);
        $this->assertNull($period->remote_locked_at);
        $this->assertNull($period->sealed_at);
        $this->cash();
        $revision = app(PayrollService::class)->revision(10, 2026);
        $this->post('/billing/gaji/store', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $revision])->assertSessionHasErrors('attendance');
        $this->assertSame(0, RekapGaji::count());
        Http::assertSentCount(2);
    }

    public function test_old_backend_format_cannot_replace_monitoring_snapshot(): void
    {
        $this->login();
        $employee = $this->employee();
        $snapshot = $this->snapshot($employee, ['2026-09-26' => ['status' => 'absent']]);
        $this->refresh($employee, $snapshot);
        unset($snapshot['monitoring_version']);
        $this->fake($snapshot);
        $this->post('/billing/gaji/attendance/refresh', ['bulan' => 10, 'tahun' => 2026])->assertSessionHasErrors('attendance');
        $this->assertSame(200000, app(PayrollAttendanceService::class)->calculate($employee, 10, 2026)['amount']);
        Http::assertSentCount(2);
    }

    public function test_cutoff_crosses_year_and_exempt_employee_has_no_deduction(): void
    {
        $service = app(PayrollAttendanceService::class);
        $this->assertSame(['from' => '2025-12-26', 'to' => '2026-01-25'], $service->dates(1, 2026));
        $this->login();
        $employee = $this->employee();
        $snapshot = $this->snapshot($employee, ['2026-09-26' => ['status' => 'absent']]);
        $snapshot['employees'][0]['attendance_required'] = false;
        $this->refresh($employee, $snapshot);
        $this->assertSame(0, $service->calculate($employee, 10, 2026)['amount']);
        Http::assertSentCount(1);
    }
}
