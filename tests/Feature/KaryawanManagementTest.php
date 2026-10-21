<?php

namespace Tests\Feature;

use App\Models\AttendanceEmployeeSync;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class KaryawanManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'attendance.enabled' => false, 'attendance.send_immediately' => false]);
        DB::purge('sqlite');
        foreach (['2014_10_12_000000_create_users_table.php', '2023_08_12_155039_create_jabatans_table.php', '2023_08_12_155203_create_karyawans_table.php', '2023_10_18_020025_add_column_to_karyawans_table.php', '2023_10_29_134916_add_column_to_karyawans_table.php', '2026_10_10_160208_create_attendance_employee_syncs_table.php', '2026_10_20_213921_add_perjanjian_kerja_to_karyawans_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Schema::table('karyawans', function (Blueprint $table): void {
            $table->boolean('apa_bpjs_tk')->default(false);
            $table->boolean('apa_bpjs_kesehatan')->default(false);
        });
        foreach (['kas_bons', 'kas_bon_cicilans', 'rekap_gaji_details'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
                $table->boolean('void')->default(false);
                $table->boolean('lunas')->default(false);
            });
        }
        Http::preventStrayRequests();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    private function login(string $role = 'admin'): User
    {
        $actor = User::factory()->create(['username' => fake()->unique()->userName(), 'role' => $role, 'password' => 'secret123']);
        $this->actingAs($actor);

        return $actor;
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_attendance_integration_button_follows_enabled_configuration(bool $enabled): void
    {
        $this->login();
        config(['attendance.enabled' => $enabled]);
        $response = $this->get(route('karyawan.index'))->assertOk();
        if ($enabled) {
            $response->assertSee('Integrasi Absensi')->assertSee(route('attendance-integration.index'));
        } else {
            $response->assertDontSee('Integrasi Absensi')->assertDontSee(route('attendance-integration.index'));
        }
    }

    private function payload(array $attributes = []): array
    {
        return array_merge([
            'nama' => 'Pegawai Uji', 'nickname' => 'Pegawai', 'jabatan_id' => Jabatan::create(['nama' => 'Staff'])->id,
            'gaji_pokok' => '5.000.000', 'tunjangan_jabatan' => '', 'tunjangan_keluarga' => '250.000',
            'nik' => '0012345678901234', 'npwp' => '-', 'tempat_lahir' => 'Palembang', 'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Alamat pegawai', 'no_hp' => '08123456789', 'no_wa' => '08123456789',
            'bank' => 'Mandiri', 'no_rekening' => '0000123456', 'nama_rekening' => 'Pegawai Uji',
            'mulai_bekerja' => '2026-01-01', 'status' => 'aktif',
        ], $attributes);
    }

    private function photos(): array
    {
        return ['perjanjian_kerja' => UploadedFile::fake()->create('perjanjian.pdf', 10, 'application/pdf'), 'foto_ktp' => UploadedFile::fake()->image('ktp.png'), 'foto_diri' => UploadedFile::fake()->image('diri.jpg')];
    }

    private function listing(array $parameters = []): TestResponse
    {
        return $this->getJson(route('karyawan.index', array_replace_recursive(['draw' => 1, 'start' => 0, 'length' => 10], $parameters)), ['X-Requested-With' => 'XMLHttpRequest']);
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    public function test_managers_can_load_index_and_forms_without_loading_employee_list(string $role): void
    {
        $this->login($role);
        $employee = Karyawan::factory()->create(['nama' => 'Nama Belum Dimuat']);
        $this->get(route('karyawan.index'))->assertOk()->assertSee('Manajemen Pegawai')->assertDontSee('Nama Belum Dimuat');
        $this->get(route('karyawan.create'))->assertOk()->assertSee('Tambah Pegawai');
        $this->get(route('karyawan.edit', $employee))->assertOk()->assertSee('Nama Belum Dimuat')->assertSee('Bank uji');
    }

    public function test_table_pages_searches_sorts_and_filters_without_exposing_personal_data(): void
    {
        $this->login();
        $position = Jabatan::create(['nama' => 'Operator']);
        for ($i = 0; $i < 115; $i++) {
            Karyawan::factory()->create(['jabatan_id' => $position->id, 'nama' => sprintf('Pegawai %03d', $i)]);
        }
        Karyawan::factory()->create(['nama' => 'Nonaktif', 'status' => 'nonaktif']);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->listing(['start' => 10])->assertOk()->assertJsonPath('recordsTotal', 116)->assertJsonPath('recordsFiltered', 115)->assertJsonCount(10, 'data')->assertJsonPath('data.0.nama', 'Pegawai 010')->assertJsonPath('data.0.DT_RowIndex', 11);
        $this->assertLessThanOrEqual(4, count(DB::getQueryLog()));
        DB::disableQueryLog();
        $this->assertArrayNotHasKey('nik', $response->json('data.0'));
        $this->assertArrayNotHasKey('gaji_pokok', $response->json('data.0'));
        $this->listing(['length' => 100000])->assertJsonCount(100, 'data');
        $this->listing(['length' => -1])->assertJsonCount(1, 'data');
        $this->listing(['search' => ['value' => 'Pegawai 020']])->assertJsonPath('recordsFiltered', 1);
        $this->listing(['search' => ['value' => 'Operator']])->assertJsonPath('recordsFiltered', 115);
        $this->listing(['status' => 'nonaktif'])->assertJsonCount(1, 'data')->assertJsonPath('data.0.nama', 'Nonaktif');
        $this->listing(['status' => 'semua', 'order' => [['column' => 1, 'dir' => 'desc']]])->assertJsonPath('recordsFiltered', 116)->assertJsonPath('data.0.nama', 'Pegawai 114');
    }

    public function test_employee_names_are_escaped_and_missing_positions_do_not_break_views(): void
    {
        $this->login();
        $employee = Karyawan::factory()->create(['nama' => '<script>alert(1)</script>']);
        Schema::disableForeignKeyConstraints();
        DB::table('jabatans')->where('id', $employee->jabatan_id)->delete();
        Schema::enableForeignKeyConstraints();
        $response = $this->listing()->assertOk();
        $this->assertStringNotContainsString('<script>', $response->json('data.0.nama'));
        $this->assertStringNotContainsString('<script>', $response->json('data.0.action'));
        $this->assertStringNotContainsString('<script>', $response->json('data.0.documents'));
        $this->get(route('karyawan.show', ['karyawan' => $employee->id, 'format' => 'html']))->assertOk()->assertSee('Jabatan belum tersedia')->assertSee('Foto diri belum tersedia')->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_pdf_biodata_handles_missing_photos_and_keeps_original_dates(): void
    {
        Storage::fake('local');
        $this->login();
        $employee = Karyawan::factory()->create(['foto_diri' => 'public/karyawan/missing.jpg']);
        $response = $this->get(route('karyawan.show', $employee))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertSame(1, preg_match_all('/\/Type\s*\/Page\b/', $response->getContent()));
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id, 'tanggal_lahir' => '1990-01-01', 'mulai_bekerja' => '2026-01-01']);
    }

    public function test_create_normalizes_amounts_flags_and_preserves_bank_and_identifiers(): void
    {
        Storage::fake('local');
        $actor = $this->login();
        $this->postJson(route('karyawan.store'), array_merge($this->payload(), $this->photos(), ['created_by' => 999]))->assertOk();
        $employee = Karyawan::sole();
        $this->assertSame('Mandiri', $employee->bank);
        $this->assertSame('0000123456', $employee->no_rekening);
        $this->assertSame('0012345678901234', $employee->nik);
        $this->assertSame(5000000, $employee->gaji_pokok);
        $this->assertSame(0, $employee->tunjangan_jabatan);
        $this->assertSame(250000, $employee->tunjangan_keluarga);
        $this->assertSame(0, $employee->apa_bpjs_tk);
        $this->assertSame('-', $employee->bpjs_tk);
        $this->assertSame($actor->id, $employee->created_by);
        Storage::disk('local')->assertExists([$employee->foto_ktp, $employee->foto_diri, $employee->perjanjian_kerja]);
    }

    public function test_number_uses_highest_existing_number_instead_of_latest_created_record(): void
    {
        Storage::fake('local');
        $this->login();
        Karyawan::factory()->create(['nomor' => 20]);
        Karyawan::factory()->create(['nomor' => 3]);
        $this->postJson(route('karyawan.store'), array_merge($this->payload(), $this->photos()))->assertOk();
        $this->assertDatabaseHas('karyawans', ['nama' => 'Pegawai Uji', 'nomor' => 21]);
    }

    public function test_update_preserves_existing_photos_and_updates_bank_without_forcing_bca(): void
    {
        Storage::fake('local');
        $actor = $this->login();
        $employee = Karyawan::factory()->create(['foto_ktp' => 'public/karyawan/missing.png', 'foto_diri' => 'public/karyawan/old.png', 'apa_bpjs_tk' => true, 'perjanjian_kerja' => 'private/karyawan/contracts/old.pdf']);
        Storage::disk('local')->put($employee->perjanjian_kerja, '%PDF-1.4 old');
        Storage::disk('local')->put($employee->foto_diri, 'old');
        $this->putJson(route('karyawan.update', $employee), $this->payload())->assertOk();
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id, 'bank' => 'Mandiri', 'foto_ktp' => 'public/karyawan/missing.png', 'foto_diri' => 'public/karyawan/old.png', 'apa_bpjs_tk' => 0, 'updated_by' => $actor->id]);
        Storage::disk('local')->assertExists('public/karyawan/old.png');
    }

    public function test_replacement_handles_missing_old_photos_and_removes_existing_old_files(): void
    {
        Storage::fake('local');
        $this->login();
        $employee = Karyawan::factory()->create(['foto_ktp' => 'public/karyawan/missing.png', 'foto_diri' => 'public/karyawan/old.png', 'perjanjian_kerja' => 'private/karyawan/contracts/old.pdf']);
        Storage::disk('local')->put($employee->perjanjian_kerja, '%PDF-1.4 old');
        Storage::disk('local')->put($employee->foto_diri, 'old');
        $this->putJson(route('karyawan.update', $employee), array_merge($this->payload(), $this->photos()))->assertOk();
        $employee->refresh();
        Storage::disk('local')->assertExists([$employee->foto_ktp, $employee->foto_diri, $employee->perjanjian_kerja]);
        Storage::disk('local')->assertMissing(['public/karyawan/old.png', 'private/karyawan/contracts/old.pdf']);
    }

    public function test_failed_update_keeps_old_files_and_removes_new_uploads(): void
    {
        Storage::fake('local');
        $this->login();
        $employee = Karyawan::factory()->create(['foto_diri' => 'public/karyawan/old.png']);
        Storage::disk('local')->put($employee->foto_diri, 'old');
        Karyawan::updating(function (): void {
            throw new RuntimeException('Simulasi gagal simpan');
        });
        $this->putJson(route('karyawan.update', $employee), array_merge($this->payload(), $this->photos()))->assertUnprocessable()->assertJsonPath('message', 'Data pegawai belum tersimpan. Silakan coba kembali.');
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id, 'foto_diri' => 'public/karyawan/old.png']);
        $this->assertSame(['public/karyawan/old.png'], Storage::disk('local')->allFiles('public/karyawan'));
        $this->assertSame([], Storage::disk('local')->allFiles('private/karyawan/contracts'));
    }

    #[TestWith(['gaji_pokok', '-100'])]
    #[TestWith(['gaji_pokok', '1.5'])]
    #[TestWith(['tunjangan_jabatan', 'abc'])]
    #[TestWith(['tanggal_lahir', 'not-a-date'])]
    #[TestWith(['tanggal_lahir', '2999-01-01'])]
    #[TestWith(['status', 'unknown'])]
    #[TestWith(['jabatan_id', 999999])]
    #[TestWith(['apa_bpjs_tk', 'invalid'])]
    public function test_invalid_values_are_rejected_without_changing_employee(string $field, mixed $value): void
    {
        $this->login();
        $employee = Karyawan::factory()->create();
        $this->putJson(route('karyawan.update', $employee), $this->payload([$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id, 'bank' => 'Bank uji']);
    }

    public function test_required_photos_and_invalid_uploads_are_rejected(): void
    {
        Storage::fake('local');
        $this->login();
        $this->postJson(route('karyawan.store'), $this->payload())->assertUnprocessable()->assertJsonValidationErrors(['foto_ktp', 'foto_diri']);
        $this->postJson(route('karyawan.store'), array_merge($this->payload(), ['foto_ktp' => UploadedFile::fake()->create('script.php', 1, 'text/plain'), 'foto_diri' => UploadedFile::fake()->image('large.jpg')->size(10001)]))->assertUnprocessable()->assertJsonValidationErrors(['foto_ktp', 'foto_diri']);
        $this->assertDatabaseCount('karyawans', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_bpjs_membership_requires_number_and_off_accepts_empty_number(): void
    {
        Storage::fake('local');
        $this->login();
        $employee = Karyawan::factory()->create();
        $this->putJson(route('karyawan.update', $employee), $this->payload(['apa_bpjs_tk' => 1, 'apa_bpjs_kesehatan' => 1]))->assertUnprocessable()->assertJsonValidationErrors(['bpjs_tk', 'bpjs_kesehatan']);
        $this->putJson(route('karyawan.update', $employee), array_merge($this->payload(['apa_bpjs_tk' => 'on', 'bpjs_tk' => '000123']), ['perjanjian_kerja' => UploadedFile::fake()->create('perjanjian.pdf', 10, 'application/pdf')]))->assertOk();
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id, 'apa_bpjs_tk' => 1, 'bpjs_tk' => '000123']);
    }

    public function test_non_ajax_validation_restores_submitted_values_in_edit_form(): void
    {
        $this->login();
        $employee = Karyawan::factory()->create();
        $this->from(route('karyawan.edit', $employee))->put(route('karyawan.update', $employee), $this->payload(['nama' => 'Nama Belum Disimpan', 'bank' => 'BNI', 'jabatan_id' => 999]))->assertRedirect(route('karyawan.edit', $employee))->assertSessionHasErrors('jabatan_id')->assertSessionHasInput('nama', 'Nama Belum Disimpan');
        $this->get(route('karyawan.edit', $employee))->assertOk()->assertSee('Nama Belum Disimpan')->assertSee('value="BNI"', false);
    }

    #[TestWith(['kas_bons', 0, 0, 'kasbon yang belum lunas'])]
    #[TestWith(['kas_bon_cicilans', 0, 0, 'cicilan kasbon yang belum lunas'])]
    #[TestWith(['kas_bons', 1, 0, 'riwayat keuangan'])]
    #[TestWith(['rekap_gaji_details', 0, 0, 'riwayat keuangan'])]
    public function test_history_blocks_deletion_without_leaking_transactions_or_deleting_files(string $table, int $paid, int $void, string $message): void
    {
        Storage::fake('local');
        $this->login();
        $employee = Karyawan::factory()->create(['foto_diri' => 'public/karyawan/photo.jpg']);
        Storage::disk('local')->put($employee->foto_diri, 'photo');
        DB::table($table)->insert(['karyawan_id' => $employee->id, 'lunas' => $paid, 'void' => $void]);
        $response = $this->deleteJson(route('karyawan.destroy', $employee))->assertUnprocessable();
        $this->assertStringContainsString($message, $response->json('message'));
        $this->assertSame(0, DB::transactionLevel());
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id]);
        $this->assertDatabaseCount($table, 1);
        Storage::disk('local')->assertExists($employee->foto_diri);
    }

    public function test_employee_without_history_can_be_deleted_and_photos_removed(): void
    {
        Storage::fake('local');
        $this->login();
        $employee = Karyawan::factory()->create(['foto_diri' => 'public/karyawan/photo.jpg', 'foto_ktp' => 'public/karyawan/missing.jpg', 'perjanjian_kerja' => 'private/karyawan/contracts/delete.pdf']);
        Storage::disk('local')->put($employee->perjanjian_kerja, '%PDF-1.4 delete');
        Storage::disk('local')->put($employee->foto_diri, 'photo');
        $this->deleteJson(route('karyawan.destroy', $employee))->assertOk();
        $this->assertDatabaseMissing('karyawans', ['id' => $employee->id]);
        Storage::disk('local')->assertMissing(['public/karyawan/photo.jpg', 'private/karyawan/contracts/delete.pdf']);
    }

    public function test_position_crud_rejects_duplicates_and_protects_assigned_employees(): void
    {
        $this->login();
        $this->postJson(route('karyawan.jabatan-store'), ['nama_jabatan_tambah' => 'Manager'])->assertOk();
        $position = Jabatan::sole();
        $this->postJson(route('karyawan.jabatan-store'), ['nama_jabatan_tambah' => 'Manager'])->assertUnprocessable()->assertJsonValidationErrors('nama_jabatan_tambah');
        $this->patchJson(route('karyawan.jabatan-update', $position), ['nama_jabatan' => 'Supervisor'])->assertOk();
        $employee = Karyawan::factory()->create(['jabatan_id' => $position->id]);
        $this->deleteJson(route('karyawan.jabatan-destroy', $position))->assertUnprocessable();
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id]);
        $this->assertDatabaseHas('jabatans', ['id' => $position->id, 'nama' => 'Supervisor']);
        $other = Jabatan::create(['nama' => 'Unused']);
        $this->deleteJson(route('karyawan.jabatan-destroy', $other))->assertOk();
        $this->assertDatabaseMissing('jabatans', ['id' => $other->id]);
    }

    public function test_employee_and_position_changes_keep_attendance_outbox_in_sync(): void
    {
        Storage::fake('local');
        $this->login();
        config(['attendance.enabled' => true]);
        $employee = Karyawan::factory()->create();
        $this->putJson(route('karyawan.update', $employee), array_merge($this->payload(['nama' => 'Nama Baru', 'jabatan_id' => $employee->jabatan_id]), ['perjanjian_kerja' => UploadedFile::fake()->create('perjanjian.pdf', 10, 'application/pdf')]))->assertOk();
        $this->assertSame('Nama Baru', AttendanceEmployeeSync::sole()->payload['name']);
        $this->patchJson(route('karyawan.jabatan-update', $employee->jabatan_id), ['nama_jabatan' => 'Jabatan Baru'])->assertOk();
        $this->assertSame('Jabatan Baru', AttendanceEmployeeSync::sole()->payload['position']);
        $this->deleteJson(route('karyawan.destroy', $employee))->assertOk();
        $this->assertSame('inactive', AttendanceEmployeeSync::sole()->payload['status']);
        Http::assertNothingSent();
    }

    public function test_contract_is_required_on_create_and_update_and_rejects_invalid_files(): void
    {
        Storage::fake('local');
        $this->login();
        $files = $this->photos();
        unset($files['perjanjian_kerja']);
        $this->postJson(route('karyawan.store'), array_merge($this->payload(), $files))->assertUnprocessable()->assertJsonValidationErrors('perjanjian_kerja');
        $employee = Karyawan::factory()->create();
        $this->putJson(route('karyawan.update', $employee), $this->payload())->assertUnprocessable()->assertJsonValidationErrors('perjanjian_kerja');
        $this->putJson(route('karyawan.update', $employee), array_merge($this->payload(), ['perjanjian_kerja' => UploadedFile::fake()->create('script.php', 1, 'text/plain')]))->assertUnprocessable()->assertJsonValidationErrors('perjanjian_kerja');
        $this->putJson(route('karyawan.update', $employee), array_merge($this->payload(), ['perjanjian_kerja' => UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf')]))->assertUnprocessable()->assertJsonValidationErrors('perjanjian_kerja');
        $this->assertNull($employee->fresh()->perjanjian_kerja);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_contract_accepts_pdf_at_maximum_size(): void
    {
        Storage::fake('local');
        $this->login();
        $employee = Karyawan::factory()->create();
        $file = UploadedFile::fake()->create('contract.pdf', 5120, 'application/pdf');

        $this->putJson(route('karyawan.update', $employee), array_merge($this->payload(), ['perjanjian_kerja' => $file]))->assertOk();

        $employee->refresh();
        $this->assertStringStartsWith('private/karyawan/contracts/', $employee->perjanjian_kerja);
        Storage::disk('local')->assertExists($employee->perjanjian_kerja);
        $this->get(route('karyawan.show', ['karyawan' => $employee->id, 'format' => 'html']))->assertOk()->assertSee('Perjanjian');
    }

    #[TestWith(['jpg'])]
    #[TestWith(['png'])]
    public function test_contract_rejects_images_even_with_pdf_filename(string $extension): void
    {
        Storage::fake('local');
        $this->login();
        $employee = Karyawan::factory()->create();
        $image = UploadedFile::fake()->image('contract.'.$extension);
        $file = new UploadedFile($image->getPathname(), 'contract.pdf', 'application/pdf', null, true);

        $this->putJson(route('karyawan.update', $employee), array_merge($this->payload(), ['perjanjian_kerja' => $file]))->assertUnprocessable()->assertJsonValidationErrors('perjanjian_kerja');

        $this->assertNull($employee->fresh()->perjanjian_kerja);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_documents_are_loaded_on_demand_and_private_paths_are_not_exposed_in_listing(): void
    {
        Storage::fake('local');
        $this->login();
        $employee = Karyawan::factory()->create(['perjanjian_kerja' => 'private/karyawan/contracts/secret.pdf', 'foto_diri' => 'public/karyawan/photo.jpg']);
        Storage::disk('local')->put($employee->perjanjian_kerja, '%PDF-1.4 contract');
        $response = $this->listing()->assertOk();
        $this->assertStringContainsString(route('karyawan.document', ['karyawan' => $employee->id, 'document' => 'perjanjian_kerja']), $response->json('data.0.documents'));
        $this->assertStringNotContainsString('private/karyawan', $response->getContent());
        $this->assertArrayNotHasKey('perjanjian_kerja', $response->json('data.0'));
        $url = route('karyawan.document', ['karyawan' => $employee->id, 'document' => 'perjanjian_kerja']);
        $this->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get(route('karyawan.document', ['karyawan' => $employee->id, 'document' => 'foto_diri']))->assertNotFound();
        $this->get(route('karyawan.document', ['karyawan' => $employee->id, 'document' => 'password']))->assertNotFound();
        $this->login('user');
        $this->get($url)->assertForbidden();
        auth()->forgetGuards();
        $this->get($url)->assertRedirect(route('login'));
    }

    public function test_unauthorized_roles_and_guests_cannot_access_employee_crud(): void
    {
        $this->get(route('karyawan.index'))->assertRedirect(route('login'));
        $this->login('user');
        $employee = Karyawan::factory()->create();
        foreach (['index', 'create', 'edit', 'show'] as $action) {
            $this->get(route('karyawan.'.$action, in_array($action, ['edit', 'show']) ? $employee : []))->assertForbidden();
        }
        $this->postJson(route('karyawan.store'), [])->assertForbidden();
        $this->putJson(route('karyawan.update', $employee), [])->assertForbidden();
        $this->deleteJson(route('karyawan.destroy', $employee))->assertForbidden();
        $this->deleteJson(route('karyawan.jabatan-destroy', $employee->jabatan_id))->assertForbidden();
    }
}
