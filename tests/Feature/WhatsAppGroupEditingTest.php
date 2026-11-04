<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WhatsAppGroupEditingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        (require database_path('migrations/2023_09_06_160726_create_group_was_table.php'))->up();
        (require database_path('migrations/2023_11_20_111552_add_group_id_column_to_group_was_table.php'))->up();
        config(['services.starsender.key' => 'test-device-key']);
        $this->withoutVite();
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
    }

    private function group(): GroupWa
    {
        return GroupWa::create(['untuk' => 'kas-uang-jalan', 'nama_group' => 'old@g.us', 'group_id' => 'Grup lama']);
    }

    private function login(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
    }

    public function test_edit_loads_current_api_groups_and_preserves_id_name_mapping(): void
    {
        Http::fake(['api.starsender.online/api/whatsapp/groups' => Http::response(['success' => true, 'data' => ['groups' => [null, ['id' => 'old@g.us', 'name' => 'Grup lama'], ['id' => 'new@g.us', 'name' => 'Grup baru']]]])]);
        $group = $this->group();
        $this->login();
        $this->get(route('pengaturan.wa.edit', $group->id))->assertOk()->assertSee('Grup baru')->assertSee('value="old@g.us" selected', false);
        Http::assertSent(fn ($request): bool => $request->method() === 'GET' && $request->url() === 'https://api.starsender.online/api/whatsapp/groups' && $request->hasHeader('Authorization', 'test-device-key'));
        $this->patch(route('pengaturan.wa.update', $group->id), ['nama_group' => 'new@g.us', 'group_id' => 'Grup baru'])->assertRedirect(route('pengaturan.wa'));
        $this->assertDatabaseHas('group_was', ['id' => $group->id, 'nama_group' => 'new@g.us', 'group_id' => 'Grup baru']);
    }

    public static function badResponses(): array
    {
        return [['<html>Provider error</html>', 500], ['not-json', 200], [['success' => false], 401], [['success' => true, 'data' => null], 200]];
    }

    #[DataProvider('badResponses')]
    public function test_provider_failure_shows_warning_instead_of_array_offset_error(mixed $body, int $status): void
    {
        Http::fake(['api.starsender.online/*' => Http::response($body, $status)]);
        $group = $this->group();
        $this->login();
        $this->get(route('pengaturan.wa.edit', $group->id))->assertOk()->assertSee('Daftar grup WhatsApp belum dapat dimuat')->assertSee('Grup lama')
            ->assertViewHas('group', [])->assertViewHas('groupsUnavailable', true)->assertSee('id="save-wa-group" disabled', false);
        $this->assertSame('old@g.us', $group->fresh()->nama_group);
        Http::assertSentCount(1);
    }

    public function test_connection_failure_and_missing_key_are_handled_without_changing_group(): void
    {
        Http::fake(['api.starsender.online/*' => Http::failedConnection()]);
        $group = $this->group();
        $this->login();
        $this->get(route('pengaturan.wa.edit', $group->id))->assertOk()->assertViewHas('groupsUnavailable', true);
        Http::assertSentCount(1);
        Http::fake();
        config(['services.starsender.key' => null]);
        $this->get(route('pengaturan.wa.edit', $group->id))->assertOk()->assertViewHas('groupsUnavailable', true);
        Http::assertNothingSent();
    }

    public function test_empty_list_and_missing_record_are_safe(): void
    {
        Http::fake(['api.starsender.online/*' => Http::response(['success' => true, 'data' => ['groups' => []]])]);
        $group = $this->group();
        $this->login();
        $this->get(route('pengaturan.wa.edit', $group->id))->assertOk()->assertSee('Belum ada grup WhatsApp yang tersedia')->assertViewHas('group', []);
        Http::assertSentCount(1);
        Http::fake();
        $this->get(route('pengaturan.wa.edit', 999999))->assertNotFound();
        $this->get(route('pengaturan.wa.edit', 'not-a-number'))->assertNotFound();
        Http::assertNothingSent();
    }
}
