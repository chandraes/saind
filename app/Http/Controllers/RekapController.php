<?php

namespace App\Http\Controllers;

use App\Models\KasKecil;
use App\Models\KasBesar;
use App\Models\Vendor;
use App\Models\Direksi;
use App\Models\KasBon;
use App\Models\KasDireksi;
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

    public function direksi()
    {
        $direksi = Direksi::all();

        return view('rekap.direksi', [
            'data' => $direksi,
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

        $data = KasVendor::where('vendor_id', $request->vendor)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasVendor::where('vendor_id', $request->vendor)->whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();

        // $data = $vendor->kas_vendor()->get();

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

    public function preview_kas_vendor(Request $request)
    {
        $vendor = Vendor::find($request->vendor);

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasVendor::where('vendor_id', $request->vendor)->selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasVendor::where('vendor_id', $request->vendor)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasVendor::where('vendor_id', $request->vendor)->whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();

        $pdf = PDF::loadview('rekap.preview-kas-vendor', [
            'data' => $data,
            'vendor' => $vendor,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kas Vendor '.$vendor->nama." ".$stringBulanNow.' '.$tahun.'.pdf');
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
        $kasVendorLast = KasVendor::where('vendor_id', '=', $kas_vendor->vendor_id)->latest()->orderBy('id', 'desc')->first()->sisa;

        $kas_vendor->update([
            'void' => 1,
        ]);

        $data['vendor_id'] = $kas_vendor->vendor_id;
        $data['vehicle_id'] = $kas_vendor->vehicle_id;
        $data['bbm_storing_id'] = $kas_vendor->bbm_storing_id;
        $data['tanggal'] = date('Y-m-d');
        $data['uraian'] = 'Void '.$kas_vendor->uraian;
        $data['bayar'] = $kas_vendor->pinjaman;
        $data['sisa'] = $kasVendorLast - $data['bayar'];

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
        } else {
            $group = GroupWa::where('untuk', 'team')->first();
            $pesan ="==========================\n".
                    "*Form Void Jasa Mekanik*\n".
                    "==========================\n\n".
                    "No. Lambung : ".$kas_vendor->vehicle->nomor_lambung."\n".
                    "Vendor : ".$kas_vendor->vendor->nama."\n\n".
                    "Lokasi : ".$kas_vendor->bbm_storing->km."\n".
                    "Nilai :  *Rp. ".number_format($data['bayar'], 0, ',', '.')."*\n\n".
                    "==========================\n\n".
                    "Terima kasih 🙏🙏🙏\n";
            $send = new StarSender($group->nama_group, $pesan);
            $res = $send->sendGroup();
        }

        return redirect()->route('rekap.index')->with('success', 'Berhasil Void Kas Vendor!!');

    }

    public function kas_bon(Request $request)
    {
        // kas besar perbulan dan tahun, jika tidak ada request maka default bulan dan tahun saat ini
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasBon::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasBon::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasBon::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();
        // dd($bulan);
        return view('rekap.kas-bon', [
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

    public function preview_kas_bon(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasBon::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasBon::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasBon::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();

        $pdf = PDF::loadview('rekap.preview-kas-bon', [
            'data' => $data,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kasbon '.$stringBulanNow.' '.$tahun.'.pdf');
    }

    public function kas_bon_void(Request $request, KasBon $kas)
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

        $kasBesar = KasBesar::latest()->first();
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $k['uraian'] = 'Void Kasbon '.$kas->karyawan->nama;
        $k['tanggal'] = date('Y-m-d');
        $k['jenis_transaksi_id'] = 1;
        $k['nominal_transaksi'] = $kas->nominal;
        $k['saldo'] = $kasBesar->saldo + $kas->nominal;
        $k['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $k['no_rekening'] = $rekening->nomor_rekening;
        $k['bank'] = $rekening->nama_bank;
        $k['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;

        $store = KasBesar::create($k);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Form Void Kasbon Staff*\n".
                "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                "Nama : ".$kas->karyawan->nama."\n".
                "Uraian : ".$k['uraian']."\n".
                "Nilai :  *Rp. ".number_format($k['nominal_transaksi'], 0, ',', '.')."*\n\n".
                "Ditransfer ke rek:\n\n".
                "Bank     : ".$k['bank']."\n".
                "Nama    : ".$k['transfer_ke']."\n".
                "No. Rek : ".$k['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n";

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        $kas->delete();

        return redirect()->route('rekap.kas-bon')->with('success', 'Berhasil Void Kasbon!!');
    }

    public function kas_bon_direksi(Request $request)
    {
        $direksi = Direksi::find($request->direksi_id);

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasDireksi::where('direksi_id', $request->direksi_id)->selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasDireksi::where('direksi_id', $request->direksi_id)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasDireksi::where('direksi_id', $request->direksi_id)->whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->first();

        return view('rekap.kas-bon-direksi', [
            'data' => $data,
            'direksi' => $direksi,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }
}
