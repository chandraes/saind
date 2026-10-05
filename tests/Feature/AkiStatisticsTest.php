<?php

namespace Tests\Feature;

use App\Http\Controllers\StatistikController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AkiStatisticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->travelTo(now()->setDate(2026, 10, 4)->startOfDay());
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('posisi_akis', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('aki_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('posisi_aki_id');
            $table->string('merk');
            $table->integer('kondisi');
            $table->timestamps();
        });
        DB::table('vehicles')->insert(['id' => 1]);
        DB::table('posisi_akis')->insert(['id' => 1]);
        foreach ([['GS', '2025-10-04 12:00:00'], ['YUASA', '2026-10-01 12:00:00'], ['GS BARU', '2026-10-03 12:00:00']] as [$merk, $date]) {
            DB::table('aki_logs')->insert(['vehicle_id' => 1, 'posisi_aki_id' => 1, 'merk' => $merk, 'kondisi' => 100, 'created_at' => $date, 'updated_at' => $date]);
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('aki_logs');
        Schema::dropIfExists('posisi_akis');
        Schema::dropIfExists('vehicles');
        $this->travelBack();
        parent::tearDown();
    }

    private function history(array $parameters = []): array
    {
        $request = Request::create('/history', 'GET', array_replace_recursive([
            'vehicle' => 1, 'posisi' => 1, 'start' => 0, 'length' => 10, 'draw' => 1,
            'order' => [['column' => 2, 'dir' => 'desc']],
        ], $parameters), server: ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        return app(StatistikController::class)->aki_histori_data($request)->getData(true);
    }

    public function test_history_ages_end_at_next_replacement_or_today(): void
    {
        $response = $this->history();
        $this->assertSame([1, 2, 362], array_column($response['data'], 'umur_hari'));
        $this->assertTrue($response['data'][0]['masih_digunakan']);
        $this->assertFalse($response['data'][1]['masih_digunakan']);
    }

    public function test_pagination_and_search_preserve_actual_replacement_age(): void
    {
        $page = $this->history(['start' => 1, 'length' => 1]);
        $this->assertSame('YUASA', $page['data'][0]['merk']);
        $this->assertSame(2, $page['data'][0]['umur_hari']);
        $filtered = $this->history(['search' => ['value' => 'GS']]);
        $this->assertSame(3, $filtered['recordsTotal']);
        $this->assertSame(2, $filtered['recordsFiltered']);
        $this->assertSame(362, $filtered['data'][1]['umur_hari']);
    }

    public function test_history_sorting_and_empty_results(): void
    {
        $ascending = $this->history(['order' => [['column' => 2, 'dir' => 'asc']]]);
        $this->assertSame('GS', $ascending['data'][0]['merk']);
        $empty = $this->history(['search' => ['value' => 'TIDAK ADA']]);
        $this->assertSame([], $empty['data']);
        $this->assertSame(0, $empty['recordsFiltered']);
    }

    public function test_statistics_templates_compile_with_valid_php(): void
    {
        foreach (['index', 'histori'] as $template) {
            $compiled = app('blade.compiler')->compileString(
                file_get_contents(resource_path('views/rekap/statistik/aki/'.$template.'.blade.php')),
            );

            $this->assertNotEmpty(token_get_all($compiled, TOKEN_PARSE));
        }
    }
}
