<?php

namespace App\Http\Controllers;

use App\Models\KasKecil;
use App\Models\KasBesar;
use App\Models\Vendor;
use App\Models\Direksi;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use App\Models\KasDireksi;
use App\Models\KasUangJalan;
use App\Models\InvoiceBonus;
use App\Models\InvoiceBayar;
use App\Models\Customer;
use App\Models\InvoiceTagihan;
use App\Models\KasVendor;
use App\Models\Transaksi;
use App\Models\Rekening;
use App\Models\RekapGaji;
use App\Models\Sponsor;
use App\Models\GroupWa;
use App\Services\StarSender;
use App\Models\PasswordKonfirmasi;
use App\Models\RekapBarang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

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
        $dataSebelumnya = KasKecil::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();
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
        $dataSebelumnya = KasKecil::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();
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
        $dataSebelumnya = KasUangJalan::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();

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
        $dataSebelumnya = KasUangJalan::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();
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
        $dataSebelumnya = RekapBarang::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();
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
        $dataSebelumnya = KasVendor::where('vendor_id', $request->vendor)->whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();

        // $data = $vendor->kas_vendor()->get();
        $sisaTerakhir = $data->last()->sisa ?? 0;

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
            'sisaTerakhir' => $sisaTerakhir,
        ]);
    }

    public function kas_vendor_detail(InvoiceBayar $invoiceBayar)
    {
        $periode = $invoiceBayar->periode;
        $vendor = Vendor::find($invoiceBayar->vendor_id);
        // dd($invoiceBayar);

        return view('rekap.kas-vendor-detail', [
            'data' => $invoiceBayar->transaksi,
            'vendor' => $vendor,
            'periode' => $periode,
            'invoice_id' => $invoiceBayar->id
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
        $dataSebelumnya = KasVendor::where('vendor_id', $request->vendor)->whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();
        $sisaTerakhir = $data->last()->sisa ?? 0;
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
            'sisaTerakhir' => $sisaTerakhir,
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
        $dataCicilan = KasBonCicilan::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasBon::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();
        // dd($bulan);
        return view('rekap.kas-bon', [
            'data' => $data,
            'dataCicilan' => $dataCicilan,
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
        $dataSebelumnya = KasBon::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();

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
                "Uraian : ".$k['uraian']."\n\n".
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
        $dataSebelumnya = KasDireksi::where('direksi_id', $request->direksi_id)->whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();

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

    public function rekap_bonus(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = InvoiceBonus::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = InvoiceBonus::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('lunas', 1)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = InvoiceBonus::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();
        // dd($bulan);
        return view('rekap.rekap-bonus', [
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

    public function rekap_bonus_detail(InvoiceBonus $invoiceBonus)
    {
        $periode = $invoiceBonus->periode;
        $sponsor = Sponsor::find($invoiceBonus->sponsor_id);
        // dd($invoiceBayar);

        return view('rekap.rekap-bonus-detail', [
            'data' => $invoiceBonus->transaksi,
            'sponsor' => $sponsor,
            'periode' => $periode,
        ]);
    }

    public function nota_lunas(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = InvoiceTagihan::selectRaw('YEAR(updated_at) tahun')->groupBy('tahun')->get();

        $data = InvoiceTagihan::whereMonth('updated_at', $bulan)->whereYear('updated_at', $tahun)->where('lunas', 1)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = InvoiceTagihan::whereMonth('updated_at', $bulanSebelumnya)->whereYear('updated_at', $tahun)->latest()->orderBy('id', 'desc')->first();
        // dd($bulan);
        return view('rekap.nota-lunas', [
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

    public function nota_lunas_detail(InvoiceTagihan $invoice)
    {
        $periode = $invoice->periode;
        $customer = Customer::find($invoice->customer_id);

        return view('rekap.nota-lunas-detail', [
            'data' => $invoice->transaksi,
            'customer' => $customer,
            'periode' => $periode,
            'invoice_id' => $invoice->id
        ]);
    }

    public function rekap_gaji_detail(Request $request)
    {
        $v = $request->validate([
                'bulan' => 'required',
                'tahun' => 'required',
            ]);

        $data = RekapGaji::where('bulan', $v['bulan'])->where('tahun', $v['tahun'])->first();

        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan!!');
        }

        $bulan = Carbon::createFromDate($v['tahun'], $v['bulan'])->locale('id')->monthName;
        $tahun = $v['tahun'];

        return view('rekap.gaji-detail', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_angka' => $v['bulan'],
        ]);
    }

    public function print_rekap_gaji(Request $request)
    {
        $v = $request->validate([
                'bulan' => 'required',
                'tahun' => 'required',
            ]);

        $data = RekapGaji::where('bulan', $v['bulan'])->where('tahun', $v['tahun'])->first();

        $bulan = Carbon::createFromDate($v['tahun'], $v['bulan'])->locale('id')->monthName;
        $tahun = $v['tahun'];

        $pdf = PDF::loadview('rekap.print-rekap-gaji', [
            'data' => $data->rekap_gaji_detail,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kasbon '.$bulan.' '.$tahun.'.pdf');
    }

    public function kas_per_vendor(Request $request, Vendor $vendor)
    {
        if($vendor->id != auth()->user()->vendor_id){
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini!!');
        }
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasVendor::where('vendor_id', $vendor->id)->selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasVendor::where('vendor_id', $vendor->id)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasVendor::where('vendor_id', $vendor->id)->whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();

        // $data = $vendor->kas_vendor()->get();
        $sisaTerakhir = $data->last()->sisa ?? 0;

        return view('rekap.kas-per-vendor', [
            'data' => $data,
            'vendor' => $vendor,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
            'sisaTerakhir' => $sisaTerakhir,
        ]);
    }

    public function kas_per_vendor_detail(Request $request, InvoiceBayar $invoiceBayar)
    {
        $vendor = Vendor::find($invoiceBayar->vendor_id);
        
        if ($vendor->id != auth()->user()->vendor_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini!!');
        }

        $periode = $invoiceBayar->periode;

        return view('rekap.kas-per-vendor-detail', [
            'data' => $invoiceBayar->transaksi,
            'vendor' => $vendor,
            'periode' => $periode,
            'invoice_id' => $invoiceBayar->id
        ]);
    }

    public function print_kas_per_vendor(Request $request)
    {
        $vendor = Vendor::find($request->vendor);

        if($vendor->id != auth()->user()->vendor_id){
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini!!');
        }

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
        $dataSebelumnya = KasVendor::where('vendor_id', $request->vendor)->whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahun)->latest()->orderBy('id', 'desc')->first();

        $sisaTerakhir = $data->last()->sisa ?? 0;

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
            'sisaTerakhir' => $sisaTerakhir,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kas Vendor '.$vendor->nama." ".$stringBulanNow.' '.$tahun.'.pdf');
    }

}
