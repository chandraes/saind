<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * Role HRD: satu menu Potongan Absensi (web & mobile), baca saja. Kolom hanya
 * nama, jabatan, potongan absensi; rincian per hari untuk yang berpotongan.
 */
class HrdAttendanceDeductionTest extends MobileApiTestCase
{
    private User $hrd;

    private Karyawan $late;

    private Karyawan $onTime;

    protected function setUp(): void
    {
        parent::setUp();
        config(['attendance.enabled' => false, 'cache.default' => 'array']);
        foreach ([
            '2023_08_12_155039_create_jabatans_table.php', '2023_08_12_155203_create_karyawans_table.php',
            '2023_10_18_020025_add_column_to_karyawans_table.php', '2023_10_29_134916_add_column_to_karyawans_table.php', '2023_10_26_192359_create_direksis_table.php',
            '2024_08_28_181510_add_column_to_karyawans_table.php',
            '2023_10_26_144444_create_kas_bons_table.php', '2023_10_26_184552_add_void_column_to_kas_bons_table.php', '2023_10_27_145407_add_column_to_kas_bons_table.php',
            '2023_10_30_163244_create_kas_bon_cicilans_table.php', '2023_10_26_153357_create_rekap_gajis_table.php',
            '2023_10_29_185538_change_column_in_rekap_gajis_table.php', '2023_10_30_200911_add_column_to_rekap_gaji_details_table.php',
            '2026_10_20_124833_create_payroll_attendance_periods_table.php', '2026_10_10_160208_create_attendance_employee_syncs_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        (require glob(database_path('migrations/*_create_bpjs_health_minimums_table.php'))[0])->up();
        (require glob(database_path('migrations/*_add_sync_monitoring_to_payroll_attendance_periods_table.php'))[0])->up();
        Http::preventStrayRequests();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-31 10:00:00'));

        $jabatan = Jabatan::create(['nama' => 'Staff Lapangan']);
        $this->late = Karyawan::factory()->create(['nama' => 'Budi Terlambat', 'jabatan_id' => $jabatan->id, 'gaji_pokok' => 5000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0]);
        $this->onTime = Karyawan::factory()->create(['nama' => 'Sari Tepat', 'jabatan_id' => $jabatan->id, 'gaji_pokok' => 4000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0]);
        DB::table('direksis')->insert(['nama' => 'Pak Direktur', 'nickname' => 'PD', 'jabatan' => 'Direktur Utama', 'gaji_pokok' => 9000000, 'nik' => '1', 'npwp' => '1',
            'bpjs_tk' => '1', 'bpjs_kesehatan' => '1', 'tempat_lahir' => 'Palembang', 'tanggal_lahir' => '1980-01-01', 'alamat' => '-', 'no_hp' => '1', 'no_wa' => '1',
            'bank' => 'BCA', 'no_rekening' => '1', 'nama_rekening' => 'PD', 'mulai_bekerja' => '2020-01-01', 'status' => 'aktif']);
        config(['attendance.enabled' => true]);
        $this->snapshot(['2026-09-28' => ['status' => 'absent']]);
        $this->hrd = User::factory()->create(['username' => 'hrd', 'role' => 'hrd', 'password' => 'secret-pass']);
    }

    /** Snapshot absensi Oktober 2026: Budi tidak masuk sehari, Sari hadir penuh. */
    private function snapshot(array $lateChanges): void
    {
        $people = [];
        foreach ([[$this->late, $lateChanges], [$this->onTime, []]] as [$employee, $changes]) {
            $days = [];
            for ($date = Carbon::parse('2026-09-26'); $date->toDateString() <= '2026-10-25'; $date = $date->addDay()) {
                $key = $date->toDateString();
                $days[] = array_replace(['date' => $key, 'status' => 'present', 'scheduled_minutes' => 540, 'break_minutes' => 60, 'late_minutes' => 0, 'early_minutes' => 0], $changes[$key] ?? []);
            }
            $people[] = ['source_employee_id' => (string) $employee->id, 'employee_id' => $employee->id, 'payroll_work_pattern' => ['mode' => 'weekly', 'workdays' => 6, 'divisor' => 25], 'attendance_required' => true, 'days' => $days];
        }
        DB::table('payroll_attendance_periods')->insert(['year' => 2026, 'month' => 10, 'from_date' => '2026-09-26', 'to_date' => '2026-10-25',
            'request_id' => '6f1f8e8e-1111-4c1c-9a9a-000000000001', 'fetched_at' => '2026-10-31 02:00:00', 'created_at' => now(), 'updated_at' => now(),
            'snapshot' => json_encode(['payroll_work_pattern_version' => 1, 'schema_version' => 1, 'revision' => str_repeat('a', 64), 'monitoring_version' => 1, 'from' => '2026-09-26', 'to' => '2026-10-25', 'timezone' => 'Asia/Jakarta', 'as_of' => '2026-10-31T09:00:00+07:00',
                'source_employee_ids' => [(string) $this->late->id, (string) $this->onTime->id], 'employees' => $people, 'issues' => [], 'ready' => true])]);
    }

    /** @return array<string, string> */
    private function headers(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_web_lists_only_name_position_and_deduction_for_the_running_period(): void
    {
        $this->actingAs($this->hrd)->get('/home')->assertOk()->assertSee('Potongan Absensi')->assertSee(route('hrd.attendance-deductions'), false);
        $page = $this->actingAs($this->hrd)->get('/hrd/potongan-absensi')->assertOk()
            ->assertSee('Oktober 2026')->assertSee('Sementara')
            ->assertSeeInOrder(['Pak Direktur', 'Direktur Utama', 'Budi Terlambat', 'Staff Lapangan', '200.000', 'Sari Tepat'])
            ->assertSee('Total seluruh hasil filter · 1 pegawai berpotongan')
            ->assertSee(route('hrd.attendance-deductions.show', ['key' => $this->late->id, 'bulan' => 10, 'tahun' => 2026]))
            ->assertDontSee('pagination')
            ->assertSee('<option value="10" selected>Oktober (berjalan)</option>', false)
            ->assertSee('<option value="2026" selected>2026</option>', false);
        // Komponen gaji lain tidak pernah tampil.
        $page->assertDontSee('5.000.000')->assertDontSee('Gaji Pokok')->assertDontSee('Kasbon');
        $page->assertDontSee(route('hrd.attendance-deductions.show', ['key' => $this->onTime->id, 'bulan' => 10, 'tahun' => 2026]));

        $this->get('/hrd/potongan-absensi?search=sari')->assertOk()->assertSee('Sari Tepat')->assertDontSee('Budi Terlambat');
        $this->get('/hrd/potongan-absensi?potongan=1')->assertOk()->assertSee('Budi Terlambat')->assertDontSee('Sari Tepat')->assertDontSee('Pak Direktur');
        // Infinite scroll: halaman berikutnya hanya berisi baris, nomor urut berlanjut.
        Karyawan::factory()->count(25)->create(['jabatan_id' => $this->late->jabatan_id]);
        $this->get('/hrd/potongan-absensi')->assertOk()->assertSee('data-next="'.url('/hrd/potongan-absensi?page=2').'"', false);
        $next = $this->getJson('/hrd/potongan-absensi?page=2', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('next', null);
        $this->assertStringContainsString('<td class="text-center">26</td>', $next->json('html'));
        $this->assertStringNotContainsString('<html', $next->json('html'));
        $this->get('/hrd/potongan-absensi?bulan=1&tahun=2020')->assertSessionHasErrors('bulan');
        Http::assertNothingSent();
    }

    public function test_web_detail_and_pdf_only_for_rows_with_a_deduction(): void
    {
        $this->actingAs($this->hrd);
        $detail = $this->getJson('/hrd/potongan-absensi/'.$this->late->id.'?bulan=10&tahun=2026')->assertOk()
            ->assertJsonPath('data.name', 'Budi Terlambat')->assertJsonPath('data.detail.amount', 200000)
            ->assertJsonPath('data.detail.daily', 200000)->assertJsonPath('data.period.final', false);
        $this->assertSame('absent', collect($detail->json('data.detail.days'))->firstWhere('date', '2026-09-28')['status']);
        $this->get($detail->json('data.pdf_url'))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->getJson('/hrd/potongan-absensi/'.$this->onTime->id.'?bulan=10&tahun=2026')->assertNotFound();
        $this->getJson('/hrd/potongan-absensi/999999?bulan=10&tahun=2026')->assertNotFound();
    }

    public function test_saved_period_reads_the_frozen_payroll_record(): void
    {
        $rekap = RekapGaji::create(['uraian' => 'Gaji Bulan September Tahun 2026', 'bulan' => 9, 'tahun' => 2026, 'total' => 0]);
        $saved = RekapGajiDetail::create(['id' => 900, 'rekap_gaji_id' => $rekap->id, 'nik' => 'K001', 'nama' => 'Budi Lama', 'jabatan' => 'Mandor',
            'gaji_pokok' => 5000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0, 'bpjs_tk' => 0, 'bpjs_k' => 0, 'potongan_bpjs_tk' => 0, 'potongan_bpjs_kesehatan' => 0,
            'pendapatan_kotor' => 5000000, 'pendapatan_bersih' => 5000000, 'kasbon' => 0, 'sisa_gaji_dibayar' => 4900000, 'potongan_absensi' => 100000, 'potongan_absensi_sebelum_batas' => 100000,
            'rincian_absensi' => json_encode(['amount' => 100000, 'raw' => 100000, 'cap' => 1250000, 'daily' => 200000, 'days' => [['date' => '2026-09-01', 'status' => 'half_day', 'deduction' => 100000, 'automatic' => 100000]]]),
            'transfer_ke' => 'Budi', 'bank' => 'BCA', 'no_rekening' => '1']);
        // Periode berikutnya (Oktober) tetap terbuka; September kini Final.
        $periods = $this->getJson('/api/v1/attendance-deductions?bulan=9&tahun=2026', $this->headers($this->hrd))->assertOk()
            ->assertJsonPath('meta.period.final', true)->assertJsonPath('meta.status.final', true)
            ->assertJsonPath('data.0.nama', 'Budi Lama')->assertJsonPath('data.0.key', $saved->id)->assertJsonPath('data.0.has_detail', true)
            ->assertJsonPath('meta.total_deduction', 100000)->json('meta.periods');
        $this->assertSame([['month' => 10, 'year' => 2026, 'final' => false], ['month' => 9, 'year' => 2026, 'final' => true]],
            array_map(fn (array $period): array => array_intersect_key($period, array_flip(['month', 'year', 'final'])), $periods));
        $this->assertArrayNotHasKey('sisa_gaji_dibayar', $this->getJson('/api/v1/attendance-deductions?bulan=9&tahun=2026', $this->headers($this->hrd))->json('data.0'));

        $this->getJson('/api/v1/attendance-deductions/'.$saved->id.'?bulan=9&tahun=2026', $this->headers($this->hrd))->assertOk()
            ->assertJsonPath('data.detail.amount', 100000)->assertJsonPath('data.jabatan', 'Mandor');
        // Rincian tersimpan tidak bisa dibaca lewat periode lain.
        $this->getJson('/api/v1/attendance-deductions/'.$saved->id.'?bulan=10&tahun=2026', $this->headers($this->hrd))->assertNotFound();
    }

    public function test_mobile_list_paginates_and_filters_like_the_web(): void
    {
        $this->getJson('/api/v1/me', $this->headers($this->hrd))->assertOk()
            ->assertJsonPath('data.permissions.attendance_deductions', true)->assertJsonPath('data.permissions.vendor_billing', false);
        $response = $this->getJson('/api/v1/attendance-deductions', $this->headers($this->hrd))->assertOk()
            ->assertJsonPath('meta.period.month', 10)->assertJsonPath('meta.period.final', false)->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.total_deduction', 200000)->assertJsonPath('meta.deducted_count', 1)
            ->assertJsonPath('meta.status.fetched_at', '2026-10-31T02:00:00+07:00');
        $this->assertSame([[1, 'Pak Direktur', 0, null], [2, 'Budi Terlambat', 200000, $this->late->id], [3, 'Sari Tepat', 0, $this->onTime->id]],
            array_map(fn (array $row): array => [$row['number'], $row['nama'], $row['potongan_absensi'], $row['key']], $response->json('data')));
        $this->assertSame(['number', 'key', 'nama', 'jabatan', 'potongan_absensi', 'has_detail'], array_keys($response->json('data.1')));

        $this->getJson('/api/v1/attendance-deductions?deductions_only=1&search=budi', $this->headers($this->hrd))->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/attendance-deductions/'.$this->late->id.'?bulan=10&tahun=2026', $this->headers($this->hrd))->assertOk()
            ->assertJsonPath('data.detail.amount', 200000)->assertJsonPath('data.name', 'Budi Terlambat');
        $this->getJson('/api/v1/attendance-deductions/'.$this->onTime->id.'?bulan=10&tahun=2026', $this->headers($this->hrd))->assertNotFound();
        $this->getJson('/api/v1/attendance-deductions/'.$this->late->id, $this->headers($this->hrd))->assertUnprocessable();
    }

    #[TestWith(['admin'])]
    #[TestWith(['operasional'])]
    #[TestWith(['vendor'])]
    public function test_other_roles_cannot_open_the_hrd_menu(string $role): void
    {
        DB::table('vendors')->insert(['id' => 1, 'nama' => 'Vendor A']);
        $user = User::factory()->create(['username' => 'user-'.$role, 'role' => $role, 'vendor_id' => $role === 'vendor' ? 1 : null, 'password' => 'secret-pass']);
        $this->getJson('/api/v1/attendance-deductions', $this->headers($user))->assertForbidden();
        $this->getJson('/api/v1/me', $this->headers($user))->assertJsonPath('data.permissions.attendance_deductions', false);
        $this->app['auth']->forgetGuards();
        $this->actingAs($user)->get('/hrd/potongan-absensi')->assertForbidden();
    }

    public function test_hrd_cannot_open_other_web_pages_or_post(): void
    {
        $this->actingAs($this->hrd);
        $this->get('/billing/gaji?bulan=10&tahun=2026')->assertForbidden();
        $this->post('/billing/gaji/attendance/refresh', ['bulan' => 10, 'tahun' => 2026])->assertForbidden();
        $this->get('/billing/gaji/attendance/'.$this->late->id.'/pdf?bulan=10&tahun=2026')->assertForbidden();
        $this->get('/pengguna')->assertForbidden();
        $this->post('/logout')->assertRedirect();
        Http::assertNothingSent();
    }
}
