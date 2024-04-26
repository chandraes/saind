<?php

namespace App\Http\Controllers;

use App\Models\AktivasiMaintenance;
use App\Models\BanLog;
use App\Models\KategoriBarangMaintenance;
use App\Models\MaintenanceLog;
use App\Models\OdoLog;
use App\Models\PosisiBan;
use App\Models\Transaksi;
use App\Models\UpahGendong;
use App\Models\Vehicle;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PerVendorController extends Controller
{
    public function upah_gendong(Request $request)
    {
        $vehicle = $request->vehicle_id;
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $tanggal_filter = $request->tanggal_filter ?? null;

        $check = Vehicle::where('id', $vehicle)->first();

        $vendorVehicle = Vendor::join('vehicles as v', 'v.vendor_id', 'vendors.id')
                                ->where('v.id', $vehicle)
                                ->first();

        if($check == null || $vendorVehicle == null){
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $ug = UpahGendong::with(['vehicle'])
                            ->where('vehicle_id', $vehicle)
                            ->first();

        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date vrom $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;


        if ($tanggal_filter != null) {
            if (strpos($tanggal_filter, 'to') !== false) {
                // $tanggalFilter is a date range
                $dates = explode('to', $tanggal_filter);
                $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                // dd($startDate, $endDate, $filter, $tanggalFilter);
                $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer'])
                                ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->join('rutes as r', 'r.id', 'kuj.rute_id')
                                ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung', 'r.jarak as jarak')
                                ->where('transaksis.void', 0)
                                ->where('kuj.vehicle_id', $vehicle)
                                ->whereBetween('tanggal', [$startDate, $endDate])
                                ->get();

            } else {
                // $tanggalFilter is a single date
                $date = Carbon::createFromFormat('d-m-Y', trim($tanggal_filter));
                $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer'])
                                ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->join('rutes as r', 'r.id', 'kuj.rute_id')
                                ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung', 'r.jarak as jarak')
                                ->where('transaksis.void', 0)
                                ->where('kuj.vehicle_id', $vehicle)
                                ->where('tanggal', '>=', $date)
                                ->get();

            }
        } else{
            $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer'])
                        ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                        ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                        ->join('rutes as r', 'r.id', 'kuj.rute_id')
                        ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung', 'r.jarak as jarak')
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->where('transaksis.void', 0)
                        ->where('kuj.vehicle_id', $vehicle)
                        ->get();
        }


        // dd($data);
        $grand_total_tonase = $data->reduce(function ($carry, $transaction) {
                            $tonase = $transaction->timbangan_bongkar ?? 0;
                            return $carry + $tonase;
                        }, 0);

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();


        return view('per-vendor.upah-gendong.index', [
            'data' => $data,
            'ug'    => $ug,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_angka' => $bulan,
            'vehicle' => $vehicle,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'tanggal_filter' => $tanggal_filter,
            'dataTahun' => $dataTahun,
            'grand_total_tonase' => $grand_total_tonase,
        ]);
    }

    public function maintenance_vehicle(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:aktivasi_maintenances,vehicle_id',
        ]);


        // check if vehicle belongs to vendor
        $vehicle = Vehicle::where('id', $request->vehicle_id)->first();

        if ($vehicle == null) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $tanggalNow = now();
        $odo = 0;
        $baut = '-';
        $db = new MaintenanceLog();
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = $db->dataTahun();
        // dd($dataTahun);

        $equipment = KategoriBarangMaintenance::select('id', 'nama')->get();

        $tahun = $request->tahun ?? date('Y');

        // Define the start and end of the year
        $startOfYear = Carbon::create($tahun)->startOfYear();
        $endOfYear = Carbon::create($tahun)->endOfYear();

        $activation_start = AktivasiMaintenance::where('vehicle_id', $data['vehicle_id'])->first()->tanggal_mulai;

        // If the activation year is the same as the requested year, use the activation date as the start date
        // Otherwise, use the start of the requested year
        $start_date = $activation_start->year == $tahun ? $activation_start : $startOfYear;

        // Fetch all relevant MaintenanceLog records for the year
        $maintenanceLogs = MaintenanceLog::where('vehicle_id', $data['vehicle_id'])
            ->whereIn('kategori_barang_maintenance_id', $equipment->pluck('id'))
            ->whereBetween('created_at', [$start_date, $endOfYear])
            ->get();

        // Fetch all relevant OdoLog records for the year
        $odoLogs = OdoLog::where('vehicle_id', $data['vehicle_id'])
            ->whereBetween('created_at', [$start_date, $endOfYear])
            ->get();

        $i = 0;
        while (true) {
            $startOfWeek = $start_date->copy()->addWeeks($i)->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            if ($endOfWeek->greaterThan($endOfYear)) {
                break;
            }

            // Set the locale to Indonesian
            Carbon::setLocale('id');

            $week = $startOfWeek->translatedFormat('d M') . ' - ' . $endOfWeek->translatedFormat('d M');

            foreach ($equipment as $eq) {
                // Filter the maintenance logs in memory
                $count = $maintenanceLogs->where('kategori_barang_maintenance_id', $eq->id)
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sum('qty') ?? 0;

                // Filter the odo logs in memory
                $weekly[$week]['odometer'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->odometer ?? 0;

                if ($weekly[$week]['odometer'] !=0) {
                    $odo = $weekly[$week]['odometer'];
                }

                $weekly[$week]['filter_strainer'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->filter_strainer ?? '-';

                if (Carbon::parse($tanggalNow)->between($startOfWeek, $endOfWeek)){
                    $state = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->sortByDesc('created_at')
                            ->first() ? 1 : 0;
                }

                $weekly[$week]['filter_udara'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->filter_udara ?? '-';

                $weekly[$week]['baut'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->baut ?? '-';

                if ($weekly[$week]['baut'] != '-') {
                    $baut = $weekly[$week]['baut'];
                }

                $weekly[$week][$eq->nama] = $count;
            }

            $i++;
        }

        $vehicle = Vehicle::leftJoin('upah_gendongs', 'vehicles.id', '=', 'upah_gendongs.vehicle_id')
            ->where('vehicles.id', $data['vehicle_id'])
            ->select('vehicles.*', 'upah_gendongs.nama_driver as driver', 'upah_gendongs.nama_pengurus as pengurus', 'upah_gendongs.tanggal_masuk_driver as tanggal_masuk_driver', 'upah_gendongs.tanggal_masuk_pengurus as tanggal_masuk_pengurus')
            ->first();

        return view('per-vendor.maintenance.index', [
            'weekly' => $weekly,
            'vehicle' => $vehicle,
            'equipment' => $equipment,
            'dataTahun' => $dataTahun,
            'tahun' => $tahun,
            'odo' => $odo,
            'baut' => $baut,
        ]);
    }

    public function maintenance_vehicle_print(Request $request)
    {

        ini_set('max_execution_time', 80);
        ini_set('memory_limit', '256M');

        $data = $request->validate([
            'vehicle_id' => 'required|exists:aktivasi_maintenances,vehicle_id',
        ]);

          // check if vehicle belongs to vendor
        $vehicle = Vehicle::where('id', $request->vehicle_id)->first();

        if ($vehicle == null) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $tanggalNow = now();

        $db = new MaintenanceLog();
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = $db->dataTahun();
        // dd($dataTahun);

        $equipment = KategoriBarangMaintenance::select('id', 'nama')->get();

        $tahun = $request->tahun ?? date('Y');

        // Define the start and end of the year
        $startOfYear = Carbon::create($tahun)->startOfYear();
        $endOfYear = Carbon::create($tahun)->endOfYear();

        $activation_start = AktivasiMaintenance::where('vehicle_id', $data['vehicle_id'])->first()->tanggal_mulai;

        // If the activation year is the same as the requested year, use the activation date as the start date
        // Otherwise, use the start of the requested year
        $start_date = $activation_start->year == $tahun ? $activation_start : $startOfYear;

        // Fetch all relevant MaintenanceLog records for the year
        $maintenanceLogs = MaintenanceLog::where('vehicle_id', $data['vehicle_id'])
            ->whereIn('kategori_barang_maintenance_id', $equipment->pluck('id'))
            ->whereBetween('created_at', [$start_date, $endOfYear])
            ->get();

        // Fetch all relevant OdoLog records for the year
        $odoLogs = OdoLog::where('vehicle_id', $data['vehicle_id'])
            ->whereBetween('created_at', [$start_date, $endOfYear])
            ->get();

        $i = 0;
        while (true) {
            $startOfWeek = $start_date->copy()->addWeeks($i)->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            if ($endOfWeek->greaterThan($endOfYear)) {
                break;
            }

            // Set the locale to Indonesian
            Carbon::setLocale('id');

            $week = $startOfWeek->translatedFormat('d M') . ' - ' . $endOfWeek->translatedFormat('d M');

            foreach ($equipment as $eq) {
                // Filter the maintenance logs in memory
                $count = $maintenanceLogs->where('kategori_barang_maintenance_id', $eq->id)
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sum('qty') ?? 0;

                // Filter the odo logs in memory
                $weekly[$week]['odometer'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->max('odometer') ?? 0;

                $weekly[$week]['filter_strainer'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->filter_strainer ?? '-';

                $weekly[$week]['filter_udara'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->filter_udara ?? '-';

                $weekly[$week]['baut'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->baut ?? '-';

                $weekly[$week][$eq->nama] = $count;
            }

            $i++;
        }


        $vehicle = Vehicle::leftJoin('upah_gendongs', 'vehicles.id', '=', 'upah_gendongs.vehicle_id')
            ->where('vehicles.id', $data['vehicle_id'])
            ->select('vehicles.*', 'upah_gendongs.nama_driver as driver', 'upah_gendongs.nama_pengurus as pengurus', 'upah_gendongs.tanggal_masuk_driver as tanggal_masuk_driver', 'upah_gendongs.tanggal_masuk_pengurus as tanggal_masuk_pengurus')
            ->first();

        $pdf = PDF::loadview('per-vendor.maintenance.print', [
                    'weekly' => $weekly,
                    'vehicle' => $vehicle,
                    'equipment' => $equipment,
                    'dataTahun' => $dataTahun,
                    'tahun' => $tahun,
                ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Maintenance '.$tahun.'.pdf');
    }

    public function store_odo(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:aktivasi_maintenances,vehicle_id',
            'odometer' => 'required',
            'filter_strainer' => 'required',
            'filter_udara' => 'required',
            'baut' => 'required|numeric',
        ]);

        $data['odometer'] = str_replace('.', '', $data['odometer']);

        OdoLog::create([
            'vehicle_id' => $data['vehicle_id'],
            'odometer' => $data['odometer'],
            'filter_strainer' => $data['filter_strainer'],
            'filter_udara' => $data['filter_udara'],
            'baut' => $data['baut'],
        ]);

        return redirect()->back()->with('success', 'Berhasil menambahkan Odometer!!');
    }

    public function ban_luar(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        // check if vehicle belongs to vendor from auth->user->vendor_id
        $vehicle = Vehicle::where('vendor_id', auth()->user()->vendor_id)->where('id', $request->vehicle_id)->first();

        if ($vehicle == null) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $vehicle = Vehicle::leftJoin('upah_gendongs as ug', 'vehicles.id', 'ug.vehicle_id')->where('vehicles.id',$request->vehicle_id)
                    ->select('vehicles.*', 'ug.nama_driver as nama_driver', 'ug.nama_pengurus as pengurus')->first();

        $banLogs = BanLog::where('vehicle_id', $request->vehicle_id)
                    ->select('posisi_ban_id', DB::raw('MAX(created_at) as max_created_at'))
                    ->groupBy('posisi_ban_id')
                    ->get()
                    ->mapWithKeys(function ($banLog) {
                        $banLog = BanLog::where('posisi_ban_id', $banLog->posisi_ban_id)
                                        ->where('created_at', $banLog->max_created_at)
                                        ->first();
                        return [$banLog->posisi_ban_id => [
                            'merk' => $banLog->merk,
                            'no_seri' => $banLog->no_seri,
                            'kondisi' => $banLog->kondisi,
                            'tanggal_ganti' => \Carbon\Carbon::parse($banLog->created_at)->format('d-m-Y'),
                        ]];
                    });

        $ban = PosisiBan::all()->map(function ($ban) use ($banLogs) {
            $ban->banLog = $banLogs[$ban->id] ?? null;
            return $ban;
        });

        return view('per-vendor.ban-luar.index', [
            'vehicle' => $vehicle,
            'ban' => $ban,
        ]);
    }

    public function ban_luar_store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'posisi_ban_id' => 'required|exists:posisi_bans,id',
            'merk' => 'required',
            'no_seri' => 'required',
            'kondisi' => 'required',
        ]);

        BanLog::create($data);

        return redirect()->back()->with('success', 'Berhasil menambahkan data!!');
    }

    public function ban_histori($vehicle, $posisi)
    {
        $vehicle = Vehicle::find($vehicle);

        return view('per-vendor.ban-luar.histori', [
            'vehicle' => $vehicle,
            'posisi' => PosisiBan::findOrFail($posisi),
        ]);
    }

    public function ban_histori_data(Request $request)
    {
        if ($request->ajax()) {
            $length = $request->get('length'); // Get the requested number of records

            // Define the columns for sorting
            $columns = ['merk', 'no_seri', 'kondisi', 'created_at'];

            $query = BanLog::where('vehicle_id', $request->vehicle)
                        ->where('posisi_ban_id', $request->posisi)
                        ->orderBy('created_at', 'desc');

            // Handle the sorting
            if ($request->has('order')) {
                $columnIndex = $request->get('order')[0]['column']; // Get the index of the sorted column
                $sortDirection = $request->get('order')[0]['dir']; // Get the sort direction
                $column = $columns[$columnIndex]; // Get the column name

                $query->orderBy($column, $sortDirection);
            }

            $data = $query->paginate($length); // Use the requested number of records

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $data->total(),
                'recordsFiltered' => $data->total(),
                'data' => $data->items(),
            ]);
        }

        return abort(404);
    }

    public function ban_histori_delete($histori, Request $request)
    {
        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        $banLog = BanLog::findOrFail($histori);
        $banLog->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus data!!');
    }


}
