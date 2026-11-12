<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\MobileWorkspace;
use App\Support\WorkspaceManifest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * key     : buat pasangan kunci DS Groups (sekali; simpan kunci rahasia offline).
 * sign    : tanda tangani manifest untuk satu ruang kerja (di komputer pemegang kunci).
 * install : pasang manifest di server ruang kerja ini setelah dicocokkan dengan identitas & APP_URL.
 */
class MobileWorkspaceManifest extends Command
{
    protected $signature = 'mobile:workspace-manifest
        {action : key|sign|install}
        {value? : key: path file kunci rahasia baru; install: manifest}
        {--key= : sign: path file kunci rahasia}
        {--id= : sign: Workspace ID (UUID)}
        {--origin= : sign: alamat HTTPS ruang kerja, mis. https://saind2.ds-groups.com}';

    protected $description = 'Kelola manifest ruang kerja mobile bertanda tangan DS Groups';

    public function handle(MobileWorkspace $workspace): int
    {
        try {
            return match ($this->argument('action')) {
                'key' => $this->generateKey((string) $this->argument('value')),
                'sign' => $this->sign(),
                'install' => $this->install($workspace, (string) $this->argument('value')),
                default => $this->fail('Aksi harus key, sign, atau install.'),
            };
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function generateKey(string $path): int
    {
        if ($path === '' || file_exists($path)) {
            throw new InvalidArgumentException('Isi path file kunci baru yang belum ada (kunci lama tidak ditimpa).');
        }
        $pair = WorkspaceManifest::generateKeyPair();
        $old = umask(077);
        try {
            if (file_put_contents($path, $pair['secret'].PHP_EOL) === false) {
                throw new InvalidArgumentException('Kunci tidak dapat ditulis ke '.$path);
            }
        } finally {
            umask($old);
        }
        chmod($path, 0600);
        $this->info('Kunci rahasia disimpan di '.$path.' (simpan cadangan offline; jangan taruh di server atau repo).');
        $this->line('Kunci publik untuk aplikasi mobile: '.$pair['public']);

        return self::SUCCESS;
    }

    private function sign(): int
    {
        $path = (string) $this->option('key');
        $id = (string) $this->option('id');
        $origin = (string) $this->option('origin');
        if ($path === '' || ! is_readable($path) || $id === '' || $origin === '') {
            throw new InvalidArgumentException('Isi --key (file terbaca), --id, dan --origin.');
        }
        $this->line(WorkspaceManifest::sign((string) file_get_contents($path), $id, $origin));

        return self::SUCCESS;
    }

    private function install(MobileWorkspace $workspace, string $manifest): int
    {
        if (config('mobile.workspace_manifest')) {
            throw new InvalidArgumentException('Manifest diatur melalui MOBILE_WORKSPACE_MANIFEST. Perbarui environment tersebut.');
        }
        $payload = WorkspaceManifest::payload($manifest);
        $identity = $workspace->identity();
        $origin = WorkspaceManifest::origin((string) config('app.url'));
        if ($payload['workspace_id'] !== strtolower($identity['id']) || $payload['origin'] !== $origin) {
            throw new InvalidArgumentException("Manifest untuk {$payload['origin']} ({$payload['workspace_id']}) tidak cocok dengan ruang kerja ini: {$origin} ({$identity['id']}).");
        }
        Setting::updateOrCreate(['key' => 'mobile_workspace_manifest'], ['value' => trim($manifest)]);
        Cache::forget('app_settings');
        $this->info('Manifest terpasang untuk '.$origin.'.');

        return self::SUCCESS;
    }
}
