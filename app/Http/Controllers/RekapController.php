<?php

namespace App\Http\Controllers;

use App\Models\KasKecil;
use App\Models\KasBesar;
use App\Models\Vendor;
use App\Models\KasUangJalan;
use App\Models\KasVendor;
use App\Models\Transaksi;
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
}
