<?php

namespace App\Console\Commands;

use App\Models\GroupWa;
use App\Services\StarSender;
use Illuminate\Console\Command;

class KirimWa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:kirim-wa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateTime = date('Y-m-d H:i:s');
        $no = '085208303087';
        $message = 'Halo, ini pesan dari aplikasi. '. $dateTime;

        $send = new StarSender($no, $message);
        $res = $send->sendGroup();

        $this->info('Success');
    }
}
