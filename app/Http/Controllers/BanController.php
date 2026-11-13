<?php

namespace App\Http\Controllers;

use App\Models\BanLog;
use App\Models\PosisiBan;
use App\Models\Vehicle;
use App\Support\MaintenanceHistoryGuard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->usesVendorMenus() && ! $request->filled('vehicle_id')) {
            return redirect()->route('vendor-operational.statistics.vehicles', ['report' => 'ban-luar']);
        }

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        if (Auth::user()->isVendorOwner()) {
            $vehicle = Vehicle::where('id', $request->vehicle_id)
                ->where('vendor_id', Auth::user()->vendor_id)
                ->first();

            if (! $vehicle) {
                return redirect()->back()->with('error', 'Kendaraan tidak ditemukan atau tidak sesuai dengan vendor Anda.');
            }

        }

        $vehicle = Vehicle::leftJoin('upah_gendongs as ug', 'vehicles.id', 'ug.vehicle_id')
            ->where('vehicles.id', $request->vehicle_id)
            ->select('vehicles.*', 'ug.nama_driver as nama_driver', 'ug.nama_pengurus as pengurus')
            ->first();

        // UPDATE: Tambahkan 'id' di dalam select
        $banLogs = BanLog::where('vehicle_id', $request->vehicle_id)
            ->when($request->user()->readsVendorStatistics(), fn ($query) => $query->where('id', function ($latest) {
                $latest->from('ban_logs as latest')->select('latest.id')->whereColumn('latest.vehicle_id', 'ban_logs.vehicle_id')->whereColumn('latest.posisi_ban_id', 'ban_logs.posisi_ban_id')->orderByDesc('latest.created_at')->orderByDesc('latest.id')->limit(1);
            }))
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
                    'tanggal_ganti' => Carbon::parse($banLog->created_at)->format('d-m-Y'),
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

    public function get_transaksi_ritase(Request $request, $banLogId)
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
            ->orderBy('kas_uang_jalans.tanggal', 'desc')->orderByDesc('transaksis.id');
        if ($request->user()->readsVendorStatistics()) {
            $transaksis->where('kas_uang_jalans.vendor_id', $request->user()->effectiveVendorId())->where('kas_uang_jalans.vehicle_id', BanLog::findOrFail($banLogId)->vehicle_id);
            $total = (clone $transaksis)->count();

            return response()->json(['data' => $transaksis->offset(max(0, $request->integer('start')))->limit(25)->get(), 'recordsFiltered' => $total]);
        }
        $transaksis = $transaksis->get();

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
        abort_unless($request->ajax(), 404);
        $validated = $request->validate([
            'vehicle' => 'required|integer|exists:vehicles,id',
            'posisi' => 'required|integer',
            'start' => 'nullable|integer|min:0',
            'length' => 'nullable|integer|min:1|max:100',
            'search.value' => 'nullable|string|max:100',
            'order.0.column' => 'nullable|integer|min:0',
            'order.0.dir' => 'nullable|in:asc,desc',
        ]);
        $query = BanLog::where('vehicle_id', $validated['vehicle'])->where('posisi_ban_id', $validated['posisi']);
        $total = (clone $query)->count();
        if ($search = $request->input('search.value')) {
            $query->where(function ($query) use ($search): void {
                $query->where('merk', 'like', '%'.$search.'%')->orWhere('no_seri', 'like', '%'.$search.'%');
            });
        }
        $filtered = (clone $query)->count();
        $columns = ['merk', 'no_seri', 'kondisi', 'ritase', 'created_at'];
        $column = $columns[$request->input('order.0.column', 4)] ?? 'created_at';
        $data = $query->orderBy($column, $request->input('order.0.dir', 'desc'))->orderByDesc('id')
            ->offset($request->integer('start'))->limit($request->integer('length', 25))->get();

        return response()->json(['draw' => $request->integer('draw'), 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }

    public function histori_delete($histori, Request $request)
    {
        $banLog = BanLog::findOrFail($histori);
        MaintenanceHistoryGuard::authorize($request, $banLog);
        if (! MaintenanceHistoryGuard::passwordMatches($request)) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        $banLog->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus data!!');
    }

    public function histori_update($histori, Request $request)
    {
        $data = $request->validate([
            'created_at' => 'required',
            'password' => 'required',
        ]);

        $banLog = BanLog::findOrFail($histori);
        MaintenanceHistoryGuard::authorize($request, $banLog);
        if (! MaintenanceHistoryGuard::passwordMatches($request)) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        unset($data['password']);

        $banLog->update($data);

        return redirect()->back()->with('success', 'Berhasil mengubah data!!');
    }
}
