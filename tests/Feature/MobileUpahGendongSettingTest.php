<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Database Upah Gendong: vendor (mobile) dan admin (web) memakai aturan yang sama. */
class MobileUpahGendongSettingTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('upah_gendongs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->integer('nominal');
            $table->float('tonase_min')->default(0);
            $table->string('nama_driver');
            $table->date('tanggal_masuk_driver')->nullable();
            $table->string('nama_pengurus');
            $table->date('tanggal_masuk_pengurus')->nullable();
            $table->string('no_rek');
            $table->string('bank');
            $table->string('nama_rek');
            $table->timestamps();
        });
        DB::table('vendors')->insert([['id' => 1, 'nama' => 'Vendor A'], ['id' => 2, 'nama' => 'Vendor B']]);
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '101', 'status' => 'aktif'],
            ['id' => 2, 'vendor_id' => 1, 'nomor_lambung' => '102', 'status' => 'aktif'],
            ['id' => 3, 'vendor_id' => 1, 'nomor_lambung' => '103', 'status' => 'nonaktif'],
            ['id' => 4, 'vendor_id' => 2, 'nomor_lambung' => '201', 'status' => 'aktif'],
        ]);
        DB::table('upah_gendongs')->insert(['vehicle_id' => 2, 'nominal' => 20000, 'tonase_min' => 30, 'nama_driver' => 'BUDI',
            'tanggal_masuk_driver' => '2025-01-02', 'nama_pengurus' => 'ARI', 'tanggal_masuk_pengurus' => '2025-02-03',
            'no_rek' => '0001', 'bank' => 'BRI', 'nama_rek' => 'BUDI']);
    }

    private function headers(string $role = 'vendor', int $vendor = 1): array
    {
        $user = User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => $vendor]);
        $this->app['auth']->forgetGuards();

        return ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => (string) $vendor,
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_replace(['nominal' => '25.000', 'tonase_min' => 31, 'nama_driver' => ' Sari ', 'tanggal_masuk_driver' => '2026-01-05',
            'nama_pengurus' => 'Tim B', 'tanggal_masuk_pengurus' => '2026-01-06', 'bank' => 'BCA', 'nama_rek' => 'Sari', 'no_rek' => '123'], $overrides);
    }

    public function test_vendor_lists_own_active_vehicles_with_their_setting(): void
    {
        $this->getJson('/api/v1/upah-gendong-settings', $this->headers())->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('data.0.nomor_lambung', '101')->assertJsonPath('data.0.setting', null)
            ->assertJsonPath('data.1.setting.nama_driver', 'BUDI')->assertJsonPath('data.1.setting.nominal', 20000)
            ->assertJsonMissing(['nomor_lambung' => '103'])->assertJsonMissing(['nomor_lambung' => '201']);
        $this->getJson('/api/v1/me', $this->headers())->assertJsonPath('data.permissions.upah_gendong_settings', true);
    }

    public function test_vendor_activates_updates_and_deletes_own_vehicle(): void
    {
        $headers = $this->headers();
        $this->postJson('/api/v1/vehicles/1/upah-gendong-setting', $this->payload(), $headers)->assertCreated()
            ->assertJsonPath('data.nominal', 25000)->assertJsonPath('data.nama_driver', 'Sari');
        $this->assertDatabaseHas('upah_gendongs', ['vehicle_id' => 1, 'nominal' => 25000, 'tanggal_masuk_driver' => '2026-01-05']);
        // Sudah aktif: tidak boleh dobel.
        $this->postJson('/api/v1/vehicles/1/upah-gendong-setting', $this->payload(), $headers)->assertUnprocessable()->assertJsonValidationErrors('vehicle_id');
        $this->patchJson('/api/v1/vehicles/1/upah-gendong-setting', $this->payload(['nominal' => 30000, 'nama_driver' => 'Rudi']), $headers)
            ->assertOk()->assertJsonPath('data.nominal', 30000)->assertJsonPath('data.nama_driver', 'Rudi');
        $this->patchJson('/api/v1/vehicles/1/upah-gendong-setting', $this->payload(['nominal' => 'abc', 'tanggal_masuk_driver' => '2999-01-01']), $headers)
            ->assertUnprocessable()->assertJsonValidationErrors(['nominal', 'tanggal_masuk_driver']);
        $this->deleteJson('/api/v1/vehicles/1/upah-gendong-setting', ['password' => 'wrong'], $headers)->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->deleteJson('/api/v1/vehicles/1/upah-gendong-setting', ['password' => 'secret-pass'], $headers)->assertOk();
        $this->assertDatabaseMissing('upah_gendongs', ['vehicle_id' => 1]);
    }

    public function test_other_vendor_inactive_vehicle_and_vendor_operational_are_refused(): void
    {
        $headers = $this->headers();
        $this->postJson('/api/v1/vehicles/4/upah-gendong-setting', $this->payload(), $headers)->assertNotFound();
        $this->postJson('/api/v1/vehicles/3/upah-gendong-setting', $this->payload(), $headers)->assertNotFound();
        $this->patchJson('/api/v1/vehicles/1/upah-gendong-setting', $this->payload(), $headers)->assertNotFound();
        $ops = $this->headers('vendor-operational');
        $this->getJson('/api/v1/upah-gendong-settings', $ops)->assertForbidden();
        $this->postJson('/api/v1/vehicles/1/upah-gendong-setting', $this->payload(), $ops)->assertForbidden();
        $this->getJson('/api/v1/me', $ops)->assertJsonPath('data.permissions.upah_gendong_settings', false);
        $this->assertDatabaseMissing('upah_gendongs', ['vehicle_id' => 1]);
    }

    public function test_admin_database_page_uses_the_same_rules(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create(['username' => 'admin-ug', 'password' => 'secret-pass', 'role' => 'admin']));
        $form = $this->payload(['vehicle_id' => 1, 'nominal' => '22.500', 'tanggal_masuk_driver' => '05-01-2026', 'tanggal_masuk_pengurus' => '06-01-2026']);
        $this->post('/database/upah-gendong/store', $form)->assertRedirect(route('database.upah-gendong'));
        $this->assertDatabaseHas('upah_gendongs', ['vehicle_id' => 1, 'nominal' => 22500, 'tanggal_masuk_driver' => '2026-01-05']);
        $this->post('/database/upah-gendong/store', $form)->assertSessionHasErrors('vehicle_id');
    }
}
