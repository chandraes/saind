<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\Karyawan;
use App\Models\KasBesar;
use App\Models\RekapGaji;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Feature\Concerns\MobileApiRequests;
use Tests\TestCase;

/** Modul 6: Admin & SU menyimpan Form Gaji dan memperbarui absensi dari mobile, sama dengan web. */
class MobilePayrollActionTest extends TestCase
{
    use MobileApiRequests;

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
        (require glob(database_path('migrations/*_create_bpjs_health_minimums_table.php'))[0])->up();
        DB::table('bpjs_health_minimums')->insert(['year' => 2026, 'minimum_wage' => 1, 'basis' => 'UMK', 'reference' => 'Fixture pengujian']);
        DB::table('payroll_attendance_settings')->where('id', 1)->update(['enabled' => false]);
        DB::table('jenis_transaksis')->insert([['id' => 1, 'nama' => 'Masuk'], ['id' => 2, 'nama' => 'Keluar']]);
        Http::preventStrayRequests();
        $this->travelTo(Carbon::parse('2026-10-31 10:00:00'));
        Karyawan::factory()->create(['gaji_pokok' => 5000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0, 'apa_bpjs_tk' => 0, 'apa_bpjs_kesehatan' => 0]);
        KasBesar::create(['tanggal' => '2026-10-31', 'uraian' => 'Saldo awal', 'jenis_transaksi_id' => 1, 'nominal_transaksi' => 100000000, 'saldo' => 100000000,
            'modal_investor_terakhir' => 100000000, 'transfer_ke' => '-', 'bank' => '-', 'no_rekening' => '-']);
        DB::table('group_was')->insert(['untuk' => 'kas-besar', 'nama_group' => 'kas-besar-test']);
    }

    private function headers(string $role): array
    {
        return $this->mobileHeaders(User::factory()->create(['username' => Str::uuid()->toString(), 'role' => $role, 'password' => 'secret-pass']));
    }

    private function revision(): string
    {
        return app(PayrollService::class)->revision(10, 2026);
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    public function test_admin_stores_the_payroll_once_with_the_reviewed_revision(string $role): void
    {
        $this->mock(GroupWa::class)->shouldReceive('sendWa')->once()->with('kas-besar-test', \Mockery::type('string'))->andReturn(true);
        $headers = $this->headers($role);
        $this->getJson('/api/v1/payroll?bulan=10&tahun=2026', $headers)->assertOk()
            ->assertJsonPath('meta.actions.revision', $this->revision())->assertJsonPath('meta.actions.can_store', true);

        $this->postJson('/api/v1/payroll', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => str_repeat('0', 64)], $headers)
            ->assertUnprocessable()->assertJsonPath('errors.attendance.0', 'Pratinjau gaji berubah atau kedaluwarsa. Muat ulang dan tinjau sebelum menyimpan.');
        $this->postJson('/api/v1/payroll', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $this->revision()], $headers)
            ->assertOk()->assertJsonMissingPath('warning');
        $this->assertSame(1, RekapGaji::where('bulan', 10)->where('tahun', 2026)->count());
        $this->assertSame(2, KasBesar::count());
        $this->assertSame('Gaji Bulan Oktober Tahun 2026', KasBesar::latest('id')->value('uraian'));

        // Diketuk ulang: periode sudah dibuat.
        $this->postJson('/api/v1/payroll', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => str_repeat('0', 64)], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('bulan');
        $this->assertSame(2, KasBesar::count());
        $this->getJson('/api/v1/payroll?bulan=10&tahun=2026', $headers)->assertOk()->assertJsonMissingPath('meta.actions');
    }

    public function test_store_respects_the_processing_date_and_busy_lock(): void
    {
        $headers = $this->headers('admin');
        // Oktober baru boleh diproses mulai 26 Oktober.
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        $this->getJson('/api/v1/payroll?bulan=10&tahun=2026', $headers)->assertOk()->assertJsonPath('meta.actions.can_store', false);
        $this->postJson('/api/v1/payroll', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $this->revision()], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('bulan');
        $this->travelTo(Carbon::parse('2026-10-31 10:00:00'));
        $lock = Cache::lock('payroll-generation', 300);
        $lock->get();
        try {
            $this->postJson('/api/v1/payroll', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $this->revision()], $headers)
                ->assertUnprocessable()->assertJsonPath('errors.payroll_busy.0', 'Pembuatan gaji sedang diproses. Tunggu sebentar lalu muat ulang halaman.');
            $this->postJson('/api/v1/payroll/refresh-attendance', ['bulan' => 10, 'tahun' => 2026], $headers)
                ->assertUnprocessable()->assertJsonPath('errors.attendance.0', 'Proses gaji lain sedang berjalan.');
        } finally {
            $lock->release();
        }
        $this->assertSame(0, RekapGaji::count());
    }

    public function test_a_failed_whatsapp_keeps_the_payroll_and_warns(): void
    {
        $this->mock(GroupWa::class)->shouldReceive('sendWa')->once()->andReturn(false);
        $this->postJson('/api/v1/payroll', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $this->revision()], $this->headers('su'))
            ->assertOk()->assertJsonPath('warning', 'Notifikasi WA Kas Besar belum berhasil dikirim. Gaji sudah tersimpan; jangan ulangi.');
        $this->assertSame(1, RekapGaji::count());
    }

    public function test_other_roles_cannot_store_payroll(): void
    {
        // Tabel users bawaan migrasi lama hanya mengenal sebagian role; GM & HRD dicakup tes lain.
        foreach (['user', 'operasional', 'vendor'] as $role) {
            $headers = $this->headers($role);
            $this->postJson('/api/v1/payroll', ['bulan' => 10, 'tahun' => 2026, 'payroll_revision' => $this->revision()], $headers)->assertForbidden();
            $this->postJson('/api/v1/payroll/refresh-attendance', ['bulan' => 10, 'tahun' => 2026], $headers)->assertForbidden();
        }
        $this->assertSame(0, RekapGaji::count());
    }
}
