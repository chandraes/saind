<?php

namespace Tests\Feature;

use App\Models\KategoriFilterOliMesin;
use App\Models\User;
use Database\Seeders\KategoriFilterOliMesinSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class KategoriFilterOliMesinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        (require database_path('migrations/2026_10_04_094812_create_kategori_filter_oli_mesins_table.php'))->up();
        View::share([
            'global_app_name' => 'SAIND', 'global_app_favicon' => '',
            'global_app_nav_bg' => '#212529', 'global_app_logo' => '',
        ]);
        $this->withoutVite();
    }

    private function loginAs(string $role): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role, 'password' => 'password']));
    }

    public function test_seed_contains_requested_limits_and_is_idempotent(): void
    {
        $this->seed(KategoriFilterOliMesinSeeder::class);
        $this->assertSame([
            'Filter Solar: JAF20' => 10, 'Filter Solar: JAF40' => 20,
            'Filter Solar: JAE51' => 30, 'Filter Oli' => 34, 'Ganti Oli' => 17,
        ], KategoriFilterOliMesin::orderBy('id')->pluck('limit_ritase', 'nama')->all());
        KategoriFilterOliMesin::where('nama', 'Filter Oli')->update(['limit_ritase' => 40]);
        $this->seed(KategoriFilterOliMesinSeeder::class);
        $this->assertDatabaseCount('kategori_filter_oli_mesins', 5);
        $this->assertDatabaseHas('kategori_filter_oli_mesins', ['nama' => 'Filter Oli', 'limit_ritase' => 40]);
    }

    public function test_superuser_can_see_form_and_create_category(): void
    {
        $this->loginAs('su');
        $this->get(route('database.kategori-filter-oli-mesin.index'))->assertOk()->assertSee('Tambah Kategori');
        $this->post(route('database.kategori-filter-oli-mesin.store'), ['nama' => 'Filter Baru', 'limit_ritase' => 15])
            ->assertRedirect(route('database.kategori-filter-oli-mesin.index'));
        $this->assertDatabaseHas('kategori_filter_oli_mesins', ['nama' => 'Filter Baru', 'limit_ritase' => 15]);
    }

    public function test_admin_can_read_but_cannot_see_or_execute_add(): void
    {
        $category = KategoriFilterOliMesin::factory()->create();
        $this->loginAs('admin');
        $this->get(route('database.kategori-filter-oli-mesin.index'))->assertOk()->assertSee($category->nama)->assertDontSee('Tambah Kategori')->assertDontSee('Simpan Kategori');
        $this->post(route('database.kategori-filter-oli-mesin.store'), ['nama' => 'Dilarang', 'limit_ritase' => 15])->assertForbidden();
        $this->assertDatabaseCount('kategori_filter_oli_mesins', 1);
    }

    public function test_guest_and_other_roles_cannot_access_database_category(): void
    {
        $this->get(route('database.kategori-filter-oli-mesin.index'))->assertRedirect(route('login'));
        $this->post(route('database.kategori-filter-oli-mesin.store'), [])->assertRedirect(route('login'));
        foreach (['user', 'vendor', 'operasional'] as $role) {
            $this->loginAs($role);
            $this->get(route('database.kategori-filter-oli-mesin.index'))->assertForbidden();
            $this->post(route('database.kategori-filter-oli-mesin.store'), [])->assertForbidden();
        }
    }

    public function test_invalid_limits_and_names_are_rejected(): void
    {
        $this->loginAs('su');
        foreach ([null, 0, -1, 1.5, 'abc', 4294967296] as $limit) {
            $this->post(route('database.kategori-filter-oli-mesin.store'), ['nama' => 'Baru', 'limit_ritase' => $limit])->assertSessionHasErrors('limit_ritase');
        }
        foreach (['', str_repeat('a', 256)] as $name) {
            $this->post(route('database.kategori-filter-oli-mesin.store'), ['nama' => $name, 'limit_ritase' => 1])->assertSessionHasErrors('nama');
        }
        $this->assertDatabaseCount('kategori_filter_oli_mesins', 0);
        $category = KategoriFilterOliMesin::factory()->create();
        $this->post(route('database.kategori-filter-oli-mesin.store'), ['nama' => $category->nama, 'limit_ritase' => 1])->assertSessionHasErrors('nama');
        $this->assertDatabaseCount('kategori_filter_oli_mesins', 1);
    }

    public function test_minimum_limit_and_escaped_names_are_supported(): void
    {
        $this->loginAs('su');
        $this->post(route('database.kategori-filter-oli-mesin.store'), ['nama' => '<script>alert(1)</script>', 'limit_ritase' => 1])->assertSessionHasNoErrors();
        $this->get(route('database.kategori-filter-oli-mesin.index'))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_superuser_can_edit_name_and_limit_without_changing_id(): void
    {
        $category = KategoriFilterOliMesin::factory()->create(['nama' => 'Filter Lama', 'limit_ritase' => 10]);
        $this->loginAs('su');
        $this->get(route('database.kategori-filter-oli-mesin.index'))->assertSee('data-bs-target="#editCategoryModal'.$category->id.'"', false);
        $this->get(route('database.kategori-filter-oli-mesin.edit', $category))->assertSee('Filter Lama')->assertSee('value="10"', false);
        $this->patch(route('database.kategori-filter-oli-mesin.update', $category), ['nama' => 'Filter Baru', 'limit_ritase' => 20, 'id' => 999])
            ->assertRedirect(route('database.kategori-filter-oli-mesin.index'));
        $this->assertDatabaseHas('kategori_filter_oli_mesins', ['id' => $category->id, 'nama' => 'Filter Baru', 'limit_ritase' => 20]);
        $this->assertDatabaseCount('kategori_filter_oli_mesins', 1);
        $this->patch(route('database.kategori-filter-oli-mesin.update', $category), ['nama' => 'Filter Baru', 'limit_ritase' => 25])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kategori_filter_oli_mesins', ['id' => $category->id, 'limit_ritase' => 25]);
    }

    public function test_admin_can_edit_and_guest_must_login(): void
    {
        $category = KategoriFilterOliMesin::factory()->create(['nama' => 'Tetap', 'limit_ritase' => 10]);
        $this->get(route('database.kategori-filter-oli-mesin.edit', $category))->assertRedirect(route('login'));
        $this->patch(route('database.kategori-filter-oli-mesin.update', $category), [])->assertRedirect(route('login'));
        $this->loginAs('admin');
        $this->get(route('database.kategori-filter-oli-mesin.index'))->assertSee('data-bs-target="#editCategoryModal'.$category->id.'"', false)->assertDontSee('Tambah Kategori');
        $this->get(route('database.kategori-filter-oli-mesin.edit', $category))->assertSee('Tetap');
        $this->patch(route('database.kategori-filter-oli-mesin.update', $category), ['nama' => 'Diubah Admin', 'limit_ritase' => 20])->assertRedirect(route('database.kategori-filter-oli-mesin.index'));
        $this->assertDatabaseHas('kategori_filter_oli_mesins', ['id' => $category->id, 'nama' => 'Diubah Admin', 'limit_ritase' => 20]);
    }

    public function test_edit_rejects_duplicate_name_invalid_limit_and_missing_category(): void
    {
        $category = KategoriFilterOliMesin::factory()->create(['nama' => 'Tetap', 'limit_ritase' => 10]);
        KategoriFilterOliMesin::factory()->create(['nama' => 'Duplikat']);
        $this->loginAs('su');
        $this->patch(route('database.kategori-filter-oli-mesin.update', $category), ['nama' => 'Duplikat', 'limit_ritase' => 20])->assertSessionHasErrors('nama');
        $this->patch(route('database.kategori-filter-oli-mesin.update', $category), ['nama' => '', 'limit_ritase' => 0])->assertSessionHasErrors(['nama', 'limit_ritase']);
        $this->assertDatabaseHas('kategori_filter_oli_mesins', ['id' => $category->id, 'nama' => 'Tetap', 'limit_ritase' => 10]);
        $this->get(route('database.kategori-filter-oli-mesin.edit', 999))->assertNotFound();
        $this->patch(route('database.kategori-filter-oli-mesin.update', 999), ['nama' => 'Baru', 'limit_ritase' => 20])->assertNotFound();
    }

    public function test_other_roles_cannot_edit_categories(): void
    {
        $category = KategoriFilterOliMesin::factory()->create(['nama' => 'Tetap', 'limit_ritase' => 10]);
        foreach (['user', 'vendor', 'operasional'] as $role) {
            $this->loginAs($role);
            $this->get(route('database.kategori-filter-oli-mesin.edit', $category))->assertForbidden();
            $this->patch(route('database.kategori-filter-oli-mesin.update', $category), ['nama' => 'Dilarang', 'limit_ritase' => 20])->assertForbidden();
        }
        $this->assertDatabaseHas('kategori_filter_oli_mesins', ['id' => $category->id, 'nama' => 'Tetap', 'limit_ritase' => 10]);
    }

    public function test_list_has_number_column_and_edit_modal_with_current_values(): void
    {
        $category = KategoriFilterOliMesin::factory()->create(['nama' => 'Filter Modal', 'limit_ritase' => 34]);
        $this->loginAs('admin');
        $this->get(route('database.kategori-filter-oli-mesin.index'))
            ->assertSee('scope="col" class="text-center">No', false)
            ->assertSee('id="editCategoryModal'.$category->id.'"', false)
            ->assertSee(route('database.kategori-filter-oli-mesin.update', $category))
            ->assertSee('value="Filter Modal"', false)
            ->assertSee('value="34"', false)
            ->assertDontSee('href="'.route('database.kategori-filter-oli-mesin.edit', $category).'"', false);
    }

    public function test_invalid_edit_reopens_matching_modal_and_preserves_input(): void
    {
        $category = KategoriFilterOliMesin::factory()->create();
        $this->loginAs('admin');
        $url = route('database.kategori-filter-oli-mesin.index');
        $this->from($url)->patch(route('database.kategori-filter-oli-mesin.update', $category), [
            'nama' => 'Nama percobaan', 'limit_ritase' => 0, 'kategori_id' => $category->id,
        ])->assertRedirect($url)->assertSessionHasErrors('limit_ritase');
        $this->get($url)->assertSee('data-reopen-modal', false)->assertSee('value="Nama percobaan"', false)->assertSee('value="0"', false);
        $this->assertDatabaseHas('kategori_filter_oli_mesins', ['id' => $category->id, 'nama' => $category->nama, 'limit_ritase' => $category->limit_ritase]);
    }

    public function test_notifications_use_escaped_sweetalert_messages(): void
    {
        $this->loginAs('su');
        $url = route('database.kategori-filter-oli-mesin.index');
        $this->withSession(['success' => 'Kategori berhasil disimpan'])->get($url)
            ->assertSee('data-category-notification', false)
            ->assertSee('data-icon="success"', false)
            ->assertSee('data-category-form', false);
        $this->withSession(['error' => '<script>alert(1)</script>'])->get($url)
            ->assertSee('data-icon="error"', false)
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
