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

        $vehicle = Vehicle::leftJoin('upah_gendongs as ug', 'vehicles.id', 'ug.vehicle_id')
                        ->where('vehicles.id', $request->vehicle_id)
                        ->select('vehicles.*', 'ug.nama_driver as nama_driver', 'ug.nama_pengurus as pengurus')
                        ->first();

        // UPDATE: Tambahkan 'id' di dalam select
        $banLogs = BanLog::where('vehicle_id', $request->vehicle_id)
                        ->select('id', 'posisi_ban_id', 'merk', 'no_seri', 'kondisi', 'ritase', 'created_at')
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->unique('posisi_ban_id')
                        ->mapWithKeys(function ($banLog) {
                            return [$banLog->posisi_ban_id => [
                                'id' => $banLog->id, // UPDATE: Masukkan id ke dalam array mapping
                                'merk' => $banLog->merk,
                                'no_seri' => $banLog->no_seri,
                                'kondisi' => $banLog->kondisi,
                                'ritase' => $banLog->ritase,
                                'tanggal_ganti' => \Carbon\Carbon::parse($banLog->created_at)->format('d-m-Y'),
                            ]];
                        });

        $ban = PosisiBan::all()->map(function ($ban) use ($banLogs) {
            $ban->banLog = $banLogs[$ban->id] ?? null;
            return $ban;
        });

        return view('rekap.statistik.ban-luar.index', [
            'vehicle' => $vehicle,
            'ban' => $ban,
        ]);
    }

    public function get_transaksi_ritase($banLogId)
    {
        // Query ini akan mengambil riwayat transaksi dari pivot dan menggabungkannya
        // dengan data KasUangJalan dan Rute agar informatif.
        $transaksis = DB::table('ban_log_transaksis')
            ->join('transaksis', 'ban_log_transaksis.transaksi_id', '=', 'transaksis.id')
            ->join('kas_uang_jalans', 'transaksis.kas_uang_jalan_id', '=', 'kas_uang_jalans.id')
            ->join('rutes', 'kas_uang_jalans.rute_id', '=', 'rutes.id')
            ->where('ban_log_transaksis.ban_log_id', $banLogId)
            ->select(
                'kas_uang_jalans.nomor_uang_jalan',
                'kas_uang_jalans.tanggal',
                'rutes.nama as rute',
                'rutes.jarak',
                'ban_log_transaksis.nilai_ritase',
                'ban_log_transaksis.created_at'
            )
            ->orderBy('kas_uang_jalans.tanggal', 'desc')
            ->get();

        return response()->json($transaksis);
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
            $length = $request->get('length');

            // Tambahkan ritase ke daftar kolom yang dapat di-sort
            $columns = ['merk', 'no_seri', 'kondisi', 'ritase', 'created_at'];

            $query = BanLog::where('vehicle_id', $request->vehicle)
                        ->where('posisi_ban_id', $request->posisi)
                        ->orderBy('created_at', 'desc');

            if ($request->has('order')) {
                $columnIndex = $request->get('order')[0]['column'];
                $sortDirection = $request->get('order')[0]['dir'];
                $column = $columns[$columnIndex] ?? 'created_at';

                $query->orderBy($column, $sortDirection);
            }

            $data = $query->paginate($length);

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
