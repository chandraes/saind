<?php

namespace App\Console\Commands;

use App\Models\FilterOliLog;
use App\Services\FilterOliRitaseService;
use Illuminate\Console\Command;

class SyncFilterOliRitase extends Command
{
    protected $signature = 'filter-oli:sync-ritase';

    protected $description = 'Hitung ulang ritase dan rincian transaksi pada histori filter & oli mesin';

    public function handle(FilterOliRitaseService $ritase): int
    {
        $vehicleIds = FilterOliLog::query()->select('vehicle_id')->distinct()->orderBy('vehicle_id')->pluck('vehicle_id');
        foreach ($vehicleIds as $vehicleId) {
            $ritase->refreshVehicle($vehicleId);
        }
        $this->info('Ritase dan rincian transaksi disinkronkan untuk '.$vehicleIds->count().' kendaraan.');

        return self::SUCCESS;
    }
}
