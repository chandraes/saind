<?php

namespace App\Console\Commands;

use App\Models\Konfigurasi;
use Illuminate\Console\Command;

class CheckKonfigruasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-konfigurasi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Konfigurasi Aplikasi Setiap Menit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = Konfigurasi::where('kode', 'nota-muat')->first();

        if($data && $data->status == 0 && $data->updated_at->diffInMinutes(now()) >= 10){
            $data->update(['status' => 1]);
        }
    }
}
