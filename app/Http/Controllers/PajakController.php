<?php

namespace App\Http\Controllers;

use App\Models\Pajak\PphSimpan;
use App\Models\Pajak\RekapKeluaranDetail;
use App\Models\Pajak\RekapMasukanDetail;
use App\Models\Pajak\RekapPpn;
use App\Models\Pajak\PpnKeluaran;
use App\Models\Pajak\PpnMasukan;
use App\Models\Pajak\RekapPphVendor;
use App\Models\transaksi\InventarisInvoice;
use App\Models\transaksi\InvoiceBelanja;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PajakController extends Controller
{
    public function index()
    {
        return view('pajak.index');
    }

    public function ppn_masukan()
    {
        $db = new PpnMasukan();

        $data = $db->with(['invoiceBayar.vendor'])->where('keranjang', 0)->where('onhold', 0)->where('selesai', 0)->get();
        $keranjang = $db->with(['invoiceBayar.vendor'])->where('keranjang', 1)->where('onhold', 0)->where('selesai', 0)->count();
        $keranjangData = $db->with(['invoiceBayar.vendor'])->where('keranjang', 1)->where('onhold', 0)->where('selesai', 0)->get();

        $total_faktur = 0;
        $total_blm_faktur = 0;

        foreach ($data as $item) {
            if ($item->is_faktur == 1) {
                $total_faktur += $item->nominal;
            } elseif ($item->is_faktur == 0) {
                $total_blm_faktur += $item->nominal;
            }
        }

        return view('pajak.ppn-masukan.index', [
            'data' => $data,
            'total_faktur' => $total_faktur,
            'total_blm_faktur' => $total_blm_faktur,
            'keranjang' => $keranjang,
            'keranjangData' => $keranjangData
        ]);
    }

    public function ppn_masukan_store_faktur(Request $request, PpnMasukan $ppnMasukan)
    {
        $data = $request->validate([
            'no_faktur' => 'required',
        ]);

        $ppnMasukan->update([
            'is_faktur' => 1,
            'no_faktur' => $data['no_faktur']
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');
    }

    public function ppn_masukan_keranjang_store(Request $request)
    {
        $data = $request->validate([
            'selectedData' => 'required',
        ]);

        $data['selectedData'] = trim($data['selectedData'], ',');
        $data['selectedData'] = explode(',', $data['selectedData']);

        $db = new PpnMasukan();

        $db->whereIn('id', $data['selectedData'])->update([
            'keranjang' => 1
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');


    }

    public function ppn_masukan_keranjang_destroy(PPnMasukan $ppnMasukan)
    {
        $ppnMasukan->update([
            'keranjang' => 0
        ]);

        return redirect()->back()->with('success', 'Berhasil menghapus data dari keranjang!');
    }

    public function ppn_masukan_keranjang_lanjut(Request $request)
    {

        $data = $request->validate([
            'penyesuaian' => 'required',
        ]);

        $db = new RekapPpn();
        $penyesuaian = str_replace('.', '', $data['penyesuaian']);
        $res = $db->keranjang_masukan_lanjut($penyesuaian);

        return redirect()->back()->with($res['status'], $res['message']);

    }

    public function ppn_keluaran(Request $request)
    {
        $db = new PpnKeluaran();

        $data = $db->with('invoiceTagihan.customer')->where('keranjang', 0)->where('is_expired', 0)->where('onhold', 0)->where('selesai', 0)->get();
        $keranjang = $db->with('invoiceTagihan.customer')->where('keranjang', 1)->where('onhold', 0)->where('selesai', 0)->count();
        $keranjangData = $db->with('invoiceTagihan.customer')->where('keranjang', 1)->where('onhold', 0)->where('selesai', 0)->get();


        return view('pajak.ppn-keluaran.index', [
            'data' => $data,
            'keranjang' => $keranjang,
            'keranjangData' => $keranjangData
        ]);

    }

    public function ppn_keluaran_store_faktur(Request $request, PpnKeluaran $ppnKeluaran)
    {
        $data = $request->validate([
            'no_faktur' => 'required',
        ]);

        $ppnKeluaran->update([
            'is_faktur' => 1,
            'no_faktur' => $data['no_faktur']
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');
    }

    public function ppn_keluaran_keranjang_store(Request $request)
    {
        $data = $request->validate([
            'selectedData' => 'required',
        ]);

        $data['selectedData'] = trim($data['selectedData'], ',');
        $data['selectedData'] = explode(',', $data['selectedData']);

        $db = new PpnKeluaran();

        $db->whereIn('id', $data['selectedData'])->update([
            'keranjang' => 1
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');
    }

    public function ppn_keluaran_keranjang_destroy(PpnKeluaran $ppnKeluaran)
    {
        $ppnKeluaran->update([
            'keranjang' => 0
        ]);

        return redirect()->back()->with('success', 'Berhasil menghapus data dari keranjang!');
    }

    public function ppn_keluaran_keranjang()
    {
        $db = new PpnKeluaran();
        $data = $db->with('invoiceTagihan.customer')->where('keranjang', 1)->where('onhold', 0)->where('selesai', 0)->get();
        $dbRekap = new RekapPpn();
        $saldoMasukan = $dbRekap->saldoTerakhir();

        $dariKas = 0;

        if (($saldoMasukan - $data->where('dipungut', 1)->sum('nominal')) < 0) {
            $dariKas = abs($saldoMasukan - $data->where('dipungut', 1)->sum('nominal'));
        }

        $dariKas = number_format($dariKas, 0, ',', '.');
        return view('pajak.ppn-keluaran.keranjang', [
            'data' => $data,
            'saldoMasukan' => $saldoMasukan,
            'dariKas' => $dariKas
        ]);
    }

    public function ppn_keluaran_keranjang_lanjut(Request $request)
    {
        $data = $request->validate([
            'penyesuaian' => 'required',
        ]);

        $penyesuaian = str_replace('.', '', $data['penyesuaian']);

        $db = new RekapPpn();

        $res = $db->keranjang_keluaran_lanjut($penyesuaian);

        return redirect()->route('pajak.ppn-keluaran')->with($res['status'], $res['message']);
    }

    public function ppn_keluaran_expired(PpnKeluaran $ppnKeluaran)
    {
        $ppnKeluaran->update([
            'is_expired' => 1
        ]);

        return redirect()->back()->with('success', 'Berhasil mengubah status menjadi expired!');
    }

    public function rekap_ppn(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new RekapPpn();

        $data = $db->rekapByMonth($bulan, $tahun);
        $dataTahun = $db->dataTahun();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        $dataSebelumnya = $db->rekapByMonthSebelumnya($bulanSebelumnya, $tahunSebelumnya);

        return view('pajak.rekap-ppn.index', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'stringBulanNow' => $stringBulanNow,
            'bulanSebelumnya' => $bulanSebelumnya,
            'tahunSebelumnya' => $tahunSebelumnya
        ]);
    }

    public function rekap_ppn_masukan_detail(RekapPpn $rekapPpn)
    {
        $masukan_id = $rekapPpn->masukan_id;
        $dataMasukan = RekapMasukanDetail::where('masukan_id', $masukan_id)->pluck('ppn_masukan_id');
        // dd($dataMasukan);
        $db = new PpnMasukan();
        $data = $db->with(['invoiceBayar.vendor'])->whereIn('id', $dataMasukan)->get();

        return view('pajak.rekap-ppn.masukan-detail', [
            'data' => $data,
            'rekapPpn' => $rekapPpn
        ]);
    }

    public function rekap_ppn_keluaran_Detail(RekapPpn $rekapPpn)
    {
        $keluaran_id = $rekapPpn->keluaran_id;
        $dataKeluaran = RekapKeluaranDetail::where('keluaran_id', $keluaran_id)->pluck('ppn_keluaran_id');

        $db = new PpnKeluaran();
        $data = $db->with(['invoiceTagihan.customer'])->whereIn('id', $dataKeluaran)->get();

        return view('pajak.rekap-ppn.keluaran-detail', [
            'data' => $data,
            'rekapPpn' => $rekapPpn
        ]);
    }

    public function ppn_expired()
    {
        $db = new PpnKeluaran();
        $data = $db->with(['invoiceTagihan.customer'])->where('is_expired', 1)->get();

        return view('pajak.ppn-expired.index', [
            'data' => $data
        ]);
    }

    public function ppn_expired_back(PpnKeluaran $ppnKeluaran)
    {
        $ppnKeluaran->update([
            'is_expired' => 0
        ]);

        return redirect()->back()->with('success', 'Berhasil mengubah status menjadi tidak expired!');
    }

    public function pph_vendor()
    {
         $db = new PphSimpan();

        $data = $db->with('invoice.vendor')->where('keranjang', 0)->where('onhold', 0)->where('selesai', 0)->get();
        $keranjang = $db->with('invoice.vendor')->where('keranjang', 1)->where('onhold', 0)->where('selesai', 0)->count();
        $keranjangData = $db->with('invoice.vendor')->where('keranjang', 1)->where('onhold', 0)->where('selesai', 0)->get();

        return view('pajak.pph-vendor.index', [
            'data' => $data,
            'keranjang' => $keranjang,
            'keranjangData' => $keranjangData
        ]);
    }

    public function pph_vendor_store_faktur(Request $request, PphSimpan $pphVendor)
    {
        $data = $request->validate([
            'no_faktur' => 'required',
        ]);

        $pphVendor->update([
            'is_faktur' => 1,
            'no_faktur' => $data['no_faktur']
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');
    }

    public function pph_vendor_keranjang_store(Request $request)
    {
         $data = $request->validate([
            'selectedData' => 'required',
        ]);

        $data['selectedData'] = trim($data['selectedData'], ',');
        $data['selectedData'] = explode(',', $data['selectedData']);

        $db = new PphSimpan();

        $db->whereIn('id', $data['selectedData'])->update([
            'keranjang' => 1
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');
    }

    public function pph_vendor_keranjang_destroy(PphSimpan $pphVendor)
    {
        $pphVendor->update([
            'keranjang' => 0
        ]);

        return redirect()->back()->with('success', 'Berhasil menghapus data dari keranjang!');
    }

    public function pph_vendor_keranjang_lanjut(Request $request)
    {
         $data = $request->validate([
            'penyesuaian' => 'required',
            'uraian' => 'required',
        ]);

        $data['penyesuaian'] = str_replace('.', '', $data['penyesuaian']);

        $db = new RekapPphVendor();

        $res = $db->keranjang_pph_vendor_lanjut($data);

        return redirect()->route('pajak.pph-vendor')->with($res['status'], $res['message']);
    }

    public function rekap_pph_vendor(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new RekapPphVendor();

        $data = $db->rekapByMonth($bulan, $tahun);
        $dataTahun = $db->dataTahun();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;


        return view('pajak.rekap-pph-vendor.index', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dataTahun' => $dataTahun,
            'stringBulan' => $stringBulan,
            'stringBulanNow' => $stringBulanNow,
            'bulanSebelumnya' => $bulanSebelumnya,
            'tahunSebelumnya' => $tahunSebelumnya
        ]);
    }
}
