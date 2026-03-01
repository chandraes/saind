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

            // 1. Coba ambil dari Cache. Jika tidak ada, ambil dari DB & simpan ke Cache selamanya
            $settings = Cache::rememberForever('app_settings', function () {
                return Setting::pluck('value', 'key')->toArray();
            });

            // 2. Logika Default (Fallback)
            // Jika key di DB tidak ada atau nilainya null, ambil dari config
            $appName = $settings['app_name'] ?? config('app.name');
             // TAMBAHKAN: Fallback untuk Perusahaan dan Alamat
            $appPerusahaan = $settings['app_perusahaan'] ?? 'Nama Perusahaan Default';
            $appAlamat = $settings['app_alamat'] ?? 'Alamat Default';
            $appKeuangan = $settings['app_keuangan'] ?? 'Nama Manajer Keuangan Default';

           $logoFilename = !empty($settings['app_logo'])
                ? 'storage/' . $settings['app_logo']
                : config('app.default_logo'); // misal 'assets/img/logo-default.png'

            // 2. Versi URL (Untuk Tampilan Web Browser) -> http://...
            $appLogoUrl = asset($logoFilename);

            // 3. Versi PATH (Untuk PDF) -> C:\xampp\htdocs\...
            // Gunakan public_path() untuk mendapatkan lokasi file di server
            $appLogoPath = public_path($logoFilename);

            $path = public_path($logoFilename);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

            // Validasi opsional: Jika file tidak ada di path, gunakan gambar kosong/placeholder
            if (!file_exists($appLogoPath)) {
                // Fallback jika file fisik tidak ketemu (opsional)
                $appLogoPath = public_path('assets/img/no-image.png');
            }

            $appFavicon = !empty($settings['app_favicon'])
                ? asset('storage/' . $settings['app_favicon'])
                : asset(config('app.default_favicon'));

            // 3. Bagikan variabel ke SEMUA view
            View::share([
                'global_app_name' => $appName,
                'global_app_perusahaan' => $appPerusahaan, // Bagikan ke view
                'global_app_alamat' => $appAlamat,         //
                'global_app_logo' => $appLogoUrl,  // <-- Pakai ini di blade biasa (index, create, edit)
               'global_app_logo_base64' => $base64,
                'global_app_favicon' => $appFavicon,
                'global_app_keuangan' => $appKeuangan,
            ]);
        }
    }
}
