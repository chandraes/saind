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

            $appLogo = !empty($settings['app_logo'])
                ? asset('storage/' . $settings['app_logo'])
                : asset(config('app.default_logo'));

            $appFavicon = !empty($settings['app_favicon'])
                ? asset('storage/' . $settings['app_favicon'])
                : asset(config('app.default_favicon'));

            // 3. Bagikan variabel ke SEMUA view
            View::share([
                'global_app_name' => $appName,
                'global_app_logo' => $appLogo,
                'global_app_favicon' => $appFavicon,
            ]);
        }
    }
}
