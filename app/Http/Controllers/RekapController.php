<?php

namespace App\Http\Controllers;

use App\Models\KasKecil;
use App\Models\KasBesar;
use App\Models\Vendor;
use App\Models\KasUangJalan;
use App\Models\KasVendor;
use App\Models\Transaksi;
use App\Models\Rekening;
use App\Models\GroupWa;
use App\Services\StarSender;
use App\Models\PasswordKonfirmasi;
use App\Models\RekapBarang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapController extends Controller
{

    public function index()
    {
        $vendor = Vendor::all();

        return view('rekap.index', [
            'vendor' => $vendor,
        ]);
    }

    public function kas_besar(Request $request)
    {
        // kas besar perbulan dan tahun, jika tidak ada request maka default bulan dan tahun saat ini
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasBesar::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasBesar::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasBesar::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();
        // dd($bulan);
        return view('rekap.kas-besar', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function preview_kas_besar($bulan, $tahun)
    {
        $data = KasBesar::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();
        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasBesar::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();
        // dd($bulan);
        $pdf = PDF::loadview('rekap.preview-kas-besar', [
            'data' => $data,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kas Besar '.$stringBulanNow.' '.$tahun.'.pdf');
    }

    public function kas_kecil(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasKecil::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasKecil::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasKecil::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();
        // dd($bulan);
        return view('rekap.kas-kecil', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function preview_kas_kecil(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $data = KasKecil::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasKecil::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();
        // dd($bulan);
        $pdf = PDF::loadview('rekap.preview-kas-kecil', [
            'data' => $data,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kas Kecil '.$stringBulanNow.' '.$tahun.'.pdf');
    }

    public function kas_uang_jalan(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasUangJalan::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasUangJalan::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasUangJalan::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();

        return view('rekap.kas-uang-jalan', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function preview_kas_uang_jalan(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $data = KasUangJalan::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasUangJalan::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();
        // dd($bulan);
        $pdf = PDF::loadview('rekap.preview-kas-uang-jalan', [
            'data' => $data,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kas Uang Jalan '.$stringBulanNow.' '.$tahun.'.pdf');
    }

    public function nota_void(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')->selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', '=', 'transaksis.kas_uang_jalan_id')
                            ->select('transaksis.*')
                            ->whereMonth('kuj.tanggal', $bulan)->whereYear('kuj.tanggal', $tahun)->where('transaksis.void', 1)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        return view('rekap.nota-void', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function preview_nota_void(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', '=', 'transaksis.kas_uang_jalan_id')
                            ->select('transaksis.*')
                            ->whereMonth('kuj.tanggal', $bulan)->whereYear('kuj.tanggal', $tahun)->where('transaksis.void', 1)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        $pdf = PDF::loadview('rekap.preview-nota-void', [
            'data' => $data,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Nota Void '.$stringBulanNow.' '.$tahun.'.pdf');
    }

    public function stock_barang()
    {
         // kas besar perbulan dan tahun, jika tidak ada request maka default bulan dan tahun saat ini
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = RekapBarang::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = RekapBarang::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = RekapBarang::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();
        // dd($bulan);
        return view('rekap.stock-barang', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function kas_vendor(Request $request)
    {
        $vendor = Vendor::find($request->vendor);

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasVendor::where('vendor_id', $request->vendor)->selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasVendor::where('vendor_id', $request->vendor)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasVendor::where('vendor_id', $request->vendor)->whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();

        $data = $vendor->kas_vendor()->get();

        return view('rekap.kas-vendor', [
            'data' => $data,
            'vendor' => $vendor,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function kas_vendor_void(Request $request, KasVendor $kas_vendor)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (!$password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        $kas_vendor->update([
            'void' => 1,
        ]);

        $data['vendor_id'] = $kas_vendor->vendor_id;
        $data['vehicle_id'] = $kas_vendor->vehicle_id;
        $data['bbm_storing_id'] = $kas_vendor->bbm_storing_id;
        $data['tanggal'] = date('Y-m-d');
        $data['uraian'] = 'Void '.$kas_vendor->uraian;
        $data['bayar'] = $kas_vendor->pinjaman;
        $data['sisa'] = $kas_vendor->sisa - $data['bayar'];

        KasVendor::create($data);

        if ($kas_vendor->storing == 1) {
            $last = KasBesar::latest()->first();
            $rekening = Rekening::where('untuk', 'kas-besar')->first();

            $kas['tanggal'] = date('Y-m-d');
            $kas['uraian'] = 'Void '.$kas_vendor->uraian;
            $kas['nominal_transaksi'] = $kas_vendor->bbm_storing->biaya_mekanik;
            $kas['jenis_transaksi_id'] = 1;
            $kas['saldo'] = $last->saldo + $kas['nominal_transaksi'];
            $kas['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
            $kas['no_rekening'] = $rekening->nomor_rekening;
            $kas['bank'] = $rekening->nama_bank;
            $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;

            $store = KasBesar::create($kas);

            $group = GroupWa::where('untuk', 'kas-besar')->first();
            $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                    "*Form Void BBM Storing*\n".
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    "No. Lambung : ".$kas_vendor->vehicle->nomor_lambung."\n".
                    "Vendor : ".$kas_vendor->vendor->nama."\n\n".
                    "Lokasi : ".$kas_vendor->bbm_storing->km."\n".
                    "Nilai :  *Rp. ".number_format($kas['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$kas['bank']."\n".
                    "Nama    : ".$kas['transfer_ke']."\n".
                    "No. Rek : ".$kas['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";
            $send = new StarSender($group->nama_group, $pesan);
            $res = $send->sendGroup();
        }

        return redirect()->route('rekap.index')->with('success', 'Berhasil Void Kas Vendor!!');

    }
}
