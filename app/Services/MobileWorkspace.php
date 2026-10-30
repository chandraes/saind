<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Str;

class MobileWorkspace
{
    /** @return array{id: string, name: string, company_name: string, product_name: string, api_version: string} */
    public function identity(): array
    {
        $settings = Setting::whereIn('key', ['mobile_workspace_id', 'mobile_workspace_name', 'mobile_company_name'])->pluck('value', 'key');
        $id = config('mobile.workspace_id') ?: $settings->get('mobile_workspace_id');
        $name = config('mobile.workspace_name') ?: $settings->get('mobile_workspace_name');
        $company = config('mobile.company_name') ?: $settings->get('mobile_company_name');
        abort_unless(is_string($id) && Str::isUuid($id) && is_string($name) && trim($name) !== '' && is_string($company) && trim($company) !== '', 503, 'Ruang kerja mobile belum dikonfigurasi.');

        return ['id' => $id, 'name' => $name, 'company_name' => $company,
            'product_name' => config('mobile.product_name'), 'api_version' => config('mobile.api_version')];
    }
}
