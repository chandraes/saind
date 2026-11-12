<?php

namespace Tests\Feature;

use App\Support\RevocationList;
use App\Support\WorkspaceManifest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/** Daftar pencabutan ruang kerja: penandatanganan dan cermin di server. */
class MobileRevocationListTest extends MobileApiTestCase
{
    private const B64 = SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;

    private string $secret;

    protected function setUp(): void
    {
        parent::setUp();
        $this->secret = WorkspaceManifest::generateKeyPair()['secret'];
        config(['mobile.trust_url' => 'https://trust.example.test/hauling/trust.json']);
    }

    /** Bundle seperti hauling/trust.json; timestamp ditandatangani kunci lain (kunci perpanjangan). */
    private function bundle(int $seq, array $revoked = []): array
    {
        $list = RevocationList::sign($this->secret, ['seq' => $seq, 'revoked' => $revoked]);
        $timestamp = WorkspaceManifest::signPayload(WorkspaceManifest::generateKeyPair()['secret'], ['v' => 1, 'type' => 'timestamp',
            'seq' => $seq, 'sha256' => hash('sha256', $list), 'issued_at' => time(), 'expires_at' => time() + 14 * 86400]);

        return ['revocations' => $list, 'timestamp' => $timestamp];
    }

    public function test_signs_normalised_entries_and_rejects_bad_sources(): void
    {
        $list = RevocationList::sign($this->secret, ['seq' => 3, 'revoked' => [
            ['workspace_id' => strtoupper(self::WORKSPACE), 'reason' => 'kontrak berakhir'],
            ['origin' => 'https://LAMA.example.test:443/', 'issued_before' => 1790000000],
        ]], 1800000000);
        [$payload, $signature] = explode('.', $list);
        $public = sodium_crypto_sign_publickey_from_secretkey(sodium_base642bin($this->secret, self::B64));
        $this->assertTrue(sodium_crypto_sign_verify_detached(sodium_base642bin($signature, self::B64), $payload, $public));
        $this->assertSame(['v' => 1, 'type' => 'revocations', 'seq' => 3, 'issued_at' => 1800000000, 'revoked' => [
            ['workspace_id' => self::WORKSPACE, 'reason' => 'kontrak berakhir'],
            ['origin' => 'https://lama.example.test', 'issued_before' => 1790000000],
        ]], WorkspaceManifest::decode($list));

        foreach ([['seq' => 0, 'revoked' => []], ['seq' => 1], ['seq' => 1, 'revoked' => [['reason' => 'x']]],
            ['seq' => 1, 'revoked' => [['workspace_id' => 'bukan-uuid']]], ['seq' => 1, 'revoked' => [['origin' => 'http://a.test']]],
            ['seq' => 1, 'revoked' => [['workspace_id' => self::WORKSPACE, 'lain' => 1]]]] as $bad) {
            $this->assertThrows(fn () => RevocationList::sign($this->secret, $bad), InvalidArgumentException::class);
        }
    }

    public function test_command_signs_a_source_file(): void
    {
        $dir = sys_get_temp_dir().'/revocations-'.uniqid();
        mkdir($dir);
        file_put_contents("$dir/key", $this->secret);
        file_put_contents("$dir/source.json", json_encode(['seq' => 1, 'revoked' => []]));
        try {
            $this->artisan('mobile:revocation-list', ['source' => "$dir/source.json", '--key' => "$dir/key"])->assertSuccessful();
            $this->artisan('mobile:revocation-list', ['source' => "$dir/missing.json", '--key' => "$dir/key"])->assertFailed();
        } finally {
            array_map('unlink', glob("$dir/*"));
            rmdir($dir);
        }
    }

    public function test_sync_mirrors_newest_bundle_and_never_goes_back(): void
    {
        $this->getJson('/api/v1/trust')->assertNotFound();
        $serve = null;
        Http::fake(function () use (&$serve) {
            return $serve;
        });
        $v2 = $this->bundle(2, [['workspace_id' => self::WORKSPACE]]);
        $serve = Http::response($v2);
        $this->artisan('mobile:trust-sync')->assertSuccessful();
        $this->getJson('/api/v1/trust')->assertOk()->assertJsonPath('data', $v2);

        // Sumber menyajikan daftar lama: salinan server tetap seq 2.
        $serve = Http::response($this->bundle(1));
        $this->artisan('mobile:trust-sync')->assertSuccessful();
        $this->getJson('/api/v1/trust')->assertJsonPath('data', $v2);

        // Pasangan tidak cocok atau sumber gagal: tidak disimpan.
        $bad = ['revocations' => $this->bundle(3)['revocations'], 'timestamp' => $v2['timestamp']];
        $serve = Http::response($bad);
        $this->artisan('mobile:trust-sync')->assertFailed();
        $serve = Http::response('', 500);
        $this->artisan('mobile:trust-sync')->assertFailed();
        $this->assertSame(1, DB::table('settings')->where('key', 'mobile_trust_bundle')->count());
        $this->getJson('/api/v1/trust')->assertJsonPath('data', $v2);

        // Seq sama dengan timestamp baru: diperpanjang.
        $renewed = ['revocations' => $v2['revocations']];
        $renewed['timestamp'] = WorkspaceManifest::signPayload(WorkspaceManifest::generateKeyPair()['secret'], ['v' => 1, 'type' => 'timestamp',
            'seq' => 2, 'sha256' => hash('sha256', $v2['revocations']), 'issued_at' => time() + 60, 'expires_at' => time() + 15 * 86400]);
        $serve = Http::response($renewed);
        $this->artisan('mobile:trust-sync')->assertSuccessful();
        $this->getJson('/api/v1/trust')->assertJsonPath('data.timestamp', $renewed['timestamp']);
    }
}
