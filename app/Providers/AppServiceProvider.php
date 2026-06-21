<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       if (Schema::hasTable('settings')) {

            // 1. Ambil dari Cache atau DB
            $settings = Cache::rememberForever('app_settings', function () {
                return Setting::pluck('value', 'key')->toArray();
            });

            // 2. Logika Default (Fallback)
            $appName = $settings['app_name'] ?? config('app.name');
            $appPerusahaan = $settings['app_perusahaan'] ?? 'Nama Perusahaan Default';
            $appAlamat = $settings['app_alamat'] ?? 'Alamat Default';
            $appKeuangan = $settings['app_keuangan'] ?? 'Nama Manajer Keuangan Default';

            $logoFilename = !empty($settings['app_logo'])
                ? 'storage/' . $settings['app_logo']
                : config('app.default_logo', 'assets/img/logo-default.png');

            // Versi URL untuk Web Browser
            $appLogoUrl = asset($logoFilename);

            // Versi PATH untuk PDF
            $appLogoPath = public_path($logoFilename);

            // --- PERBAIKAN DI SINI: Cek file_exists SEBELUM file_get_contents ---
            // file_exists() otomatis mengembalikan false jika symlink rusak/corrupt
            if (!file_exists($appLogoPath) || is_dir($appLogoPath)) {
                $appLogoPath = public_path('assets/img/no-image.png'); // Pastikan file fallback ini ada
            }

            // Ambil data base64 dengan aman setelah path dipastikan valid
            $base64 = '';
            if (file_exists($appLogoPath)) {
                $type = pathinfo($appLogoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($appLogoPath);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }

            $appFavicon = !empty($settings['app_favicon'])
                ? asset('storage/' . $settings['app_favicon'])
                : asset(config('app.default_favicon', 'assets/img/favicon-default.png'));

            // 3. Bagikan variabel ke SEMUA view
            View::share([
                'global_app_name' => $appName,
                'global_app_perusahaan' => $appPerusahaan,
                'global_app_alamat' => $appAlamat,
                'global_app_logo' => $appLogoUrl,
                'global_app_logo_base64' => $base64,
                'global_app_favicon' => $appFavicon,
                'global_app_keuangan' => $appKeuangan,
            ]);
        }
    }
}
