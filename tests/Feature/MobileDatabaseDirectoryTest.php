<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/** Database mobile (baca saja) untuk Admin & SU: biodata driver, karyawan, dan vehicle. */
class MobileDatabaseDirectoryTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('jabatans', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });
        Schema::create('karyawans', function (Blueprint $table): void {
            $table->id();
            $table->integer('nomor');
            $table->foreignId('jabatan_id');
            foreach (['nama', 'nickname', 'nik', 'npwp', 'bpjs_tk', 'bpjs_kesehatan', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'no_hp', 'no_wa',
                'bank', 'no_rekening', 'nama_rekening', 'mulai_bekerja', 'status', 'foto_ktp', 'foto_diri', 'perjanjian_kerja'] as $field) {
                $table->string($field)->nullable();
            }
            foreach (['gaji_pokok', 'tunjangan_jabatan', 'tunjangan_keluarga', 'apa_bpjs_tk', 'apa_bpjs_kesehatan'] as $field) {
                $table->bigInteger($field)->default(0);
            }
            $table->timestamps();
        });
        (require database_path('migrations/2026_07_25_184509_create_drivers_table.php'))->up();
        Storage::fake('public');
        Storage::fake('local');
        DB::table('drivers')->insert([
            ['id' => 1, 'nama' => 'Budi', 'no_sim' => 'SIM-1', 'masa_berlaku_sim' => '2027-01-31', 'no_hp' => '0811', 'no_rek' => '123',
                'nama_rek' => 'Budi', 'bank' => 'BRI', 'alamat' => 'Palembang', 'foto_sim' => UploadedFile::fake()->image('sim.jpg')->store('drivers_sim', 'public'),
                'status' => 'aktif', 'keterangan' => null],
            ['id' => 2, 'nama' => 'Andi', 'no_sim' => 'SIM-2', 'masa_berlaku_sim' => '2026-01-01', 'no_hp' => '0822', 'no_rek' => '9',
                'nama_rek' => 'Andi', 'bank' => 'BCA', 'alamat' => '-', 'foto_sim' => 'drivers_sim/hilang.jpg', 'status' => 'non_aktif', 'keterangan' => 'Keluar'],
        ]);
        DB::table('jabatans')->insert(['id' => 1, 'nama' => 'Admin Kantor']);
        $employee = ['jabatan_id' => 1, 'nik' => '1', 'npwp' => '1', 'apa_bpjs_tk' => 1, 'bpjs_tk' => 'TK-1', 'apa_bpjs_kesehatan' => 0, 'bpjs_kesehatan' => '-',
            'tempat_lahir' => 'Palembang', 'tanggal_lahir' => '1990-01-01', 'alamat' => 'Jl. A', 'no_hp' => '08', 'no_wa' => '08', 'bank' => 'BRI',
            'no_rekening' => '1', 'nama_rekening' => 'X', 'mulai_bekerja' => '2020-01-01', 'gaji_pokok' => 4500000, 'tunjangan_jabatan' => 500000,
            'tunjangan_keluarga' => 0];
        DB::table('karyawans')->insert([
            $employee + ['id' => 1, 'nomor' => 1, 'nama' => 'Sari', 'nickname' => 'Sar', 'status' => 'aktif',
                'foto_ktp' => UploadedFile::fake()->image('ktp.jpg')->store('public/karyawan', 'local'), 'foto_diri' => null,
                'perjanjian_kerja' => 'private/karyawan/contracts/a.pdf'],
            $employee + ['id' => 2, 'nomor' => 2, 'nama' => 'Tono', 'nickname' => 'Ton', 'status' => 'nonaktif', 'foto_ktp' => null, 'foto_diri' => null, 'perjanjian_kerja' => null],
        ]);
    }

    private function headers(string $role): array
    {
        $user = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role]);
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_admin_and_su_read_driver_biodata_and_sim_photo(): void
    {
        foreach (['admin', 'su'] as $role) {
            $headers = $this->headers($role);
            $this->getJson('/api/v1/database/drivers?search=sim-1', $headers)->assertOk()->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.nama', 'Budi')->assertJsonPath('data.0.masa_berlaku_sim', '2027-01-31')
                ->assertJsonPath('meta.total', 1)->assertJsonMissingPath('data.0.no_rek');
            $this->getJson('/api/v1/database/drivers/1', $headers)->assertOk()
                ->assertJsonPath('data.bank', 'BRI')->assertJsonPath('data.has_sim_photo', true);
            $this->getJson('/api/v1/database/drivers/2', $headers)->assertOk()
                ->assertJsonPath('data.keterangan', 'Keluar')->assertJsonPath('data.has_sim_photo', false);
            $photo = $this->get('/api/v1/database/drivers/1/sim-photo', $headers)->assertOk();
            $this->assertStringContainsString('no-store', $photo->headers->get('Cache-Control'));
            $this->get('/api/v1/database/drivers/2/sim-photo', $headers)->assertNotFound();
        }
    }

    public function test_admin_reads_employees_with_salary_and_photos(): void
    {
        $headers = $this->headers('admin');
        $this->getJson('/api/v1/database/employees', $headers)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Sari')->assertJsonPath('data.0.jabatan', 'Admin Kantor');
        $this->getJson('/api/v1/database/employees?status=semua&search=admin', $headers)->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/database/employees?status=nonaktif', $headers)->assertOk()->assertJsonPath('data.0.nama', 'Tono');
        $this->getJson('/api/v1/database/employees/1', $headers)->assertOk()
            ->assertJsonPath('data.gaji_pokok', 4500000)->assertJsonPath('data.tunjangan_jabatan', 500000)
            ->assertJsonPath('data.apa_bpjs_tk', true)->assertJsonPath('data.has_foto_ktp', true)
            ->assertJsonPath('data.has_foto_diri', false)->assertJsonPath('data.has_perjanjian_kerja', true);
        $this->get('/api/v1/database/employees/1/photos/foto_ktp', $headers)->assertOk();
        $this->get('/api/v1/database/employees/1/photos/foto_diri', $headers)->assertNotFound();
        // Perjanjian kerja (PDF) tidak dibuka dari mobile.
        $this->get('/api/v1/database/employees/1/photos/perjanjian_kerja', $headers)->assertNotFound();
    }

    public function test_vehicle_detail_shows_the_full_biodata(): void
    {
        DB::table('vendors')->insert(['id' => 1, 'nama' => 'Vendor A']);
        DB::table('vehicles')->insert(['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101', 'nopol' => 'BG 1', 'no_rekening' => '77']);
        $this->getJson('/api/v1/database/vehicles/1', $this->headers('su'))->assertOk()
            ->assertJsonPath('data.nopol', 'BG 1')->assertJsonPath('data.gps', false)->assertJsonPath('data.driver', null)
            ->assertJsonMissingPath('data.no_rekening');
    }

    public function test_other_roles_cannot_read_the_directory(): void
    {
        foreach (['operasional', 'general-manager', 'direksi', 'vendor', 'user'] as $role) {
            $headers = $this->headers($role);
            $this->getJson('/api/v1/database/drivers', $headers)->assertForbidden();
            $this->getJson('/api/v1/database/employees/1', $headers)->assertForbidden();
            $this->get('/api/v1/database/employees/1/photos/foto_ktp', $headers)->assertForbidden();
        }
    }
}
