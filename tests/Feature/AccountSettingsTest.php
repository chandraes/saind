<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;

/** Pengaturan akun web (/akun): ganti username/password akun sendiri, untuk semua role. */
class AccountSettingsTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        DB::table('vendors')->insert(['id' => 1, 'nama' => 'Vendor 1']);
        // Dashboard eksekutif memuat pilihan customer (Nota Tagihan).
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('singkatan')->nullable();
            $table->integer('status')->default(1);
        });
    }

    private function user(string $role = 'vendor', string $username = 'budi'): User
    {
        return User::factory()->create(['username' => $username, 'password' => 'secret-pass', 'role' => $role,
            'vendor_id' => in_array($role, User::VENDOR_ACCOUNT_ROLES, true) ? 1 : null]);
    }

    private function form(array $overrides = []): array
    {
        return [...['username' => 'budi', 'current_password' => 'secret-pass'], ...$overrides];
    }

    #[TestWith(['vendor'])]
    #[TestWith(['vendor-operational'])]
    #[TestWith(['operasional'])]
    #[TestWith(['general-manager'])]
    #[TestWith(['direksi'])]
    #[TestWith(['hrd'])]
    #[TestWith(['admin'])]
    #[TestWith(['user'])]
    public function test_every_role_can_open_the_page_and_change_its_username(string $role): void
    {
        $user = $this->user($role);
        $this->actingAs($user);

        $this->get('/home')->assertOk()->assertSee(route('account.edit'), false);
        $this->get('/akun')->assertOk()->assertSee('Pengaturan akun')->assertSee('value="budi"', false);
        $this->put('/akun', $this->form(['username' => 'budi-'.$role]))
            ->assertRedirect('/akun')->assertSessionHas('success', 'Username diperbarui.');

        $this->assertSame('budi-'.$role, $user->fresh()->username);
        $this->assertTrue(Hash::check('secret-pass', $user->fresh()->password));
    }

    public function test_card_and_tile_dashboards_link_the_account_page(): void
    {
        $this->actingAs($this->user('vendor'));
        $this->get('/home')->assertOk()->assertSee('Pengaturan Akun')->assertSee('images/akun.svg', false);

        $this->actingAs($this->user('admin', 'admin'));
        $this->get('/home')->assertOk()->assertSee('<h4 class="mt-3">AKUN</h4>', false);
    }

    public function test_wrong_current_password_or_taken_username_changes_nothing(): void
    {
        $user = $this->user();
        $this->user('user', 'occupied');
        $this->actingAs($user);

        $this->from('/akun')->put('/akun', $this->form(['username' => 'baru', 'current_password' => 'wrong']))
            ->assertRedirect('/akun')->assertSessionHasErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        $this->from('/akun')->put('/akun', $this->form(['username' => 'occupied']))
            ->assertSessionHasErrors(['username' => 'Username sudah digunakan.']);
        $this->from('/akun')->put('/akun', $this->form(['password' => 'pendek', 'password_confirmation' => 'pendek']))
            ->assertSessionHasErrors(['password' => 'Password baru minimal 8 karakter.']);
        $this->from('/akun')->put('/akun', $this->form(['password' => 'new-secret-pass', 'password_confirmation' => 'beda-sekali']))
            ->assertSessionHasErrors(['password' => 'Konfirmasi password baru tidak cocok.']);
        $this->from('/akun')->put('/akun', ['username' => '', 'current_password' => ''])
            ->assertSessionHasErrors(['username' => 'Isi username.', 'current_password' => 'Isi password saat ini.']);

        $this->assertSame('budi', $user->fresh()->username);
        $this->assertTrue(Hash::check('secret-pass', $user->fresh()->password));
    }

    public function test_password_change_signs_out_other_browsers_and_every_mobile_login_but_keeps_this_browser(): void
    {
        $user = $this->user('general-manager');
        $user->createToken('iPhone');
        $user->createToken('Android');
        $this->actingAs($user);
        $this->get('/akun')->assertOk();
        $otherBrowser = session()->all();

        $this->put('/akun', $this->form(['password' => 'new-secret-pass', 'password_confirmation' => 'new-secret-pass', 'role' => 'su']))
            ->assertRedirect('/akun')->assertSessionHas('success');

        $fresh = $user->fresh();
        $this->assertTrue(Hash::check('new-secret-pass', $fresh->password));
        $this->assertSame('general-manager', $fresh->role);
        $this->assertSame(0, $fresh->tokens()->count());
        $this->get('/akun')->assertOk();

        // Browser lain masih membawa hash password lama di sesinya: dikeluarkan ke halaman login.
        $this->flushSession();
        $this->withSession($otherBrowser)->actingAs($fresh)->get('/akun')->assertRedirect(route('login'));
    }
}
