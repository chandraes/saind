<?php

namespace Tests\Feature;

use App\Models\GroupWa;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class WaSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'services.starsender.key' => 'test-device-key']);
        DB::purge('sqlite');
        (require database_path('migrations/2023_09_06_160726_create_group_was_table.php'))->up();
        (require database_path('migrations/2023_11_20_111552_add_group_id_column_to_group_was_table.php'))->up();
        GroupWa::create(['id' => 1, 'untuk' => 'kas-uj-ditahan', 'nama_group' => '123@g.us', 'group_id' => 'Grup Uji']);
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin', 'password' => 'password']));
        View::share(['global_app_name' => 'SAIND', 'global_app_favicon' => '', 'global_app_nav_bg' => '', 'global_app_logo' => '']);
        $this->withoutVite();
    }

    public function test_edit_uses_config_key_and_v3_group_response(): void
    {
        Http::fake(['api.starsender.online/api/whatsapp/groups' => Http::response(['success' => true, 'data' => ['groups' => [['id' => '123@g.us', 'name' => 'Grup Uji']]]])]);
        $this->get(route('pengaturan.wa.edit', 1))->assertOk()->assertSee('Grup Uji')->assertSee('value="123@g.us"', false);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET' && $request->url() === 'https://api.starsender.online/api/whatsapp/groups' && $request->hasHeader('Authorization', 'test-device-key'));
    }

    #[TestWith([401, ['success' => false]])]
    #[TestWith([502, '<html>Bad gateway</html>'])]
    #[TestWith([200, 'invalid-json'])]
    #[TestWith([200, ['success' => true, 'data' => null]])]
    public function test_provider_failure_shows_message_instead_of_500(int $status, mixed $body): void
    {
        Http::fake(['api.starsender.online/*' => Http::response($body, $status)]);
        $this->get(route('pengaturan.wa.edit', 1))->assertRedirect(route('pengaturan.wa'))->assertSessionHas('error');
        $this->assertDatabaseHas('group_was', ['id' => 1, 'nama_group' => '123@g.us']);
    }

    public function test_timeout_does_not_break_settings(): void
    {
        Http::fake(['api.starsender.online/*' => Http::failedConnection()]);
        $this->get(route('pengaturan.wa.edit', 1))->assertRedirect(route('pengaturan.wa'))->assertSessionHas('error');
    }

    public function test_missing_key_does_not_call_provider(): void
    {
        config(['services.starsender.key' => '']);
        Http::fake();
        $this->get(route('pengaturan.wa.edit', 1))->assertRedirect(route('pengaturan.wa'))->assertSessionHas('error');
        Http::assertNothingSent();
    }

    public function test_missing_setting_returns_404_without_provider_request(): void
    {
        Http::fake();
        $this->get(route('pengaturan.wa.edit', 999))->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_empty_group_list_is_safe(): void
    {
        Http::fake(['api.starsender.online/*' => Http::response(['success' => true, 'data' => ['groups' => []]])]);
        $this->get(route('pengaturan.wa.edit', 1))->assertOk();
    }
}
