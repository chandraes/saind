<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConfigureMobileWorkspace extends Command
{
    protected $signature = 'mobile:configure-workspace {--name= : Nama ruang kerja} {--company= : Nama perusahaan}';

    protected $description = 'Configure mobile workspace identity without changing an existing workspace UUID';

    public function handle(): int
    {
        $name = trim((string) $this->option('name'));
        $company = trim((string) $this->option('company'));
        if ($name === '' || $company === '' || mb_strlen($name) > 255 || mb_strlen($company) > 255) {
            $this->error('Isi --name dan --company, masing-masing maksimal 255 karakter.');

            return self::FAILURE;
        }
        if (config('mobile.workspace_id') || config('mobile.workspace_name') || config('mobile.company_name')) {
            $this->error('Identitas diatur melalui MOBILE_WORKSPACE_* / MOBILE_COMPANY_NAME. Perbarui konfigurasi environment tersebut.');

            return self::FAILURE;
        }
        $id = DB::transaction(function () use ($name, $company): string {
            $identity = Setting::firstOrCreate(['key' => 'mobile_workspace_id'], ['value' => (string) Str::uuid()]);
            if (! Str::isUuid((string) $identity->value)) {
                throw new \RuntimeException('UUID ruang kerja tersimpan tidak valid; perbaiki sebelum melanjutkan.');
            }
            Setting::updateOrCreate(['key' => 'mobile_workspace_name'], ['value' => $name]);
            Setting::updateOrCreate(['key' => 'mobile_company_name'], ['value' => $company]);

            return $identity->value;
        });
        Cache::forget('app_settings');
        $this->info('Workspace ID: '.$id);

        return self::SUCCESS;
    }
}
