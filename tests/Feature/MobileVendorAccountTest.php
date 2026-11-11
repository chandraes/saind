<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;

/** Akun vendor di aplikasi mobile: login, Kas Vendor, dan edit/hapus riwayat ban & aki. */
class MobileVendorAccountTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('kas_vendors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->date('tanggal');
            $table->string('uraian');
            $table->unsignedBigInteger('invoice_bayar_id')->nullable();
            $table->unsignedBigInteger('invoice_add_vendor_id')->nullable();
            $table->bigInteger('pinjaman')->default(0);
            $table->bigInteger('bayar')->default(0);
            $table->bigInteger('sisa')->default(0);
            $table->timestamps();
        });
        Schema::create('invoice_add_vendors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('jenis');
        });
        DB::table('invoice_add_vendors')->insert([['id' => 30, 'vendor_id' => 1, 'jenis' => 'achievement'], ['id' => 31, 'vendor_id' => 2, 'jenis' => 'achievement']]);
        foreach (['ban_logs', 'aki_logs'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('vehicle_id');
                $table->timestamps();
            });
        }
        Schema::create('password_konfirmasis', function (Blueprint $table): void {
            $table->id();
            $table->string('password');
        });
        DB::table('password_konfirmasis')->insert(['password' => 'admin-confirm']);
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('vehicles')->insert([['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101'], ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => '202']]);
        foreach (['ban_logs', 'aki_logs'] as $name) {
            DB::table($name)->insert([['id' => 1, 'vehicle_id' => 1, 'created_at' => '2026-10-01'], ['id' => 2, 'vehicle_id' => 2, 'created_at' => '2026-10-01'], ['id' => 3, 'vehicle_id' => 1, 'created_at' => '2026-10-02']]);
        }
        DB::table('kas_vendors')->insert([
            ['vendor_id' => 1, 'vehicle_id' => null, 'tanggal' => '2026-09-20', 'uraian' => 'Saldo lama', 'pinjaman' => 500000, 'bayar' => 0, 'sisa' => 500000, 'invoice_bayar_id' => null, 'invoice_add_vendor_id' => null],
            ['vendor_id' => 1, 'vehicle_id' => 1, 'tanggal' => '2026-11-03', 'uraian' => 'Pinjaman ban', 'pinjaman' => 200000, 'bayar' => 0, 'sisa' => 700000, 'invoice_bayar_id' => null, 'invoice_add_vendor_id' => 30],
            ['vendor_id' => 1, 'vehicle_id' => null, 'tanggal' => '2026-11-10', 'uraian' => 'Potong invoice', 'pinjaman' => 0, 'bayar' => 300000, 'sisa' => 400000, 'invoice_bayar_id' => 975, 'invoice_add_vendor_id' => null],
            ['vendor_id' => 2, 'vehicle_id' => 2, 'tanggal' => '2026-11-05', 'uraian' => 'Vendor lain', 'pinjaman' => 999999, 'bayar' => 0, 'sisa' => 999999, 'invoice_bayar_id' => null, 'invoice_add_vendor_id' => 31],
        ]);
    }

    private function headers(string $role = 'vendor', int $vendor = 1): array
    {
        $user = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => $vendor]);
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => (string) $vendor,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    public function test_vendor_can_log_in_and_vendor_without_assignment_cannot(): void
    {
        User::factory()->create(['username' => 'vendor-login', 'password' => 'secret-pass', 'role' => 'vendor', 'vendor_id' => 1]);
        $this->postJson('/api/v1/login', ['username' => 'vendor-login', 'password' => 'secret-pass', 'device_name' => 'Android'], ['X-Workspace-ID' => self::WORKSPACE])
            ->assertOk()->assertJsonPath('data.user.role', 'vendor')->assertJsonPath('data.user.permissions.vendor_finance', true);
        User::factory()->create(['username' => 'vendor-loose', 'password' => 'secret-pass', 'role' => 'vendor', 'vendor_id' => null]);
        $this->postJson('/api/v1/login', ['username' => 'vendor-loose', 'password' => 'secret-pass', 'device_name' => 'Android'], ['X-Workspace-ID' => self::WORKSPACE])
            ->assertForbidden();
    }

    public function test_vendor_cash_lists_own_month_with_opening_and_closing_balance(): void
    {
        $this->getJson('/api/v1/vendor-cash?month=11&year=2026', $this->headers())->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('data.0.description', 'Pinjaman ban')->assertJsonPath('data.0.vehicle_number', '101')
            ->assertJsonPath('meta.opening_balance', 500000)->assertJsonPath('meta.total_loan', 200000)
            ->assertJsonPath('meta.total_payment', 300000)->assertJsonPath('meta.closing_balance', 400000)
            ->assertJsonMissing(['description' => 'Vendor lain'])
            ->assertJsonPath('data.0.invoice', ['kind' => 'achievement', 'id' => 30])
            ->assertJsonPath('data.1.invoice', ['kind' => 'utama', 'id' => 975]);
        $this->getJson('/api/v1/vendor-cash?month=12&year=2026', $this->headers())->assertOk()->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.opening_balance', 400000)->assertJsonPath('meta.closing_balance', 400000);
        $this->getJson('/api/v1/vendor-cash?month=13&year=2026', $this->headers())->assertUnprocessable();
        $this->getJson('/api/v1/vendor-cash', $this->headers('vendor-operational'))->assertForbidden();
    }

    /** @return array<string, array{string, string}> */
    public static function histories(): array
    {
        return ['ban' => ['tire-logs', 'ban_logs'], 'aki' => ['battery-logs', 'aki_logs']];
    }

    #[DataProvider('histories')]
    public function test_vendor_edits_and_deletes_own_history_with_own_password(string $path, string $table): void
    {
        $headers = $this->headers();
        $this->patchJson('/api/v1/vehicles/1/'.$path.'/3', ['replaced_on' => '2026-10-05', 'password' => 'wrong'], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors(['password' => 'Password salah.']);
        $this->patchJson('/api/v1/vehicles/1/'.$path.'/3', ['replaced_on' => '2999-01-01', 'password' => 'secret-pass'], $headers)
            ->assertUnprocessable()->assertJsonValidationErrors('replaced_on');
        $this->patchJson('/api/v1/vehicles/1/'.$path.'/3', ['replaced_on' => '2026-10-05', 'password' => 'secret-pass'], $headers)->assertOk();
        $this->assertSame('2026-10-05', substr((string) DB::table($table)->where('id', 3)->value('created_at'), 0, 10));
        $this->deleteJson('/api/v1/vehicles/1/'.$path.'/1', ['password' => 'admin-confirm'], $headers)->assertUnprocessable();
        $this->deleteJson('/api/v1/vehicles/1/'.$path.'/1', ['password' => 'secret-pass'], $headers)->assertOk();
        $this->assertDatabaseMissing($table, ['id' => 1]);
        // Kendaraan vendor lain, atau log kendaraan lain lewat kendaraan sendiri: tidak ditemukan.
        $this->deleteJson('/api/v1/vehicles/2/'.$path.'/2', ['password' => 'secret-pass'], $headers)->assertNotFound();
        $this->deleteJson('/api/v1/vehicles/1/'.$path.'/2', ['password' => 'secret-pass'], $headers)->assertNotFound();
        $this->assertDatabaseHas($table, ['id' => 2]);
    }

    #[DataProvider('histories')]
    public function test_vendor_operational_cannot_change_history(string $path, string $table): void
    {
        $this->deleteJson('/api/v1/vehicles/1/'.$path.'/1', ['password' => 'secret-pass'], $this->headers('vendor-operational'))->assertForbidden();
        $this->assertDatabaseHas($table, ['id' => 1]);
    }
}
