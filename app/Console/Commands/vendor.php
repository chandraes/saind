<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vendor;
use App\Models\Vehicle;
use App\Models\KasVendor;

class vendor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:vendor';

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
        $vendor = Vendor::where('status', 'aktif')->get();
        $vehicle = Vehicle::where('status', 'aktif')->orWhere('status', 'proses')->get();

        foreach ($vendor as $v) {
            $last = KasVendor::where('vendor_id', $v->id)->latest()->orderBy('id', 'desc')->first();
            
        }

        foreach ($vehicle as $v) {

        }

    }
}
