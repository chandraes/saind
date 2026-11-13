<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;

/** Dari mobile nota muat hanya boleh dikirim lewat scan: endpoint kirim manual ditutup untuk semua role. */
class MobileManualLoadNoteClosedTest extends MobileApiTestCase
{
    #[TestWith(['vendor'])]
    #[TestWith(['vendor-induk'])]
    #[TestWith(['vendor-operational'])]
    #[TestWith(['operasional'])]
    #[TestWith(['admin'])]
    public function test_manual_submission_is_closed_for_every_role(string $role): void
    {
        Storage::fake('local');
        (require database_path('migrations/2026_11_03_104025_create_load_note_drafts_table.php'))->up();
        DB::table('vendors')->insert(['id' => 1, 'nama' => 'Vendor A']);
        DB::table('vehicles')->insert(['id' => 1, 'vendor_id' => 1, 'nomor_lambung' => '169']);
        $user = User::factory()->create(['username' => Str::uuid()->toString(), 'password' => 'secret-pass', 'role' => $role, 'vendor_id' => $role === 'operasional' || $role === 'admin' ? null : 1]);
        $headers = ['Accept' => 'application/json', 'X-Workspace-ID' => self::WORKSPACE, 'X-Vendor-ID' => '1',
            'Authorization' => 'Bearer '.$user->createToken('test', ['workspace:'.self::WORKSPACE])->plainTextToken];

        $this->assertFalse(Route::has('api.v1.load-notes.store'));
        $this->postJson('/api/v1/load-notes', ['request_id' => Str::uuid()->toString(), 'vehicle_id' => 1, 'ticket_number' => 'SJB-1',
            'mine_name' => 'PT A', 'loaded_on' => '2026-10-23', 'gross_weight' => '45.54', 'tare_weight' => '15.32',
            'photo' => UploadedFile::fake()->image('nota.jpg', 200, 300)], $headers)->assertMethodNotAllowed();
        $this->assertTrue(Schema::hasTable('load_note_drafts'));
        $this->assertDatabaseCount('load_note_drafts', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }
}
