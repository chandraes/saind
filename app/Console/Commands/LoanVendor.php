<?php

namespace App\Console\Commands;

use App\Models\KasVendor;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Console\Command;

class LoanVendor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:loan-vendor';

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

        $vendor = Vendor::where('status', 'aktif')->where('support_operational', 1)->get();

        foreach ($vendor as $v) {
            $last = KasVendor::where('vendor_id', $v->id)->latest()->orderBy('id', 'desc')->first();
            $vehicle = Vehicle::where('vendor_id', $v->id)->whereNot('status', 'nonaktif')->count();
            $data = [];
            $data['vendor_id'] = $v->id;
            $data['pinjaman'] = 1500000 * $vehicle;
            $data['bayar'] = 0;
            $data['sisa'] = $last ? $last->sisa+$data['pinjaman'] : $data['pinjaman'];
            $data['tanggal'] = date('Y-m-d');
            $data['uraian'] = 'Support Operational';

            KasVendor::create($data);

            usleep(10000);
        }

        $this->info('Success');
    }
}
