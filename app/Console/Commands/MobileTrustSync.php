<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\RevocationList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Salin daftar pencabutan + timestamp terbaru dari repo trust DS Groups agar
 * server ini menjadi cermin untuk aplikasi mobile (GET /api/v1/trust).
 */
class MobileTrustSync extends Command
{
    protected $signature = 'mobile:trust-sync';

    protected $description = 'Sinkronkan daftar pencabutan ruang kerja mobile dari repo trust DS Groups';

    public function handle(): int
    {
        try {
            $response = Http::timeout(15)->withoutRedirecting()->acceptJson()->get((string) config('mobile.trust_url'));
            $bundle = RevocationList::bundle($response->successful() ? $response->json() : null);
        } catch (Throwable $e) {
            $this->error('Sinkronisasi gagal: '.$e->getMessage());

            return self::FAILURE;
        }
        $stored = Setting::query()->where('key', 'mobile_trust_bundle')->value('value');
        try {
            $current = $stored ? RevocationList::bundle(json_decode($stored, true)) : null;
        } catch (Throwable) {
            $current = null;
        }
        // Jangan mundur ke daftar lama; seq sama boleh (timestamp diperpanjang).
        if ($current !== null && $bundle['seq'] < $current['seq']) {
            $this->warn("Daftar sumber (seq {$bundle['seq']}) lebih lama dari salinan server (seq {$current['seq']}); tidak diubah.");

            return self::SUCCESS;
        }
        Setting::updateOrCreate(['key' => 'mobile_trust_bundle'],
            ['value' => json_encode(['revocations' => $bundle['revocations'], 'timestamp' => $bundle['timestamp']])]);
        $this->info("Daftar pencabutan seq {$bundle['seq']} tersimpan.");

        return self::SUCCESS;
    }
}
