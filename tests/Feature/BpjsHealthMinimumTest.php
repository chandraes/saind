<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BpjsHealthMinimumService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class BpjsHealthMinimumTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'bpjs_test', 'database.connections.bpjs_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        (require glob(database_path('migrations/*_create_bpjs_health_minimums_table.php'))[0])->up();
        $this->withoutVite();
        View::share(['global_app_name' => 'Uji', 'global_app_favicon' => '', 'global_app_nav_bg' => '#212529', 'global_app_logo' => '']);
        $user = new User(['name' => 'Admin uji', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
    }

    private function payload(): array
    {
        return ['year' => 2026, 'minimum_wage' => 4000000, 'basis' => 'UMK', 'reference' => 'Keputusan gubernur untuk pengujian'];
    }

    #[TestWith(['admin'])]
    #[TestWith(['su'])]
    public function test_admin_can_save_and_edit_one_setting_per_year(string $role): void
    {
        $user = new User(['name' => 'Pengelola', 'role' => $role]);
        $user->id = 997;
        $this->actingAs($user);
        $this->get(route('bpjs-health.index'))->assertOk()->assertSee('Dasar minimum BPJS Kesehatan');
        $this->post(route('bpjs-health.store'), $this->payload())->assertSessionHasNoErrors();
        $this->post(route('bpjs-health.store'), [...$this->payload(), 'minimum_wage' => 4500000])->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('bpjs_health_minimums')->count());
        $this->assertEquals(4500000, DB::table('bpjs_health_minimums')->value('minimum_wage'));
        $this->post(route('bpjs-health.store'), [...$this->payload(), 'year' => 2027])->assertSessionHasNoErrors();
        $this->assertSame(2, DB::table('bpjs_health_minimums')->count());
    }

    public function test_invalid_values_and_unprivileged_users_are_rejected(): void
    {
        $this->post(route('bpjs-health.store'), [...$this->payload(), 'minimum_wage' => 0])->assertSessionHasErrors('minimum_wage');
        $this->post(route('bpjs-health.store'), [...$this->payload(), 'minimum_wage' => 12000001])->assertSessionHasErrors('minimum_wage');
        $this->post(route('bpjs-health.store'), [...$this->payload(), 'year' => '2026.5', 'basis' => 'lainnya'])->assertSessionHasErrors(['year', 'basis']);
        $user = new User(['name' => 'Pembaca', 'role' => 'user']);
        $user->id = 998;
        $this->actingAs($user);
        $this->get(route('bpjs-health.index'))->assertForbidden();
        $this->post(route('bpjs-health.store'), $this->payload())->assertForbidden();
        $this->assertSame(0, DB::table('bpjs_health_minimums')->count());
    }

    public function test_health_base_uses_requested_year_floor_and_ceiling(): void
    {
        DB::table('bpjs_health_minimums')->insert([
            ['year' => 2026, 'minimum_wage' => 4000000, 'basis' => 'UMK', 'reference' => 'Uji'],
            ['year' => 2027, 'minimum_wage' => 4500000, 'basis' => 'UMP', 'reference' => 'Uji'],
        ]);
        $service = app(BpjsHealthMinimumService::class);
        $this->assertSame(4000000, $service->base(3000000, 2026));
        $this->assertSame(4500000, $service->base(3000000, 2027));
        $this->assertSame(8000000, $service->base(8000000, 2026));
        $this->assertSame(12000000, $service->base(15000000, 2026));
        $this->assertSame(4000000, $service->base(0, 2026));
        $this->expectException(ValidationException::class);
        $service->requireConfigured(2028);
    }

    public function test_setting_reads_are_memoized_and_lock_refreshes_current_value(): void
    {
        DB::table('bpjs_health_minimums')->insert(['year' => 2026, 'minimum_wage' => 4000000, 'basis' => 'UMK', 'reference' => 'Uji']);
        $service = app(BpjsHealthMinimumService::class);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $service->base(1, 2026);
        $service->base(2, 2026);
        $reads = collect(DB::getQueryLog())->filter(fn ($q): bool => str_contains($q['query'], 'select * from "bpjs_health_minimums"'));
        DB::disableQueryLog();
        $this->assertCount(1, $reads);
        DB::table('bpjs_health_minimums')->where('year', 2026)->update(['minimum_wage' => 4500000]);
        DB::transaction(fn () => $service->requireConfigured(2026, true));
        $this->assertSame(4500000, $service->base(1, 2026));
    }
}
