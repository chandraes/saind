<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class MobileApiFoundationTest extends TestCase
{
    private const WORKSPACE = 'b1bca759-224f-4f48-bbe7-76904c3434fa';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'mobile.workspace_id' => self::WORKSPACE, 'mobile.workspace_name' => 'SAIND Hauling',
            'mobile.company_name' => 'DS Groups', 'mobile.token_expiration_minutes' => 60]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('role');
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('nomor_lambung');
            $table->string('status')->default('aktif');
            $table->string('no_rekening')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2019_12_14_000001_create_personal_access_tokens_table.php'))->up();
    }

    private function user(?int $vendorId = 1, string $role = 'vendor-operational'): User
    {
        if ($vendorId !== null) {
            DB::table('vendors')->insertOrIgnore(['id' => $vendorId, 'nama' => 'Vendor '.$vendorId]);
        }

        return User::factory()->create(['username' => 'budi', 'password' => 'secret-pass', 'role' => $role,
            'vendor_id' => $vendorId]);
    }

    private function login(): string
    {
        return $this->postJson('/api/v1/login', ['username' => 'budi', 'password' => 'secret-pass',
            'device_name' => 'iPhone Budi'], ['X-Workspace-ID' => self::WORKSPACE])
            ->assertOk()->json('data.token');
    }

    public function test_public_metadata_identifies_the_workspace_and_mobile_product(): void
    {
        $this->getJson('/api/v1/workspace')->assertOk()
            ->assertJsonPath('data.id', self::WORKSPACE)
            ->assertJsonPath('data.product_name', 'DSGroup Hauling')
            ->assertJsonPath('data.name', 'SAIND Hauling')
            ->assertJsonPath('data.company_name', 'DS Groups');
    }

    public function test_unconfigured_workspace_fails_closed(): void
    {
        config(['mobile.workspace_id' => null]);
        $this->getJson('/api/v1/workspace')->assertStatus(503);
    }

    public function test_setup_persists_identity_without_rotating_it_on_rerun(): void
    {
        config(['mobile.workspace_id' => null, 'mobile.workspace_name' => null, 'mobile.company_name' => null]);
        $this->artisan('mobile:configure-workspace', ['--name' => 'Hauling', '--company' => 'DS Groups'])->assertSuccessful();
        $id = $this->getJson('/api/v1/workspace')->assertOk()->json('data.id');
        $this->assertTrue(Str::isUuid($id));
        $this->artisan('mobile:configure-workspace', ['--name' => 'Hauling Baru', '--company' => 'DS Groups'])->assertSuccessful();
        $this->getJson('/api/v1/workspace')->assertJsonPath('data.id', $id)->assertJsonPath('data.name', 'Hauling Baru');
    }

    public function test_login_issues_an_expiring_hashed_token_and_safe_user_data(): void
    {
        $this->freezeTime();
        $user = $this->user();
        $token = $this->login();
        $stored = PersonalAccessToken::findToken($token);
        $this->assertNotNull($stored);
        $this->assertNotSame($token, $stored->token);
        $this->assertSame(now()->addHour()->timestamp, $stored->expires_at->timestamp);
        $this->assertContains('workspace:'.self::WORKSPACE, $stored->abilities);
        $this->app['auth']->forgetGuards();
        $response = $this->withToken($token)->withHeader('X-Workspace-ID', self::WORKSPACE)
            ->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.id', $user->id);
        $this->assertArrayNotHasKey('password', $response->json('data'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data'));
    }

    public function test_wrong_credentials_are_unauthorized_without_creating_tokens(): void
    {
        $this->user();
        $this->postJson('/api/v1/login', ['username' => 'budi', 'password' => 'wrong', 'device_name' => 'Android'],
            ['X-Workspace-ID' => self::WORKSPACE])->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_requires_valid_input_even_without_accept_header(): void
    {
        $this->post('/api/v1/login', [], ['X-Workspace-ID' => self::WORKSPACE])
            ->assertUnprocessable()->assertJsonValidationErrors(['username', 'password', 'device_name']);
    }

    public function test_login_requires_the_selected_workspace_and_rejects_mismatch(): void
    {
        $this->user();
        $payload = ['username' => 'budi', 'password' => 'secret-pass', 'device_name' => 'Android'];
        $this->postJson('/api/v1/login', $payload)->assertStatus(409);
        $this->postJson('/api/v1/login', $payload, ['X-Workspace-ID' => 'different'])->assertStatus(409);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/login', ['username' => 'missing', 'password' => 'wrong', 'device_name' => 'Android'],
                ['X-Workspace-ID' => self::WORKSPACE])->assertUnauthorized();
        }
        $this->postJson('/api/v1/login', ['username' => 'missing', 'password' => 'wrong', 'device_name' => 'Android'],
            ['X-Workspace-ID' => self::WORKSPACE])->assertStatus(429);
    }

    public function test_guest_and_invalid_tokens_cannot_read_account(): void
    {
        $this->withHeader('X-Workspace-ID', self::WORKSPACE)->getJson('/api/v1/me')->assertUnauthorized();
        $this->withToken('not-valid')->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_expired_and_other_workspace_tokens_are_rejected(): void
    {
        $this->user();
        $token = $this->login();
        PersonalAccessToken::findToken($token)->update(['expires_at' => now()->subMinute()]);
        $this->withToken($token)->withHeader('X-Workspace-ID', self::WORKSPACE)->getJson('/api/v1/me')->assertUnauthorized();
        PersonalAccessToken::findToken($token)->update(['expires_at' => now()->addHour(), 'abilities' => ['workspace:other']]);
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_logout_revokes_only_the_current_device_token(): void
    {
        $this->user();
        $first = $this->login();
        $second = $this->login();
        $this->withToken($first)->withHeader('X-Workspace-ID', self::WORKSPACE)->postJson('/api/v1/logout')->assertOk();
        $this->assertNull(PersonalAccessToken::findToken($first));
        $this->assertNotNull(PersonalAccessToken::findToken($second));
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_vendor_without_assignment_cannot_login(): void
    {
        $this->user(null);
        $this->postJson('/api/v1/login', ['username' => 'budi', 'password' => 'secret-pass', 'device_name' => 'Android'],
            ['X-Workspace-ID' => self::WORKSPACE])->assertForbidden();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_vehicle_list_and_detail_are_limited_to_vendor_and_hide_bank_data(): void
    {
        $this->user();
        DB::table('vehicles')->insert([
            ['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => 'SAI169', 'no_rekening' => 'private-account'],
            ['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => 'OTHER', 'no_rekening' => 'private-account'],
        ]);
        $token = $this->login();
        $this->withToken($token)->withHeader('X-Workspace-ID', self::WORKSPACE)
            ->getJson('/api/v1/vehicles')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 1);
        $response = $this->getJson('/api/v1/vehicles/1')->assertOk();
        $this->assertArrayNotHasKey('no_rekening', $response->json('data'));
        $this->getJson('/api/v1/vehicles/2')->assertNotFound();
    }

    public function test_employee_can_read_other_vendors_but_vendor_role_is_denied(): void
    {
        $user = $this->user(null, 'admin');
        DB::table('vehicles')->insert(['id' => 2, 'vendor_id' => 2, 'nomor_lambung' => 'OTHER']);
        $token = $this->login();
        $this->withToken($token)->withHeader('X-Workspace-ID', self::WORKSPACE)
            ->getJson('/api/v1/vehicles/2')->assertOk()->assertJsonPath('data.id', 2);
        $user->update(['role' => 'customer']);
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/vehicles/2')->assertForbidden();
    }

    public function test_web_session_alone_cannot_access_mobile_api(): void
    {
        $this->actingAs($this->user());
        $this->withHeader('X-Workspace-ID', self::WORKSPACE)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_missing_token_returns_json_without_accept_header(): void
    {
        $this->get('/api/v1/me', ['X-Workspace-ID' => self::WORKSPACE])->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_vehicle_pagination_is_bounded(): void
    {
        $this->user();
        $this->withToken($this->login())->withHeader('X-Workspace-ID', self::WORKSPACE)
            ->getJson('/api/v1/vehicles?per_page=10000')->assertUnprocessable()->assertJsonValidationErrors('per_page');
    }

    public function test_workspace_change_rejects_old_token_even_with_new_header(): void
    {
        $this->user();
        $token = $this->login();
        $other = 'a8f49c35-47a5-44be-a120-596f707be692';
        config(['mobile.workspace_id' => $other]);
        $this->withToken($token)->withHeader('X-Workspace-ID', $other)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_removed_vendor_assignment_invalidates_existing_access(): void
    {
        $user = $this->user();
        $token = $this->login();
        $user->update(['vendor_id' => null]);
        $this->withToken($token)->withHeader('X-Workspace-ID', self::WORKSPACE)->getJson('/api/v1/vehicles')->assertForbidden();
    }
}
