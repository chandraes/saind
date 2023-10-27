<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KasBon;
use App\Models\KasBesar;
use App\Models\KasDireksi;
use App\Models\Direksi;
use App\Models\Rekening;
use App\Models\GroupWa;
use App\Services\StarSender;
use Illuminate\Http\Request;

class FormKasbonController extends Controller
{
    public function index()
    {
        $karyawan = Karyawan::where('status', 'aktif')->get();

        return view('billing.kasbon.index', [
            'karyawan' => $karyawan,
        ]);
    }

    public function direksi_kas()
    {
        $data = Direksi::all();

        return view('billing.kasbon.direksi.direksi-kas', [
            'data' => $data,
        ]);
    }

    public function direksi_kas_store(Request $request)
    {
        $data = $request->validate([
            'direksi_id' => 'required',
            'uraian' => 'required',
            'nominal' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        $d = Direksi::find($data['direksi_id']);

        $last = KasBesar::latest()->first();

        if ($last == null || $last->saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $direksi['tanggal'] = date('Y-m-d');
        $direksi['uraian'] = $data['uraian'];
        $direksi['total_kas'] = $data['nominal'];
        $direksi['direksi_id'] = $data['direksi_id'];

        $lastKasDireksi = KasDireksi::where('direksi_id', $data['direksi_id'])->latest()->orderBy('id', 'desc')->first();

        if ($lastKasDireksi == null) {
            $direksi['sisa_kas'] = $data['nominal'];
        } else {
            $direksi['sisa_kas'] = $lastKasDireksi->sisa_kas + $data['nominal'];
        }

        $kas['tanggal'] = $direksi['tanggal'];
        $kas['uraian'] = $direksi['uraian'];
        $kas['jenis_transaksi_id'] = 2;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $last->saldo - $data['nominal'];
        $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;
        $kas['transfer_ke'] = $d->nama_rekening;
        $kas['bank'] = $d->bank;
        $kas['no_rekening'] = $d->no_rekening;

        $store = KasDireksi::create($direksi);

        $kasBesar = KasBesar::create($kas);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Kasbon Direksi*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nama : ".$d->nama."\n".
                    "Uraian : ".$kas['uraian']."\n\n".
                    "Nilai :  *Rp. ".number_format($kas['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$kas['bank']."\n".
                    "Nama    : ".$kas['transfer_ke']."\n".
                    "No. Rek : ".$kas['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($kasBesar->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($kasBesar->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Total Kasbon : \n".
                    "Rp. ".number_format($store->sisa_kas, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Kasbon berhasil ditambahkan');

    }

    public function direksi_bayar()
    {
        $data = Direksi::where('status', 'aktif')->get();

        return view('billing.kasbon.direksi.direksi-bayar', [
            'data' => $data,
        ]);
    }

    public function direksi_bayar_list(Request $request)
    {
        $data = $request->validate([
            'direksi_id' => 'required',
        ]);

        $direksi = Direksi::find($data['direksi_id']);

        return view('billing.kasbon.direksi.direksi-bayar-list', [
            'direksi' => $direksi,
        ]);
    }

    public function direksi_bayar_lunas(KasDireksi $kas)
    {
        $rekening = Rekening::where('untuk', 'kas-besar')->first();
        $kasBesar = KasBesar::latest()->first();

        $data['tanggal'] = date('Y-m-d');
        $data['uraian'] = "Pelunasan ".$kas->uraian. " ".$kas->direksi->nama;
        $data['jenis_transaksi_id'] = 1;
        $data['nominal_transaksi'] = $kas->sisa_kas;
        $data['saldo'] = $kasBesar->saldo + $data['nominal_transaksi'];
        $data['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
        $data['transfer_ke'] = $rekening->nama_rekening;
        $data['bank'] = $rekening->nama_bank;
        $data['no_rekening'] = $rekening->nomor_rekening;

        $store = KasBesar::create($data);

        if ($kas->sisa_kas != $kas->total_kas) {
            $total_bayar = $kas->total_bayar + $kas->sisa_kas;
        } else {
            $total_bayar = $kas->sisa_kas;
        }

        $kas->update([
            'lunas' => 1,
            'sisa_kas' => 0,
            'total_bayar' => $total_bayar,
        ]);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Form Pelunasan Kasbon Direksi*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                "Nama : ".$kas->direksi->nama."\n".
                "Uraian : ".$data['uraian']."\n\n".
                "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                "Ditransfer ke rek:\n\n".
                "Bank     : ".$data['bank']."\n".
                "Nama    : ".$data['transfer_ke']."\n".
                "No. Rek : ".$data['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Kasbon direksi berhasil dilunasi');
    }

    public function direksi_bayar_cicil(Request $request, KasDireksi $kas)
    {
        $k = $request->validate([
            'cicilan' => 'required',
        ]);

        $k['cicilan'] = str_replace('.', '', $k['cicilan']);

        if (($k['cicilan']+$kas->total_bayar) > $kas->total_kas) {
            return redirect()->back()->with('error', 'Cicilan melebihi total kasbon');
        }

        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $kasBesar = KasBesar::latest()->first();

        $data['tanggal'] = date('Y-m-d');
        $data['uraian'] = "Cicilan ".$kas->uraian." ".$kas->direksi->nama;
        $data['jenis_transaksi_id'] = 1;
        $data['nominal_transaksi'] = $k['cicilan'];
        $data['saldo'] = $kasBesar->saldo + $data['nominal_transaksi'];
        $data['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
        $data['transfer_ke'] = $rekening->nama_rekening;
        $data['bank'] = $rekening->nama_bank;
        $data['no_rekening'] = $rekening->nomor_rekening;

        $store = KasBesar::create($data);

        $total_bayar = $kas->total_bayar + $k['cicilan'];

        $sisa_kas = $kas->sisa_kas - $k['cicilan'];

        if ($sisa_kas == 0) {
            $kas->update([
                'sisa_kas' => $sisa_kas,
                'total_bayar' => $total_bayar,
                'lunas' => 1,
            ]);
        } else {
            $kas->update([
                'sisa_kas' => $sisa_kas,
                'total_bayar' => $total_bayar,
            ]);
        }

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Form Cicilan Kasbon Direksi*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                "Nama : ".$kas->direksi->nama."\n".
                "Uraian : ".$data['uraian']."\n\n".
                "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                "Ditransfer ke rek:\n\n".
                "Bank     : ".$data['bank']."\n".
                "Nama    : ".$data['transfer_ke']."\n".
                "No. Rek : ".$data['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'karyawan_id' => 'required',
            'nominal' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['tanggal'] = date('Y-m-d');
        $data['sisa_kas'] = $data['nominal'];

        $kasbon = KasBon::where('karyawan_id', $data['karyawan_id'])->where('lunas', 0)->sum('nominal');

        $karyawan = Karyawan::find($data['karyawan_id']);

        $gapok = $karyawan->gaji_pokok * 0.5;

        if ($gapok < $data['nominal'] || $gapok < ($kasbon+$data['nominal'])) {
            return redirect()->back()->with('error', 'Kasbon sudah melebihi ketentuan!!');
        }

        $last = KasBesar::latest()->first();

        if ($last == null || $last->saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $kas['tanggal'] = $data['tanggal'];
        $kas['uraian'] = "Kasbon ".$karyawan->nama;
        $kas['jenis_transaksi_id'] = 2;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $last->saldo - $data['nominal'];
        $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;
        $kas['transfer_ke'] = $karyawan->nama_rekening;
        $kas['bank'] = $karyawan->bank;
        $kas['no_rekening'] = $karyawan->no_rekening;

        KasBon::create($data);

        $store = KasBesar::create($kas);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Kasbon Staff*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nama : ".$karyawan->nama."\n".
                    "Uraian : Potong Gaji\n\n".
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


        return redirect()->route('billing.index')->with('success', 'Kasbon berhasil ditambahkan');
    }

    public function kas_bon_cicil()
    {
        $data = Karyawan::where('status', 'aktif')->get();

        return view('billing.kasbon.kas-bon-cicil', [
            'karyawan' => $data,
        ]);
    }

    public function kas_bon_cicil_store(Request $request)
    {
        $data = $request->validate([
            'karyawan_id' => 'required',
            'nominal' => 'required',
            'cicil_kali' => 'required|integer',
            'mulai_bulan' => 'required|integer|between:1,12',
            'mulai_tahun' => 'required|integer',
        ]);

        $kasBesar = KasBesar::latest()->first();

        if ($kasBesar == null || $kasBesar->saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $karyawan = Karyawan::find($data['karyawan_id']);

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['tanggal'] = date('Y-m-d');
        $data['sisa_kas'] = $data['nominal'];
        $data['cicilan'] = 1;
        $data['cicilan_nominal'] = $data['nominal'] / $data['cicil_kali'];

        KasBon::create($data);

        $k['tanggal'] = $data['tanggal'];
        $k['uraian'] = "Kasbon Cicilan ".$karyawan->nama;
        $k['jenis_transaksi_id'] = 2;
        $k['nominal_transaksi'] = $data['nominal'];
        $k['saldo'] = $kasBesar->saldo - $data['nominal'];
        $k['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
        $k['transfer_ke'] = $karyawan->nama_rekening;
        $k['bank'] = $karyawan->bank;
        $k['no_rekening'] = $karyawan->no_rekening;

        $store = KasBesar::create($k);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Kasbon Karyawan Cicilan*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nama : ".$karyawan->nama."\n".
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

        return redirect()->route('billing.index')->with('success', 'Kasbon berhasil ditambahkan');
    }
}
