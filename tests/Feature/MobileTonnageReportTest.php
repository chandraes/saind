<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;

class MobileTonnageReportTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('singkatan')->nullable();
            $table->integer('status')->default(1);
        });
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('kas_uang_jalans', function (Blueprint $table): void {
            $table->id();
            $table->integer('customer_id');
            $table->integer('vehicle_id');
            $table->integer('rute_id');
            $table->date('tanggal');
        });
        Schema::create('transaksis', function (Blueprint $table): void {
            $table->id();
            $table->integer('kas_uang_jalan_id');
            $table->boolean('void');
            $table->date('tanggal_muat');
            $table->date('tanggal_bongkar')->nullable();
            $table->decimal('tonase');
            $table->decimal('timbangan_bongkar')->nullable();
        });
        DB::table('customers')->insert([
            ['id' => 1, 'nama' => 'Tambang A', 'status' => 1],
            ['id' => 2, 'nama' => 'Tambang Lama', 'status' => 0],
        ]);
        DB::table('vehicles')->insert(['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '123']);
        DB::table('rutes')->insert([
            ['id' => 1, 'nama' => 'Rute A'],
            ['id' => 2, 'nama' => 'Rute B'],
        ]);
        DB::table('kas_uang_jalans')->insert([
            ['id' => 1, 'customer_id' => 1, 'rute_id' => 1, 'vehicle_id' => 1, 'tanggal' => '2028-02-01'],
            ['id' => 2, 'customer_id' => 1, 'rute_id' => 2, 'vehicle_id' => 1, 'tanggal' => '2028-02-01'],
            ['id' => 3, 'customer_id' => 2, 'rute_id' => 1, 'vehicle_id' => 1, 'tanggal' => '2028-02-01'],
        ]);
        foreach ([
            [1, false, 30.5, null],
            [2, false, 50, 29.25],
            [1, true, 50, null],
            [3, false, 99, null],
        ] as [$trip, $void, $load, $unload]) {
            DB::table('transaksis')->insert([
                'kas_uang_jalan_id' => $trip, 'void' => $void,
                'tanggal_muat' => '2028-02-01', 'tanggal_bongkar' => $unload === null ? null : '2028-02-02',
                'tonase' => $load, 'timbangan_bongkar' => $unload,
            ]);
        }
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    #[TestWith(['operasional'])]
    #[TestWith(['general-manager'])]
    #[TestWith(['direksi'])]
    public function test_report_roles_can_read_monthly_tonnage(string $role): void
    {
        $headers = $this->headers($role);
        $this->getJson('/api/v1/tonnage-report/customers', $headers)
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);
        $this->getJson('/api/v1/tonnage-report/customers/1?month=2&year=2028', $headers)
            ->assertOk()->assertJsonCount(29, 'data')->assertJsonCount(2, 'meta.routes')
            ->assertJsonPath('data.0', ['day' => 1, 'trips' => 1, 'loaded_tons' => 30.5, 'unloaded_tons' => 0])
            ->assertJsonPath('data.1', ['day' => 2, 'trips' => 1, 'loaded_tons' => 0, 'unloaded_tons' => 29.25])
            ->assertJsonPath('meta.totals', ['trips' => 2, 'loaded_tons' => 30.5, 'unloaded_tons' => 29.25]);
        $this->getJson('/api/v1/tonnage-report/customers/1?month=2&year=2028&route_id=2', $headers)
            ->assertOk()->assertJsonPath('meta.totals', ['trips' => 1, 'loaded_tons' => 0, 'unloaded_tons' => 29.25]);
    }

    public function test_api_rejects_other_roles_invalid_filters_and_inactive_customers(): void
    {
        $this->getJson('/api/v1/tonnage-report/customers', $this->headers('hrd'))->assertForbidden();
        $headers = $this->headers('operasional');
        $this->getJson('/api/v1/tonnage-report/customers/1?month=13&year=2028', $headers)->assertUnprocessable();
        $this->getJson('/api/v1/tonnage-report/customers/1?month=2&year=2028&route_id=999', $headers)->assertUnprocessable();
        $this->getJson('/api/v1/tonnage-report/customers/2?month=2&year=2028', $headers)->assertNotFound();
    }

    private function headers(string $role): array
    {
        $user = User::factory()->create(['username' => Str::uuid()->toString(), 'role' => $role, 'password' => 'test-password']);
        $this->app['auth']->forgetGuards();

        return ['X-Workspace-ID' => self::WORKSPACE,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }
}
