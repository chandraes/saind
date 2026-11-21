<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ReminderPreferences;
use App\Services\ReminderReader;
use App\Support\ReminderRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

/** Rekap Dashboard Pengingat (reminders:refresh) dan pembacaannya per akun. */
class ReminderRefreshTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-17 10:00:00');
        config(['attendance.enabled' => false, 'cache.default' => 'array']);
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id');
            $table->foreignId('vehicle_id');
            $table->foreignId('rute_id');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kas_uang_jalan_id');
            $table->integer('status');
            $table->boolean('void')->default(false);
            $table->dateTime('tanggal_bongkar')->nullable();
            $table->timestamps();
        });
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('singkatan')->nullable();
        });
        Schema::create('invoice_tagihans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id');
            $table->string('no_invoice');
            $table->date('estimasi_pembayaran')->nullable();
            $table->bigInteger('total_tagihan');
            $table->bigInteger('sisa_tagihan');
            $table->boolean('lunas')->default(false);
        });
        Schema::create('karyawans', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->foreignId('jabatan_id')->nullable();
        });
        // Prasyarat migrasi periode absensi (menambah kolom ke rekap gaji).
        Schema::create('rekap_gaji_details', function (Blueprint $table): void {
            $table->id();
        });
        foreach ([
            '2023_08_12_155039_create_jabatans_table.php',
            '2026_10_04_145713_add_pembatasan_filter_oli_to_vehicles_table.php',
            '2026_10_04_094812_create_kategori_filter_oli_mesins_table.php',
            '2026_10_04_103058_create_filter_oli_logs_table.php',
            '2026_10_07_191623_drop_limit_ritase_from_filter_oli_logs_table.php',
            '2026_10_20_124833_create_payroll_attendance_periods_table.php',
            '2026_10_30_175959_add_sync_monitoring_to_payroll_attendance_periods_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('rutes')->insert(['id' => 1, 'nama' => 'Rute Jauh']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function user(string $role, ?int $vendor = null): User
    {
        return User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => $vendor]);
    }

    /** Satu perjalanan nolam: UJ keluar pada $createdAt, bongkar pada $unloadedAt (null = belum bongkar). */
    private function trip(int $vehicle, int $vendor, string $createdAt, ?string $unloadedAt, bool $void = false): void
    {
        $cash = DB::table('kas_uang_jalans')->insertGetId(['vendor_id' => $vendor, 'vehicle_id' => $vehicle, 'rute_id' => 1]);
        DB::table('transaksis')->insert(['kas_uang_jalan_id' => $cash, 'status' => $unloadedAt ? 3 : 2, 'void' => $void,
            'tanggal_bongkar' => $unloadedAt, 'created_at' => $createdAt, 'updated_at' => $createdAt]);
    }

    private function rows(User $user, string $type): array
    {
        return app(ReminderReader::class)->rows($user, $type);
    }

    public function test_standby_lists_active_vehicles_idle_48_hours_since_last_unloading(): void
    {
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101', 'status' => 'aktif'],
            ['id' => 2, 'vendor_id' => 1, 'nomor_lambung' => '102', 'status' => 'aktif'],
            ['id' => 3, 'vendor_id' => 2, 'nomor_lambung' => '201', 'status' => 'aktif'],
            ['id' => 4, 'vendor_id' => 1, 'nomor_lambung' => '103', 'status' => 'proses'],
            ['id' => 5, 'vendor_id' => 1, 'nomor_lambung' => '104', 'status' => 'aktif'],
        ]);
        $this->trip(1, 1, '2026-11-10 07:00:00', '2026-11-12 09:00:00');
        $this->trip(1, 1, '2026-11-13 07:00:00', '2026-11-14 08:30:00'); // bongkar terakhir: 3 hari 1 jam 30 mnt lalu
        $this->trip(2, 1, '2026-11-15 07:00:00', '2026-11-16 12:00:00'); // baru 22 jam
        $this->trip(3, 2, '2026-11-01 07:00:00', '2026-11-02 10:00:00');
        $this->trip(4, 1, '2026-11-01 07:00:00', '2026-11-02 10:00:00'); // sedang jalan (proses)
        $this->trip(5, 1, '2026-11-01 07:00:00', '2026-11-16 10:00:00', true); // bongkar void tidak dihitung
        $this->artisan('reminders:refresh')->assertSuccessful();

        $admin = $this->rows($this->user('admin'), ReminderRegistry::STANDBY);
        $this->assertSame(['201', '101'], array_column($admin, 'nomor_lambung'));
        $this->assertSame(['days' => 3, 'hours' => 1], $admin[1]['elapsed']);
        $this->assertSame(['101'], array_column($this->rows($this->user('vendor-operational', 1), ReminderRegistry::STANDBY), 'nomor_lambung'));

        // Ambang dihitung saat dibaca: tanpa rekap ulang, nolam 102 muncul setelah 48 jam.
        Carbon::setTestNow('2026-11-18 12:00:00');
        $this->assertContains('102', array_column($this->rows($this->user('admin'), ReminderRegistry::STANDBY), 'nomor_lambung'));
    }

    public function test_unloading_lists_trips_loaded_24_hours_ago_without_unloading(): void
    {
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '201']]);
        $this->trip(1, 1, '2026-11-15 08:00:00', null); // 2 hari 2 jam
        $this->trip(2, 2, '2026-11-16 12:00:00', null); // 22 jam
        $this->trip(2, 2, '2026-11-10 08:00:00', null, true); // void
        $this->artisan('reminders:refresh', ['types' => ['unloading']])->assertSuccessful();

        $rows = $this->rows($this->user('general-manager'), ReminderRegistry::UNLOADING);
        $this->assertSame(['101'], array_column($rows, 'nomor_lambung'));
        $this->assertSame(['nomor_lambung' => '101', 'vendor' => 'Vendor A', 'rute' => 'Rute Jauh'], array_intersect_key($rows[0], array_flip(['nomor_lambung', 'vendor', 'rute'])));
        $this->assertSame(['days' => 2, 'hours' => 2], $rows[0]['elapsed']);
        $this->assertSame([], $this->rows($this->user('vendor', 2), ReminderRegistry::UNLOADING));
    }

    public function test_filter_oil_shows_remaining_ritase_within_two_and_negative_past_limit(): void
    {
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101', 'pembatasan_filter_oli' => true],
            ['id' => 2, 'vendor_id' => 1, 'nomor_lambung' => '102', 'pembatasan_filter_oli' => false],
            ['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => '103', 'pembatasan_filter_oli' => true],
        ]);
        DB::table('kategori_filter_oli_mesins')->insert([['id' => 1, 'nama' => 'Oli Mesin', 'limit_ritase' => 100], ['id' => 2, 'nama' => 'Filter Solar', 'limit_ritase' => 50]]);
        DB::table('filter_oli_logs')->insert([
            ['vehicle_id' => 1, 'kategori_filter_oli_mesin_id' => 1, 'merk' => 'A', 'kondisi' => 100, 'ritase' => 99, 'created_at' => '2026-10-01'],  // lama, diabaikan
            ['vehicle_id' => 1, 'kategori_filter_oli_mesin_id' => 1, 'merk' => 'A', 'kondisi' => 100, 'ritase' => 98.5, 'created_at' => '2026-11-01'], // sisa 1,5
            ['vehicle_id' => 1, 'kategori_filter_oli_mesin_id' => 2, 'merk' => 'B', 'kondisi' => 100, 'ritase' => 10, 'created_at' => '2026-11-01'],   // jauh dari limit
            ['vehicle_id' => 2, 'kategori_filter_oli_mesin_id' => 2, 'merk' => 'B', 'kondisi' => 100, 'ritase' => 53, 'created_at' => '2026-11-01'],   // lewat 3
            ['vehicle_id' => 3, 'kategori_filter_oli_mesin_id' => 1, 'merk' => 'A', 'kondisi' => 100, 'ritase' => 20, 'created_at' => '2026-11-01'],   // aman
        ]);
        $this->artisan('reminders:refresh', ['types' => ['filter_oil']])->assertSuccessful();

        $admin = $this->rows($this->user('admin'), ReminderRegistry::FILTER_OIL);
        $this->assertSame(['101'], array_column($admin, 'nomor_lambung'));
        $this->assertSame(['1' => 1.5], $admin[0]['sisa']);
        // Vendor tidak memerlukan pengaktifan admin: nolam 102 ikut, sisa negatif.
        $vendor = $this->rows($this->user('vendor', 1), ReminderRegistry::FILTER_OIL);
        $this->assertSame(['101', '102'], array_column($vendor, 'nomor_lambung'));
        $this->assertSame(['2' => -3], $vendor[1]['sisa']);
    }

    public function test_invoice_due_within_two_days_and_overdue_negative(): void
    {
        DB::table('customers')->insert(['id' => 1, 'nama' => 'PT Tambang Satu', 'singkatan' => 'TS']);
        DB::table('invoice_tagihans')->insert([
            ['id' => 1, 'customer_id' => 1, 'no_invoice' => 'INV-1', 'estimasi_pembayaran' => '2026-11-19', 'total_tagihan' => 1000, 'sisa_tagihan' => 600, 'lunas' => 0],
            ['id' => 2, 'customer_id' => 1, 'no_invoice' => 'INV-2', 'estimasi_pembayaran' => '2026-11-14', 'total_tagihan' => 500, 'sisa_tagihan' => 500, 'lunas' => 0],
            ['id' => 3, 'customer_id' => 1, 'no_invoice' => 'INV-3', 'estimasi_pembayaran' => '2026-11-20', 'total_tagihan' => 500, 'sisa_tagihan' => 500, 'lunas' => 0],
            ['id' => 4, 'customer_id' => 1, 'no_invoice' => 'INV-4', 'estimasi_pembayaran' => '2026-11-15', 'total_tagihan' => 500, 'sisa_tagihan' => 0, 'lunas' => 1],
        ]);
        $this->artisan('reminders:refresh', ['types' => ['invoice_due']])->assertSuccessful();

        $rows = $this->rows($this->user('user'), ReminderRegistry::INVOICE_DUE);
        $this->assertSame(['INV-2', 'INV-1'], array_column($rows, 'no_invoice'));
        $this->assertSame([-3, 2], array_column($rows, 'sisa_hari'));
        $this->assertEquals([500, 600], array_column($rows, 'sisa_tagihan'));
    }

    public function test_attendance_lists_yesterday_absences_with_streak_skipping_days_off(): void
    {
        config(['attendance.enabled' => true]);
        DB::table('jabatans')->insert(['id' => 1, 'nama' => 'Mekanik']);
        DB::table('karyawans')->insert([['id' => 1, 'nama' => 'Andi', 'jabatan_id' => 1], ['id' => 2, 'nama' => 'Budi', 'jabatan_id' => 1], ['id' => 3, 'nama' => 'Cici', 'jabatan_id' => 1]]);
        // Kemarin = 16 Nov. Andi: 12 alpa, 13 alpa, 14-15 libur, 16 alpa (berturut 3; total periode 4 karena 2 Nov juga alpa).
        $days = fn (array $statuses): array => collect(range(0, 30))->map(fn (int $i): array => ['date' => Carbon::parse('2026-10-26')->addDays($i)->toDateString(),
            'status' => $statuses[Carbon::parse('2026-10-26')->addDays($i)->toDateString()] ?? 'present'])->all();
        $snapshot = ['monitoring_version' => 1, 'from' => '2026-10-26', 'to' => '2026-11-25', 'employees' => [
            ['source_employee_id' => '1', 'name' => 'Andi', 'attendance_required' => true, 'days' => $days(['2026-11-02' => 'absent', '2026-11-12' => 'absent', '2026-11-13' => 'absent', '2026-11-14' => 'off', '2026-11-15' => 'holiday', '2026-11-16' => 'absent'])],
            ['source_employee_id' => '2', 'name' => 'Budi', 'attendance_required' => true, 'days' => $days(['2026-11-15' => 'absent', '2026-11-16' => 'off'])],
            ['source_employee_id' => '3', 'name' => 'Cici', 'attendance_required' => true, 'days' => $days(['2026-11-15' => 'excused', '2026-11-16' => 'absent'])],
        ]];
        DB::table('payroll_attendance_periods')->insert(['year' => 2026, 'month' => 11, 'from_date' => '2026-10-26', 'to_date' => '2026-11-25',
            'request_id' => (string) Str::uuid(), 'snapshot' => json_encode($snapshot), 'fetched_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $this->artisan('reminders:refresh', ['types' => ['attendance']])->assertSuccessful();

        $rows = collect($this->rows($this->user('hrd'), ReminderRegistry::ATTENDANCE))->keyBy('nama');
        $this->assertSame(['Andi', 'Cici'], $rows->keys()->sort()->values()->all());
        $this->assertSame(['jabatan' => 'Mekanik', 'berturut' => 3, 'total' => 4], array_intersect_key($rows['Andi'], array_flip(['jabatan', 'berturut', 'total'])));
        $this->assertSame([1, 1], [$rows['Cici']['berturut'], $rows['Cici']['total']]);
        // Rekap kemarin tidak tampil lagi esok hari (sebelum direkap ulang).
        Carbon::setTestNow('2026-11-18 09:00:00');
        $this->assertSame([], $this->rows($this->user('hrd'), ReminderRegistry::ATTENDANCE));
    }

    public function test_roles_per_table_and_refresh_time_is_recorded(): void
    {
        $this->artisan('reminders:refresh')->assertSuccessful();
        $this->assertSame([ReminderRegistry::ATTENDANCE], ReminderRegistry::for($this->user('hrd')));
        $this->assertSame([ReminderRegistry::STANDBY, ReminderRegistry::UNLOADING, ReminderRegistry::FILTER_OIL], ReminderRegistry::for($this->user('vendor-induk', 1)));
        $this->assertSame([ReminderRegistry::FILTER_OIL, ReminderRegistry::INVOICE_DUE, ReminderRegistry::ATTENDANCE], ReminderRegistry::for($this->user('user')));
        $this->assertSame(ReminderRegistry::keys(), ReminderRegistry::for($this->user('su')));
        $this->assertSame(ReminderRegistry::keys(), ReminderRegistry::for($this->user('direksi')));
        $this->assertSame([], ReminderRegistry::for($this->user('driver')));
        $this->assertSame('2026-11-17 10:00:00', app(ReminderReader::class)->refreshedAt(ReminderRegistry::INVOICE_DUE)?->toDateTimeString());

        $this->expectException(HttpException::class);
        app(ReminderReader::class)->query($this->user('vendor', 1), ReminderRegistry::INVOICE_DUE);
    }

    public function test_reminder_page_shows_only_permitted_tables_and_data_is_vendor_scoped(): void
    {
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '201']]);
        $this->trip(1, 1, '2026-11-15 08:00:00', null);
        $this->trip(2, 2, '2026-11-15 08:00:00', null);
        $this->artisan('reminders:refresh')->assertSuccessful();

        $this->actingAs($this->user('vendor', 1));
        $this->get('/pengingat')->assertOk()->assertSee('reminder-table-unloading', false)->assertSee('reminder-table-standby', false)
            ->assertDontSee('reminder-table-invoice_due', false)->assertDontSee('reminder-table-attendance', false)->assertSee('Diperbarui 17-11-2026 10:00');
        $this->getJson('/pengingat/unloading/data')->assertOk()->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.nomor_lambung', '101')->assertJsonPath('data.0.elapsed', ['days' => 2, 'hours' => 2]);
        $this->getJson('/pengingat/invoice_due/data')->assertForbidden();

        $this->actingAs($this->user('hrd'));
        $this->get('/pengingat')->assertOk()->assertSee('reminder-table-attendance', false)->assertDontSee('reminder-table-unloading', false)
            ->assertSee('Sinkron absensi belum aktif');
        $this->getJson('/pengingat/unloading/data')->assertForbidden();

        $this->actingAs($this->user('asisten-user'));
        $this->get('/pengingat')->assertForbidden();
        $this->getJson('/pengingat/standby/data')->assertForbidden();
        $this->getJson('/pengingat/unknown/data')->assertNotFound();
    }

    public function test_dashboard_tiles_show_the_reminder_badge(): void
    {
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '201']]);
        $this->trip(1, 1, '2026-11-15 08:00:00', null);
        $this->trip(2, 2, '2026-11-15 08:00:00', null);
        $this->artisan('reminders:refresh')->assertSuccessful();
        $badge = fn (string $html): ?string => preg_match('/data-reminder-badge>(\d+)/', $html, $match) ? $match[1] : null;

        $this->actingAs($this->user('admin'));
        $this->assertSame('2', $badge($this->get('/home')->assertOk()->assertSee('PENGINGAT')->getContent()));
        $this->actingAs($this->user('vendor', 1));
        $this->assertSame('1', $badge($this->get('/home')->assertOk()->getContent()));
        $this->actingAs($this->user('hrd'));
        $this->assertSame('0', $badge($this->get('/home')->assertOk()->assertSee('Potongan Absensi')->getContent()));
    }

    public function test_card_dashboards_show_a_one_row_reminder_button_and_can_switch_back_to_cards(): void
    {
        $this->artisan('reminders:refresh')->assertSuccessful();
        $vendor = $this->user('vendor', 1);
        $admin = $this->user('admin');

        // Bawaan: tombol panjang, tanpa seksi/judul "Pengingat"; tile klasik admin tidak berubah.
        $this->actingAs($vendor)->get('/home')->assertOk()
            ->assertSee('class="vendor-reminder-button"', false)->assertDontSee('id="vendor-section-reminders"', false)
            // Tanpa pengingat: tidak menempel saat digulir.
            ->assertDontSee('vendor-reminder-button is-sticky', false)
            ->assertSeeInOrder(['vendor-reminder-button', 'vendor-section-documents'], false);
        $this->actingAs($admin)->get('/home')->assertOk()->assertSee('PENGINGAT')->assertDontSee('vendor-reminder-button', false);

        // Ada pengingat: tombol menempel di atas layar.
        DB::table('vehicles')->insert(['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101']);
        $this->trip(1, 1, '2026-11-15 08:00:00', null);
        $this->artisan('reminders:refresh')->assertSuccessful();
        $this->actingAs($vendor)->get('/home')->assertOk()->assertSee('class="vendor-reminder-button is-sticky"', false);

        // Kembali ke tampilan kartu cukup lewat konfigurasi.
        config(['reminders.dashboard_entry' => 'card']);
        $this->actingAs($vendor)->get('/home')->assertOk()
            ->assertSee('id="vendor-section-reminders"', false)->assertDontSee('class="vendor-reminder-button"', false);
    }

    public function test_each_account_orders_and_hides_its_tables(): void
    {
        DB::table('vehicles')->insert(['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101']);
        $this->trip(1, 1, '2026-11-15 08:00:00', null);
        $this->artisan('reminders:refresh')->assertSuccessful();
        $vendor = $this->user('vendor', 1);
        $this->actingAs($vendor);

        // Bawaan: semua tabel role aktif, urutan 1-3.
        $this->get('/pengingat')->assertOk()->assertSeeInOrder(['reminder-table-standby', 'reminder-table-unloading', 'reminder-table-filter_oil'], false);
        $this->post('/pengingat/pengaturan', ['order' => ['filter_oil', 'unloading', 'standby'], 'enabled' => ['filter_oil', 'standby']])
            ->assertRedirect('/pengingat')->assertSessionHas('success');
        $this->get('/pengingat')->assertOk()->assertSeeInOrder(['reminder-table-filter_oil', 'reminder-table-standby'], false)
            ->assertDontSee('reminder-table-unloading', false)->assertSee('reminder-enabled-unloading', false);
        // Tabel nonaktif tidak dihitung di lencana (1 perjalanan belum bongkar ada di tabel unloading).
        $this->assertStringContainsString('data-reminder-badge>0', $this->get('/home')->getContent());

        // Semua dinonaktifkan: pesan ajakan mengaktifkan kembali.
        $this->post('/pengingat/pengaturan', ['order' => ['filter_oil', 'unloading', 'standby']]);
        $this->get('/pengingat')->assertOk()->assertSee('Semua tabel pengingat sedang dinonaktifkan');

        // Daftar harus persis tabel milik role; akun lain tidak terpengaruh.
        $this->post('/pengingat/pengaturan', ['order' => ['filter_oil', 'unloading', 'invoice_due'], 'enabled' => []])->assertSessionHasErrors('order');
        $this->post('/pengingat/pengaturan', ['order' => ['filter_oil', 'unloading'], 'enabled' => []])->assertSessionHasErrors('order');
        $this->assertSame(3, DB::table('user_reminder_settings')->where('user_id', $vendor->id)->count());
        $this->actingAs($this->user('vendor', 1));
        $this->get('/pengingat')->assertOk()->assertSeeInOrder(['reminder-table-standby', 'reminder-table-unloading', 'reminder-table-filter_oil'], false);
    }

    public function test_read_only_roles_may_save_only_their_reminder_settings(): void
    {
        foreach (['general-manager', 'direksi', 'hrd'] as $role) {
            $user = $this->user($role);
            $order = ReminderRegistry::for($user);
            $this->actingAs($user)->post('/pengingat/pengaturan', ['order' => array_reverse($order), 'enabled' => [$order[0]]])->assertRedirect('/pengingat');
            $this->assertSame([$order[0]], app(ReminderPreferences::class)->enabled($user));
        }
        // Penulisan lain tetap ditolak untuk eksekutif & HRD.
        $this->actingAs($this->user('general-manager'))->post('/statistik/aki/1/ganti')->assertForbidden();
        $this->actingAs($this->user('hrd'))->post('/statistik/aki/1/ganti')->assertForbidden();
        $this->actingAs($this->user('asisten-user'))->post('/pengingat/pengaturan', ['order' => []])->assertForbidden();
    }

    /** Header token mobile (tanpa X-Vendor-ID: pengingat di luar konteks vendor). */
    private function api(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_mobile_api_lists_tables_rows_and_saves_shared_settings(): void
    {
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101', 'pembatasan_filter_oli' => true],
            ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '201', 'pembatasan_filter_oli' => true],
        ]);
        $this->trip(1, 1, '2026-11-15 08:00:00', null);
        $this->trip(2, 2, '2026-11-15 08:00:00', null);
        DB::table('kategori_filter_oli_mesins')->insert([['id' => 1, 'nama' => 'Oli Mesin', 'limit_ritase' => 100], ['id' => 2, 'nama' => 'Filter Solar', 'limit_ritase' => 50]]);
        DB::table('filter_oli_logs')->insert(['vehicle_id' => 1, 'kategori_filter_oli_mesin_id' => 2, 'merk' => 'B', 'kondisi' => 100, 'ritase' => 51, 'created_at' => '2026-11-01']);
        $this->artisan('reminders:refresh')->assertSuccessful();
        $vendor = $this->user('vendor', 1);
        $headers = $this->api($vendor);

        $this->getJson('/api/v1/me', $headers)->assertOk()->assertJsonPath('data.permissions.reminders', true);
        $this->getJson('/api/v1/reminders', $headers)->assertOk()
            ->assertJsonPath('data.*.key', ['standby', 'unloading', 'filter_oil'])
            ->assertJson(['data' => [1 => ['key' => 'unloading', 'label' => 'On Going', 'title' => ReminderRegistry::title('unloading'),
                'description' => ReminderRegistry::meta('unloading')['description'], 'icon' => 'fa-hourglass-half', 'enabled' => true, 'count' => 1,
                'refreshed_at' => '2026-11-17T10:00:00+07:00']]])
            ->assertJsonPath('meta.total', 2);
        // Baris: hanya vendor sendiri, bentuk NotePage (id + meta halaman), nomor urut.
        $this->getJson('/api/v1/reminders/unloading', $headers)->assertOk()
            ->assertJsonPath('data.0.number', 1)->assertJsonPath('data.0.nomor_lambung', '101')
            ->assertJsonPath('data.0.elapsed', ['days' => 2, 'hours' => 2])->assertJsonCount(1, 'data')
            ->assertJsonPath('meta', ['current_page' => 1, 'last_page' => 1, 'total' => 1, 'refreshed_at' => '2026-11-17T10:00:00+07:00']);
        // Filter & oli: hanya kategori yang perlu perhatian beserta namanya.
        $this->getJson('/api/v1/reminders/filter_oil', $headers)->assertOk()
            ->assertJsonPath('data.0.kategori', [['id' => 2, 'nama' => 'Filter Solar', 'sisa' => -1]]);
        $this->getJson('/api/v1/reminders/invoice_due', $headers)->assertForbidden();

        // Pengaturan dari HP berlaku juga di web (tabel yang sama).
        $this->postJson('/api/v1/reminders/settings', ['order' => ['filter_oil', 'standby', 'unloading'], 'enabled' => ['filter_oil', 'standby']], $headers)
            ->assertOk()->assertJsonPath('data.*.key', ['filter_oil', 'standby', 'unloading'])
            ->assertJsonPath('data.2.enabled', false)->assertJsonPath('data.2.count', null)->assertJsonPath('meta.total', 1);
        $this->assertSame(['filter_oil', 'standby'], app(ReminderPreferences::class)->enabled($vendor));
        $this->postJson('/api/v1/reminders/settings', ['order' => ['filter_oil'], 'enabled' => []], $headers)->assertUnprocessable();
    }

    public function test_mobile_api_roles_without_reminders_and_read_only_roles(): void
    {
        $this->artisan('reminders:refresh')->assertSuccessful();
        $hrd = $this->user('hrd');
        $headers = $this->api($hrd);
        $this->getJson('/api/v1/reminders', $headers)->assertOk()->assertJsonPath('data.*.key', ['attendance'])->assertJsonPath('meta.attendance_enabled', false);
        $this->postJson('/api/v1/reminders/settings', ['order' => ['attendance'], 'enabled' => []], $headers)->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson('/api/v1/reminders/standby', $headers)->assertForbidden();

        $gm = $this->api($this->user('general-manager'));
        $this->getJson('/api/v1/reminders', $gm)->assertOk()->assertJsonCount(5, 'data');

        // Asisten-user di mobile memakai tabel pengingat user (web tetap tanpa Pengingat, lihat tes web).
        $assistant = $this->api($this->user('asisten-user'));
        $this->getJson('/api/v1/me', $assistant)->assertOk()->assertJsonPath('data.permissions.reminders', true);
        $this->getJson('/api/v1/reminders', $assistant)->assertOk()->assertJsonPath('data.*.key', ['filter_oil', 'invoice_due', 'attendance']);
        $this->getJson('/api/v1/reminders/standby', $assistant)->assertForbidden();
        $this->getJson('/api/v1/reminders/unknown', $assistant)->assertNotFound();
    }
}
