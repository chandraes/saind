<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UjDitahanPencairanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Storage::fake('local');
        Schema::create('uj_ditahans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->decimal('saldo', 15, 2);
            $table->decimal('total_keluar', 15, 2)->default(0);
        });
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'user', 'password' => 'password']));
    }

    #[TestWith([2026, 9, 1])]
    #[TestWith([2026, 9, 200000])]
    #[TestWith([2025, 12, 1])]
    public function test_any_positive_earlier_balance_blocks_withdrawal_and_closes_transaction(int $year, int $month, int $balance): void
    {
        DB::table('uj_ditahans')->insert(['vehicle_id' => 1, 'tahun' => $year, 'bulan' => $month, 'saldo' => $balance]);
        $current = DB::table('uj_ditahans')->insertGetId(['vehicle_id' => 1, 'tahun' => 2026, 'bulan' => 10, 'saldo' => 300000]);
        $this->post(route('billing.uj-ditahan.cairkan'), $this->data($current))->assertRedirect()->assertSessionHas('error', 'Silahkan gunakan Saldo UJ Ditahan pada bulan sebelumnya terlebih dahulu.');
        $this->assertDatabaseHas('uj_ditahans', ['id' => $current, 'saldo' => 300000, 'total_keluar' => 0]);
        $this->assertSame(0, DB::transactionLevel());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_zero_earlier_balance_other_vehicle_and_future_balance_do_not_block(): void
    {
        DB::table('uj_ditahans')->insert([
            ['vehicle_id' => 1, 'tahun' => 2026, 'bulan' => 9, 'saldo' => 0],
            ['vehicle_id' => 2, 'tahun' => 2026, 'bulan' => 9, 'saldo' => 100000],
            ['vehicle_id' => 1, 'tahun' => 2026, 'bulan' => 11, 'saldo' => 100000],
        ]);
        $current = DB::table('uj_ditahans')->insertGetId(['vehicle_id' => 1, 'tahun' => 2026, 'bulan' => 10, 'saldo' => 1]);
        $this->post(route('billing.uj-ditahan.cairkan'), $this->data($current))->assertRedirect()->assertSessionHas('error', 'Nominal pencairan melebihi sisa saldo bulan ini!');
        $this->assertSame(0, DB::transactionLevel());
        $this->assertDatabaseHas('uj_ditahans', ['id' => $current, 'saldo' => 1, 'total_keluar' => 0]);
    }

    /** @return array<string, int|string|UploadedFile> */
    private function data(int $master): array
    {
        return ['uj_ditahan_id' => $master, 'nominal' => '100.000', 'keterangan' => 'Pencairan',
            'bank' => 'BCA', 'no_rekening' => '123', 'nama_rekening' => 'Penerima',
            'bukti_pdf' => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf')];
    }
}
