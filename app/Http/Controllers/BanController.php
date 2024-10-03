<?php

namespace App\Http\Controllers;

use App\Models\BanLog;
use App\Models\PasswordKonfirmasi;
use App\Models\PosisiBan;
use App\Models\UpahGendong;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        // Retrieve vehicle with driver and pengurus information
        $vehicle = Vehicle::leftJoin('upah_gendongs as ug', 'vehicles.id', 'ug.vehicle_id')
                          ->where('vehicles.id', $request->vehicle_id)
                          ->select('vehicles.*', 'ug.nama_driver as nama_driver', 'ug.nama_pengurus as pengurus')
                          ->first();

        // Retrieve the latest BanLog for each posisi_ban_id for the given vehicle_id
        $banLogs = BanLog::where('vehicle_id', $request->vehicle_id)
                         ->select('posisi_ban_id', 'merk', 'no_seri', 'kondisi', 'created_at')
                         ->orderBy('created_at', 'desc')
                         ->get()
                         ->unique('posisi_ban_id')
                         ->mapWithKeys(function ($banLog) {
                             return [$banLog->posisi_ban_id => [
                                 'merk' => $banLog->merk,
                                 'no_seri' => $banLog->no_seri,
                                 'kondisi' => $banLog->kondisi,
                                 'tanggal_ganti' => \Carbon\Carbon::parse($banLog->created_at)->format('d-m-Y'),
                             ]];
                         });

        // Map the BanLog data to the PosisiBan
        $ban = PosisiBan::all()->map(function ($ban) use ($banLogs) {
            $ban->banLog = $banLogs[$ban->id] ?? null;
            return $ban;
        });

        return view('rekap.statistik.ban-luar.index', [
            'vehicle' => $vehicle,
            'ban' => $ban,
        ]);

    }

    public function log_store(Request $request)
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

    public function histori($vehicle, $posisi)
    {
        $vehicle = Vehicle::find($vehicle);

        return view('rekap.statistik.ban-luar.histori', [
            'vehicle' => $vehicle,
            'posisi' => PosisiBan::findOrFail($posisi),
        ]);
    }

    public function histori_data(Request $request)
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

    public function histori_delete($histori, Request $request)
    {
        $dbP = PasswordKonfirmasi::first();

        if ($request->password != $dbP->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        $banLog = BanLog::findOrFail($histori);
        $banLog->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus data!!');
    }

    public function histori_update($histori, Request $request)
    {
        $data = $request->validate([
            'created_at' => 'required',
            'password' => 'required',
        ]);

        $dbP = PasswordKonfirmasi::first();

        if ($data['password'] != $dbP->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        unset($data['password']);

        $banLog = BanLog::findOrFail($histori);

        $banLog->update($data);

        return redirect()->back()->with('success', 'Berhasil mengubah data!!');
    }
}
