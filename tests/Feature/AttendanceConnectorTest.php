<?php

namespace Tests\Feature;

use App\Models\AttendanceEmployeeSync;
use App\Models\Karyawan;
use App\Models\User;
use App\Services\AttendanceConnector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use RuntimeException;
use Tests\TestCase;

class AttendanceConnectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'attendance.enabled' => true,
            'attendance.url' => 'https://attendance.test', 'attendance.token' => str_repeat('s', 64), 'attendance.ca_bundle' => null, 'attendance.send_immediately' => false]);
        DB::purge('sqlite');
        foreach (['2014_10_12_000000_create_users_table.php', '2023_08_12_155039_create_jabatans_table.php', '2023_08_12_155203_create_karyawans_table.php',
            '2023_10_18_020025_add_column_to_karyawans_table.php', '2023_10_29_134916_add_column_to_karyawans_table.php', '2026_10_10_160208_create_attendance_employee_syncs_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Http::preventStrayRequests();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    private function acknowledge(): void
    {
        Http::fake(['attendance.test/*' => fn ($request) => Http::response(['applied_version' => $request['source_version'], 'data' => ['source_employee_id' => $request['source_employee_id']]])]);
    }

    public function test_employee_save_captures_only_required_identity_fields(): void
    {
        $employee = Karyawan::factory()->create(['nama' => 'Pegawai Uji', 'kode' => 'STAFF-', 'nomor' => 7, 'gaji_pokok' => 5000000]);
        $sync = AttendanceEmployeeSync::sole();
        $this->assertSame((string) $employee->id, $sync->source_employee_id);
        $this->assertSame(['source_employee_id', 'employee_number', 'name', 'position', 'status', 'employment_started_at'], array_keys($sync->payload));
        $this->assertSame('STAFF-7', $sync->payload['employee_number']);
        $this->assertSame('Pegawai Uji', $sync->payload['name']);
        $this->assertSame('active', $sync->payload['status']);
        $this->assertSame(1, $sync->source_version);
        Http::assertNothingSent();
    }

    public function test_irrelevant_updates_do_not_increment_version_but_identity_updates_do(): void
    {
        $employee = Karyawan::factory()->create();
        $employee->update(['gaji_pokok' => 6000000]);
        $this->assertSame(1, AttendanceEmployeeSync::sole()->source_version);
        $employee->update(['nama' => 'Nama diperbarui']);
        $sync = AttendanceEmployeeSync::sole();
        $this->assertSame(2, $sync->source_version);
        $this->assertSame('Nama diperbarui', $sync->payload['name']);
    }

    public function test_employee_and_outbox_changes_roll_back_together(): void
    {
        $employee = Karyawan::factory()->create(['nama' => 'Nama semula']);
        config(['attendance.send_immediately' => true]);
        try {
            DB::transaction(function () use ($employee): void {
                $employee->update(['nama' => 'Tidak jadi']);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
        }
        $this->assertSame('Nama semula', $employee->fresh()->nama);
        $this->assertSame('Nama semula', AttendanceEmployeeSync::sole()->payload['name']);
        $this->assertSame(1, AttendanceEmployeeSync::sole()->source_version);
        Http::assertNothingSent();
    }

    public function test_inactive_and_deleted_employees_are_sent_as_inactive(): void
    {
        $employee = Karyawan::factory()->create();
        $employee->update(['status' => 'nonaktif']);
        $this->assertSame('inactive', AttendanceEmployeeSync::sole()->payload['status']);
        $employee->update(['status' => 'aktif']);
        $employee->delete();
        $this->assertDatabaseMissing('karyawans', ['id' => $employee->id]);
        $sync = AttendanceEmployeeSync::sole();
        $this->assertSame('inactive', $sync->payload['status']);
        $this->assertSame(4, $sync->source_version);
    }

    public function test_successful_send_is_acknowledged_and_not_repeated(): void
    {
        Karyawan::factory()->create();
        $this->acknowledge();
        $sync = AttendanceEmployeeSync::sole();
        $connector = app(AttendanceConnector::class);
        $this->assertTrue($connector->send($sync->id));
        $this->assertFalse($connector->send($sync->id));
        $this->assertSame(1, $sync->fresh()->delivered_version);
        $this->assertNotNull($sync->fresh()->last_synced_at);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->method() === 'PUT' && $request->hasHeader('Authorization', 'Bearer '.str_repeat('s', 64)) && $request['source_version'] === 1);
    }

    public function test_newer_change_during_send_stays_pending_until_its_own_acknowledgment(): void
    {
        $employee = Karyawan::factory()->create();
        $sync = AttendanceEmployeeSync::sole();
        Http::fake(['attendance.test/*' => function ($request) use ($employee) {
            $employee->update(['nama' => 'Nama terbaru']);

            return Http::response(['applied_version' => $request['source_version'], 'data' => ['source_employee_id' => $request['source_employee_id']]]);
        }]);
        $connector = app(AttendanceConnector::class);
        $this->assertTrue($connector->send($sync->id));
        $this->assertSame(2, $sync->fresh()->source_version);
        $this->assertSame(1, $sync->fresh()->delivered_version);
        $this->acknowledge();
        $this->assertTrue($connector->send($sync->id));
        $this->assertSame(2, $sync->fresh()->delivered_version);
    }

    public function test_server_failure_preserves_pending_change_and_retries_after_backoff(): void
    {
        Karyawan::factory()->create();
        $sync = AttendanceEmployeeSync::sole();
        Http::fake(['attendance.test/*' => Http::sequence()->push(['secret' => str_repeat('s', 64)], 500)->push(['applied_version' => 1, 'data' => ['source_employee_id' => $sync->source_employee_id]])]);
        $connector = app(AttendanceConnector::class);
        $this->assertFalse($connector->send($sync->id));
        $this->assertSame(0, $sync->fresh()->delivered_version);
        $this->assertSame(1, $sync->fresh()->attempts);
        $this->assertStringNotContainsString(str_repeat('s', 64), $sync->fresh()->last_error);
        $this->assertFalse($connector->send($sync->id));
        Http::assertSentCount(1);
        $this->travel(16)->seconds();
        $this->assertTrue($connector->send($sync->id));
        $this->assertNull($sync->fresh()->last_error);
        $this->assertSame(1, $sync->fresh()->delivered_version);
    }

    public function test_network_failure_does_not_undo_committed_employee_save(): void
    {
        config(['attendance.send_immediately' => true]);
        Http::fake(['attendance.test/*' => Http::failedConnection()]);
        $employee = Karyawan::factory()->create(['nama' => 'Tetap tersimpan']);
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id, 'nama' => 'Tetap tersimpan']);
        $sync = AttendanceEmployeeSync::sole();
        $this->assertSame(1, $sync->attempts);
        $this->assertSame(0, $sync->delivered_version);
        Http::assertSentCount(1);
    }

    public function test_wrong_acknowledgment_does_not_mark_sync_delivered(): void
    {
        Karyawan::factory()->create();
        $sync = AttendanceEmployeeSync::sole();
        Http::fake(['attendance.test/*' => Http::response(['applied_version' => 99, 'data' => ['source_employee_id' => 'foreign']])]);
        $this->assertFalse(app(AttendanceConnector::class)->send($sync->id));
        $this->assertSame(0, $sync->fresh()->delivered_version);
        $this->assertNotNull($sync->fresh()->last_error);
    }

    public function test_reconciliation_captures_bulk_updates_deletes_and_position_rename(): void
    {
        $employee = Karyawan::factory()->create();
        $employee->jabatan->update(['nama' => 'Supervisor']);
        DB::table('karyawans')->where('id', $employee->id)->update(['nama' => 'Diubah langsung']);
        $connector = app(AttendanceConnector::class);
        $this->assertSame(1, $connector->reconcile());
        $sync = AttendanceEmployeeSync::sole();
        $this->assertSame('Supervisor', $sync->payload['position']);
        $this->assertSame('Diubah langsung', $sync->payload['name']);
        DB::table('karyawans')->where('id', $employee->id)->delete();
        $connector->reconcile();
        $this->assertSame('inactive', $sync->fresh()->payload['status']);
    }

    public function test_initial_sync_is_idempotent_and_force_creates_next_version(): void
    {
        config(['attendance.enabled' => false]);
        Karyawan::factory()->create();
        config(['attendance.enabled' => true]);
        $this->artisan('attendance:sync-employees')->assertSuccessful();
        $this->artisan('attendance:sync-employees')->assertSuccessful();
        $this->assertSame(1, AttendanceEmployeeSync::sole()->source_version);
        $this->artisan('attendance:sync-employees', ['--force' => true])->assertSuccessful();
        $this->assertSame(2, AttendanceEmployeeSync::sole()->source_version);
    }

    public function test_disabled_or_insecure_connector_does_not_send_credentials(): void
    {
        Karyawan::factory()->create();
        config(['attendance.url' => 'http://attendance.test']);
        $this->assertFalse(app(AttendanceConnector::class)->send(AttendanceEmployeeSync::sole()->id));
        $this->artisan('attendance:flush')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_active_lease_prevents_duplicate_workers(): void
    {
        $sync = AttendanceEmployeeSync::factory()->create(['lease_token' => 'taken', 'lease_until' => now()->addSeconds(30)]);
        $this->assertFalse(app(AttendanceConnector::class)->send($sync->id));
        Http::assertNothingSent();
        $this->travel(31)->seconds();
        $this->acknowledge();
        $this->assertTrue(app(AttendanceConnector::class)->send($sync->id));
    }

    public function test_results_are_read_via_fixed_company_scoped_api_and_source_employee_id(): void
    {
        Http::fake(['attendance.test/*' => Http::response(['data' => [['id' => 5, 'employee' => ['source_employee_id' => '9']]], 'current_page' => 1, 'last_page' => 1, 'total' => 1])]);
        $results = app(AttendanceConnector::class)->results('attendances', ['source_employee_id' => '9', 'from' => '2026-10-01', 'page' => 1]);
        $this->assertSame('9', $results['data'][0]['employee']['source_employee_id']);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://attendance.test/api/v1/integration/attendances?') && $request['source_employee_id'] === '9' && $request['from'] === '2026-10-01');
    }

    public function test_non_admin_roles_cannot_read_or_trigger_integration(): void
    {
        foreach (['user', 'vendor', 'operasional'] as $role) {
            $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role, 'password' => 'password']));
            $this->get('/attendance-integration')->assertForbidden();
            $this->post('/attendance-integration/sync')->assertForbidden();
        }
        Http::assertNothingSent();
    }

    public function test_admin_can_trigger_sync_and_read_scoped_results(): void
    {
        Karyawan::factory()->create();
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'su', 'password' => 'password']));
        Http::fake(['attendance.test/*' => fn ($request) => $request->method() === 'PUT'
            ? Http::response(['applied_version' => $request['source_version'], 'data' => ['source_employee_id' => $request['source_employee_id']]])
            : Http::response(['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0])]);
        $this->post('/attendance-integration/sync')->assertRedirect('/attendance-integration');
        $this->assertSame(1, AttendanceEmployeeSync::sole()->delivered_version);
        $this->get('/attendance-integration?resource=attendances&source_employee_id=1')->assertOk()->assertSee('Belum ada hasil untuk filter ini.');
        Http::assertSentCount(2);
    }

    public function test_result_failure_is_visible_and_filters_are_validated(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
        Http::fake(['attendance.test/*' => Http::failedConnection()]);
        $this->get('/attendance-integration?resource=attendances')->assertOk()->assertSee('Koneksi ke aplikasi absensi gagal atau melewati batas waktu.');
        $this->get('/attendance-integration?resource=users')->assertSessionHasErrors('resource');
        Http::assertSentCount(1);
    }

    public function test_integration_page_is_protected_and_shows_escaped_status(): void
    {
        $this->get('/attendance-integration')->assertRedirect('/login');
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
        Karyawan::factory()->create(['nama' => '<script>alert(1)</script>']);
        $this->get('/attendance-integration')->assertOk()->assertSee('Penghubung aktif')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }
}
