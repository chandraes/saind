<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('role')->default('user');
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
        });
        foreach (['vendors', 'customers'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama');
                $table->string('status');
            });
        }
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    private function user(string $role = 'admin', array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => $role, 'username' => fake()->unique()->userName(), 'password' => 'secret123'], $attributes));
    }

    private function payload(array $attributes = []): array
    {
        return array_merge(['name' => 'Pengguna Baru', 'username' => 'pengguna-baru', 'email' => null, 'password' => 'secret123', 'role' => 'user'], $attributes);
    }

    private function listing(array $parameters = []): TestResponse
    {
        return $this->getJson(route('pengguna.index', array_replace_recursive(['draw' => 1, 'start' => 0, 'length' => 10], $parameters)), ['X-Requested-With' => 'XMLHttpRequest']);
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    public function test_managers_can_load_page_without_loading_user_records(string $role): void
    {
        $this->actingAs($this->user($role));
        $this->user('user', ['name' => 'Nama Tersimpan Rahasia']);
        $response = $this->get(route('pengguna.index'))->assertOk()->assertSee('Manajemen Pengguna')->assertDontSee('Nama Tersimpan Rahasia');
        $this->assertSame(1, substr_count($response->getContent(), 'id="user-modal"'));
    }

    public function test_server_pagination_excludes_superusers_and_limits_loaded_rows(): void
    {
        $this->actingAs($this->user());
        $this->user('su');
        for ($i = 0; $i < 112; $i++) {
            $this->user('user', ['username' => sprintf('account-%03d', $i)]);
        }
        $this->listing(['length' => 10, 'start' => 10, 'order' => [['column' => 1, 'dir' => 'asc']]])
            ->assertOk()->assertJsonPath('recordsTotal', 113)->assertJsonCount(10, 'data')->assertJsonPath('data.0.username', 'account-010')->assertJsonPath('data.0.DT_RowIndex', 11);
        $this->listing(['length' => 100000])->assertOk()->assertJsonCount(100, 'data');
        $this->listing(['length' => -1])->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_search_filters_role_and_relation_without_loading_passwords(): void
    {
        $this->actingAs($this->user());
        $vendor = DB::table('vendors')->insertGetId(['nama' => 'Vendor Istimewa', 'status' => 'aktif']);
        $target = $this->user('vendor', ['vendor_id' => $vendor, 'name' => '<img src=x onerror=alert(1)>', 'username' => 'vendor-special']);
        $this->user('user');
        $response = $this->listing(['search' => ['value' => 'Istimewa'], 'role_filter' => 'vendor'])->assertOk()
            ->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', $target->id)->assertJsonPath('data.0.vendor_name', 'Vendor Istimewa');
        $this->assertArrayNotHasKey('password', $response->json('data.0'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data.0'));
        $this->listing(['search' => ['value' => 'vendor-special'], 'role_filter' => 'user'])->assertOk()->assertJsonPath('recordsFiltered', 0);
        $this->listing(['search' => ['value' => "' OR 1=1 --"]])->assertOk()->assertJsonPath('recordsFiltered', 0);
    }

    public function test_table_escapes_action_attributes_and_does_not_query_per_row(): void
    {
        $this->actingAs($this->user());
        $this->user('user', ['username' => '\"><script>alert(1)</script>']);
        for ($i = 0; $i < 20; $i++) {
            $this->user('user');
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->listing(['length' => 25])->assertOk()->assertJsonCount(22, 'data');
        $this->assertLessThanOrEqual(3, count(DB::getQueryLog()));
        DB::disableQueryLog();
        foreach ($response->json('data') as $row) {
            $this->assertStringNotContainsString('<script>', $row['action']);
        }
    }

    public function test_edit_details_include_email_and_relations_but_no_secrets(): void
    {
        $this->actingAs($this->user());
        $customer = DB::table('customers')->insertGetId(['nama' => 'Customer Lama', 'status' => '0']);
        $target = $this->user('customer-admin', ['customer_id' => $customer]);
        $response = $this->getJson(route('pengguna.show', $target))->assertOk()->assertJsonPath('data.email', $target->email)->assertJsonPath('data.customer.nama', 'Customer Lama');
        $this->assertArrayNotHasKey('password', $response->json('data'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data'));
    }

    public function test_creation_hashes_password_and_ignores_irrelevant_relations(): void
    {
        $this->actingAs($this->user());
        $this->postJson(route('pengguna.store'), $this->payload(['vendor_id' => 999, 'customer_id' => 999]))->assertOk()->assertJsonPath('message', 'Pengguna berhasil ditambahkan.');
        $user = User::where('username', 'pengguna-baru')->firstOrFail();
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertNull($user->vendor_id);
        $this->assertNull($user->customer_id);
    }

    public function test_edit_updates_customer_and_clears_old_vendor_while_preserving_password(): void
    {
        $this->actingAs($this->user());
        $vendor = DB::table('vendors')->insertGetId(['nama' => 'Vendor', 'status' => 'aktif']);
        $customer = DB::table('customers')->insertGetId(['nama' => 'Customer', 'status' => '1']);
        $target = $this->user('vendor-operational', ['vendor_id' => $vendor]);
        $oldPassword = $target->password;
        $this->putJson(route('pengguna.update', $target), $this->payload(['role' => 'customer-admin', 'customer_id' => $customer, 'password' => '']))->assertOk();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'customer-admin', 'customer_id' => $customer, 'vendor_id' => null, 'password' => $oldPassword]);
        $this->putJson(route('pengguna.update', $target), $this->payload(['password' => 'new-secret']))->assertOk();
        $this->assertTrue(Hash::check('new-secret', $target->fresh()->password));
        $this->assertNull($target->fresh()->customer_id);
    }

    #[TestWith(['vendor', 'vendor_id'])]
    #[TestWith(['vendor-operational', 'vendor_id'])]
    #[TestWith(['customer', 'customer_id'])]
    #[TestWith(['customer-admin', 'customer_id'])]
    public function test_related_roles_require_an_existing_relation(string $role, string $field): void
    {
        $this->actingAs($this->user());
        $this->postJson(route('pengguna.store'), $this->payload(['role' => $role]))->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->postJson(route('pengguna.store'), $this->payload(['role' => $role, $field => 999]))->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseMissing('users', ['username' => 'pengguna-baru']);
    }

    public function test_duplicate_fields_invalid_email_and_short_password_are_rejected(): void
    {
        $actor = $this->user();
        $this->actingAs($actor);
        $this->postJson(route('pengguna.store'), $this->payload(['username' => $actor->username, 'email' => $actor->email, 'password' => '123']))->assertUnprocessable()->assertJsonValidationErrors(['username', 'email', 'password']);
        $this->postJson(route('pengguna.store'), $this->payload(['email' => 'invalid']))->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->putJson(route('pengguna.update', $actor), $this->payload(['username' => $actor->username, 'email' => $actor->email, 'role' => 'admin', 'password' => '']))->assertOk();
    }

    public function test_last_admin_protection_does_not_block_deleting_other_users(): void
    {
        $this->actingAs($this->user('su'));
        $admin = $this->user();
        $other = $this->user('user');
        $this->deleteJson(route('pengguna.destroy', $other))->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $other->id]);
        $this->deleteJson(route('pengguna.destroy', $admin))->assertUnprocessable()->assertJsonValidationErrors('user');
        $this->putJson(route('pengguna.update', $admin), $this->payload())->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
        $this->user();
        $this->deleteJson(route('pengguna.destroy', $admin))->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_self_delete_and_modifying_superusers_are_blocked(): void
    {
        $actor = $this->user();
        $this->actingAs($actor);
        $superuser = $this->user('su');
        $this->deleteJson(route('pengguna.destroy', $actor))->assertUnprocessable();
        $this->getJson(route('pengguna.show', $superuser))->assertNotFound();
        $this->putJson(route('pengguna.update', $superuser), $this->payload())->assertNotFound();
        $this->deleteJson(route('pengguna.destroy', $superuser))->assertNotFound();
        $this->postJson(route('pengguna.store'), $this->payload(['role' => 'su']))->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->assertDatabaseHas('users', ['id' => $superuser->id, 'role' => 'su']);
    }

    #[TestWith(['vendor', 'vendors', 'aktif', 'nonaktif'])]
    #[TestWith(['customer', 'customers', '1', '0'])]
    public function test_select_options_are_searched_paginated_and_only_active(string $type, string $table, string $active, string $inactive): void
    {
        $this->actingAs($this->user());
        for ($i = 0; $i < 25; $i++) {
            DB::table($table)->insert(['nama' => sprintf('Mitra %02d', $i), 'status' => $active]);
        }
        DB::table($table)->insert(['nama' => 'Mitra Nonaktif', 'status' => $inactive]);
        $this->getJson(route('pengguna.options', ['type' => $type]))->assertOk()->assertJsonCount(20, 'results')->assertJsonPath('pagination.more', true);
        $this->getJson(route('pengguna.options', ['type' => $type, 'page' => 2]))->assertOk()->assertJsonCount(5, 'results')->assertJsonPath('pagination.more', false);
        $this->getJson(route('pengguna.options', ['type' => $type, 'q' => 'Mitra 03']))->assertOk()->assertJsonCount(1, 'results')->assertJsonPath('results.0.text', 'Mitra 03');
    }

    public function test_guest_and_unprivileged_users_cannot_access_management_endpoints(): void
    {
        $this->get(route('pengguna.index'))->assertRedirect(route('login'));
        $this->actingAs($this->user('operasional'));
        $this->getJson(route('pengguna.index'))->assertForbidden();
        $this->getJson(route('pengguna.options', ['type' => 'vendor']))->assertForbidden();
        $this->getJson(route('pengguna.show', 999))->assertForbidden();
        $this->postJson(route('pengguna.store'), $this->payload())->assertForbidden();
        $this->putJson(route('pengguna.update', 999), $this->payload())->assertForbidden();
        $this->deleteJson(route('pengguna.destroy', 999))->assertForbidden();
    }
}
