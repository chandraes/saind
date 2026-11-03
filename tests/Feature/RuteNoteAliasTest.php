<?php

namespace Tests\Feature;

use App\Models\RuteNoteAlias;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;

class RuteNoteAliasTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('jarak')->default(100);
            $table->integer('uang_jalan')->default(300000);
            $table->integer('uj_ditahan')->default(0);
        });
        DB::table('rutes')->insert([['id' => 1, 'nama' => 'MIP Selatan - Port'], ['id' => 2, 'nama' => 'MIP Utara - Port']]);
        (require database_path('migrations/2026_11_03_194757_create_rute_note_aliases_table.php'))->up();
    }

    public static function operators(): array
    {
        return [['admin'], ['su']];
    }

    #[DataProvider('operators')]
    public function test_route_operators_can_manage_multiple_aliases_and_cannot_delete_another_routes_alias(string $role): void
    {
        $this->actingAs(User::factory()->create(['username' => fake()->unique()->userName(), 'password' => 'secret-pass', 'role' => $role]));
        foreach (['STP CPP-01 - SDJ', 'STP CPP-02 - SDJ'] as $name) {
            $this->post('/rute/1/nota-alias', ['name' => $name])->assertRedirect(route('rute.index'));
        }
        $this->assertDatabaseCount('rute_note_aliases', 2);
        $alias = RuteNoteAlias::firstOrFail();
        $this->delete('/rute/2/nota-alias/'.$alias->id)->assertNotFound();
        $this->delete('/rute/1/nota-alias/'.$alias->id)->assertRedirect(route('rute.index'));
        $this->assertDatabaseCount('rute_note_aliases', 1);
    }

    public function test_normalized_alias_is_unique_across_routes_and_numbers_are_preserved(): void
    {
        $this->actingAs(User::factory()->create(['username' => 'admin', 'password' => 'secret-pass', 'role' => 'admin']));
        $this->post('/rute/1/nota-alias', ['name' => 'STP CPP-01 - SDJ'])->assertSessionHasNoErrors();
        $this->post('/rute/2/nota-alias', ['name' => ' stp cpp 01 sdj '])->assertSessionHasErrors('name');
        $this->post('/rute/2/nota-alias', ['name' => '---'])->assertSessionHasErrors('name');
        $this->assertDatabaseHas('rute_note_aliases', ['normalized_name' => 'STPCPP01SDJ', 'rute_id' => 1]);
        $this->assertDatabaseCount('rute_note_aliases', 1);
    }

    public function test_vendor_and_user_cannot_change_route_references(): void
    {
        foreach (['vendor-operational', 'user'] as $role) {
            $this->actingAs(User::factory()->create(['username' => $role, 'password' => 'secret-pass', 'role' => $role]));
            $this->post('/rute/1/nota-alias', ['name' => 'STP CPP-01 - SDJ'])->assertForbidden();
        }
        $this->assertDatabaseCount('rute_note_aliases', 0);
    }

    public function test_modal_is_scoped_to_route_and_escapes_alias_text(): void
    {
        $this->actingAs(User::factory()->create(['username' => 'admin', 'password' => 'secret-pass', 'role' => 'admin']));
        $alias = RuteNoteAlias::create(['rute_id' => 1, 'name' => '<script>alert(1)</script>']);
        RuteNoteAlias::create(['rute_id' => 2, 'name' => 'OTHER-ROUTE']);
        $this->get('/rute/1/nota-alias')->assertOk()->assertSee('MIP Selatan - Port')
            ->assertSee($alias->name)->assertDontSee($alias->name, false)->assertDontSee('OTHER-ROUTE');
    }

    public function test_aliases_appear_in_table_cell_with_modal_button(): void
    {
        $this->actingAs(User::factory()->create(['username' => 'admin', 'password' => 'secret-pass', 'role' => 'admin']));
        RuteNoteAlias::create(['rute_id' => 1, 'name' => 'STP CPP-01 - SDJ']);
        RuteNoteAlias::create(['rute_id' => 1, 'name' => 'STP CPP-02 - SDJ']);
        $response = $this->getJson('/rute?draw=1&start=0&length=-1', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $response->assertJsonCount(2, 'data');
        $cell = $response->json('data.0.note_aliases');
        $this->assertStringContainsString('STP CPP-01 - SDJ', $cell);
        $this->assertStringContainsString('STP CPP-02 - SDJ', $cell);
        $this->assertStringContainsString('data-note-alias-url', $cell);
    }

    public function test_alias_edit_is_unique_and_cannot_cross_route_boundaries(): void
    {
        $this->actingAs(User::factory()->create(['username' => 'admin', 'password' => 'secret-pass', 'role' => 'admin']));
        $alias = RuteNoteAlias::create(['rute_id' => 1, 'name' => 'STP CPP-01 - SDJ']);
        RuteNoteAlias::create(['rute_id' => 2, 'name' => 'STP CPP-02 - SDJ']);
        $this->patchJson('/rute/1/nota-alias/'.$alias->id, ['name' => 'STP CPP-01 - SDJ'])->assertOk();
        $this->patchJson('/rute/2/nota-alias/'.$alias->id, ['name' => 'Changed'])->assertNotFound();
        $this->patchJson('/rute/1/nota-alias/'.$alias->id, ['name' => 'stp cpp 02 sdj'])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->patchJson('/rute/1/nota-alias/'.$alias->id, ['name' => 'STP CPP-03 - SDJ'])->assertOk();
        $this->assertDatabaseHas('rute_note_aliases', ['id' => $alias->id, 'normalized_name' => 'STPCPP03SDJ']);
    }

    public function test_json_mutations_keep_modal_open_and_unauthorized_roles_cannot_read_or_edit_aliases(): void
    {
        $admin = User::factory()->create(['username' => 'admin', 'password' => 'secret-pass', 'role' => 'admin']);
        $this->actingAs($admin)->postJson('/rute/1/nota-alias', ['name' => 'STP CPP-01 - SDJ'])->assertOk()->assertJsonPath('message', 'Alias nota berhasil ditambahkan.');
        $alias = RuteNoteAlias::firstOrFail();
        $this->actingAs(User::factory()->create(['username' => 'vendor', 'password' => 'secret-pass', 'role' => 'vendor-operational']));
        $this->get('/rute/1/nota-alias')->assertForbidden();
        $this->patchJson('/rute/1/nota-alias/'.$alias->id, ['name' => 'Changed'])->assertForbidden();
        $this->actingAs($admin)->deleteJson('/rute/1/nota-alias/'.$alias->id)->assertOk();
        $this->assertDatabaseCount('rute_note_aliases', 0);
    }
}
