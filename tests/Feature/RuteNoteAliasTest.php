<?php

namespace Tests\Feature;

use App\Models\Rute;
use App\Models\RuteNoteAlias;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\DataProvider;

class RuteNoteAliasTest extends MobileApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('rutes', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
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

    public function test_alias_panel_renders_route_mapping_and_escapes_note_text(): void
    {
        $alias = RuteNoteAlias::create(['rute_id' => 1, 'name' => '<script>alert(1)</script>']);
        $this->view('database.rute.nota-alias', [
            'errors' => new ViewErrorBag,
            'routeChoices' => Rute::all(),
            'noteAliases' => RuteNoteAlias::with('rute')->paginate(20),
        ])->assertSee('MIP Selatan - Port')->assertSee($alias->name)->assertDontSee($alias->name, false);
    }
}
