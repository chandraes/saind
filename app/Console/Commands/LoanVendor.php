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

        $status = ['aktif', 'proses'];
        $vehicle = Vehicle::whereIn('status', $status)->where('gps', 1)->get();

        foreach ($vendor as $v) {
            $last = KasVendor::where('vendor_id', $v->id)->latest()->orderBy('id', 'desc')->first();

            $data = [];
            $data['vendor_id'] = $v->id;
            $data['pinjaman'] = 1500000;
            $data['bayar'] = 0;
            $data['sisa'] = $last ? $last->sisa+$data['pinjaman'] : $data['pinjaman'];
            $data['tanggal'] = date('Y-m-d');
            $data['uraian'] = 'Support Operational';

            KasVendor::create($data);

            usleep(10000);
        }

        foreach ($vehicle as $v) {
            $data = [];
            $data['vendor_id'] = $v->vendor->id;

            $last = KasVendor::where('vendor_id', $v->vendor->id)->latest()->orderBy('id', 'desc')->first();

            $data['pinjaman'] = 1500000;
            $data['bayar'] = 0;
            $data['sisa'] = $last ? $last->sisa+$data['pinjaman'] : $data['pinjaman'];
            $data['tanggal'] = date('Y-m-d');
            $data['uraian'] = 'GPS '.$v->nomor_lambung;

            KasVendor::create($data);

            usleep(10000);
        }

        $this->info('Success');
    }
}
