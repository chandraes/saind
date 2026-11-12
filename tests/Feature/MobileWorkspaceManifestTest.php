<?php

namespace Tests\Feature;

use App\Support\WorkspaceManifest;
use Illuminate\Support\Facades\DB;

/** Manifest ruang kerja bertanda tangan DS Groups untuk validasi origin di aplikasi mobile. */
class MobileWorkspaceManifestTest extends MobileApiTestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://saind2.ds-groups.com']);
        $this->dir = sys_get_temp_dir().'/ws-manifest-'.uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*') ?: []);
        rmdir($this->dir);
        parent::tearDown();
    }

    /** @return array{0: string, 1: string} path kunci rahasia dan kunci publik */
    private function key(): array
    {
        $path = $this->dir.'/signing.key';
        $this->artisan('mobile:workspace-manifest', ['action' => 'key', 'value' => $path])->assertSuccessful();
        $this->assertSame(0600, fileperms($path) & 0777);
        $this->artisan('mobile:workspace-manifest', ['action' => 'key', 'value' => $path])->assertFailed();

        return [$path, base64_encode(sodium_crypto_sign_publickey_from_secretkey(
            sodium_base642bin(trim(file_get_contents($path)), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING)))];
    }

    public function test_signed_manifest_verifies_with_public_key_and_binds_exact_origin(): void
    {
        [$path, $public] = $this->key();
        $manifest = WorkspaceManifest::sign(file_get_contents($path), strtoupper(self::WORKSPACE), 'https://SAIND2.ds-groups.com:443/', 1700000000);
        [$payload, $signature] = explode('.', $manifest);
        $b64 = SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;

        $this->assertTrue(sodium_crypto_sign_verify_detached(sodium_base642bin($signature, $b64), $payload, base64_decode($public)));
        $this->assertSame(['v' => 1, 'workspace_id' => self::WORKSPACE, 'origin' => 'https://saind2.ds-groups.com', 'iat' => 1700000000],
            WorkspaceManifest::payload($manifest));
        $this->assertSame('https://a.test:8443', WorkspaceManifest::origin('https://a.test:8443'));
        foreach (['http://a.test', 'https://a.test/login', 'https://u:p@a.test'] as $bad) {
            $this->assertThrows(fn () => WorkspaceManifest::origin($bad), \InvalidArgumentException::class);
        }
    }

    public function test_install_stores_only_a_manifest_for_this_workspace_and_app_url(): void
    {
        [$path] = $this->key();
        $this->getJson('/api/v1/workspace')->assertOk()->assertJsonPath('data.manifest', null);
        $other = WorkspaceManifest::sign(file_get_contents($path), self::WORKSPACE, 'https://saind3.ds-groups.com');
        $this->artisan('mobile:workspace-manifest', ['action' => 'install', 'value' => $other])->assertFailed();
        $this->artisan('mobile:workspace-manifest', ['action' => 'install', 'value' => 'rusak'])->assertFailed();
        $this->assertDatabaseMissing('settings', ['key' => 'mobile_workspace_manifest']);

        $this->artisan('mobile:workspace-manifest', ['action' => 'sign', '--key' => $path, '--id' => self::WORKSPACE,
            '--origin' => 'https://saind2.ds-groups.com'])->assertSuccessful();
        $manifest = WorkspaceManifest::sign(file_get_contents($path), self::WORKSPACE, 'https://saind2.ds-groups.com');
        $this->artisan('mobile:workspace-manifest', ['action' => 'install', 'value' => $manifest])->assertSuccessful();
        $this->getJson('/api/v1/workspace')->assertOk()->assertJsonPath('data.manifest', $manifest);
        $this->assertSame(1, DB::table('settings')->where('key', 'mobile_workspace_manifest')->count());

        config(['mobile.workspace_manifest' => 'dari-env']);
        $this->getJson('/api/v1/workspace')->assertJsonPath('data.manifest', 'dari-env');
        $this->artisan('mobile:workspace-manifest', ['action' => 'install', 'value' => $manifest])->assertFailed();
    }
}
