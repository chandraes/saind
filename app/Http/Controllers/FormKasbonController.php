<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KasBon;
use App\Models\KasBesar;
use App\Models\KasBonCicilan;
use App\Models\KasDireksi;
use App\Models\Direksi;
use App\Models\Rekening;
use App\Models\GroupWa;
use App\Models\PasswordKonfirmasi;
use App\Services\StarSender;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        $last = KasBesar::latest()->orderBy('id', 'desc')->first();

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
        $kas['transfer_ke'] = substr($d->nama_rekening, 0, 15);
        $kas['bank'] = $d->bank;
        $kas['no_rekening'] = $d->no_rekening;

        $store = KasDireksi::create($direksi);

        $kasBesar = KasBesar::create($kas);

        $dbWa = new GroupWa();

        $group = $dbWa->where('untuk', 'kas-besar')->first();

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

        $send = $dbWa->sendWa($group->nama_group, $pesan);

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

    public function direksi_bayar_store(Request $request, Direksi $direksi)
    {
        $data = $request->validate([
            'nominal' => 'required',
            'uraian' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $lastKasDireksi = KasDireksi::where('direksi_id', $direksi->id)->latest()->orderBy('id', 'desc')->first();

        if ($lastKasDireksi == null || $lastKasDireksi->sisa_kas < $data['nominal']) {
            return redirect()->back()->with('error', 'Pembayaran melebihi kasbon');
        }

        $d['tanggal'] = date('Y-m-d');
        $d['uraian'] = $data['uraian'];
        $d['total_bayar'] = $data['nominal'];
        $d['direksi_id'] = $direksi->id;
        $d['sisa_kas'] = $lastKasDireksi->sisa_kas - $data['nominal'];

        $lastKasBesar = KasBesar::latest()->orderBy('id', 'desc')->first();
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $kas['tanggal'] = $d['tanggal'];
        $kas['uraian'] = $d['uraian']." ".$direksi->nama;
        $kas['jenis_transaksi_id'] = 1;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $lastKasBesar->saldo + $data['nominal'];
        $kas['modal_investor_terakhir'] = $lastKasBesar->modal_investor_terakhir;
        $kas['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $kas['bank'] = $rekening->nama_bank;
        $kas['no_rekening'] = $rekening->nomor_rekening;

        KasDireksi::create($d);

        $store = KasBesar::create($kas);

        $dbWa = new GroupWa();

        $group = $dbWa->where('untuk', 'kas-besar')->first();
        $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Form Bayar Kasbon Direksi*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                "Nama : ".$direksi->nama."\n".
                "Uraian : ".$d['uraian']."\n\n".
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
                "Total Kasbon : \n".
                "Rp. ".number_format($d['sisa_kas'], 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Pembayaran berhasil disimpan!!');

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

        $last = KasBesar::latest()->orderBy('id', 'desc')->first();

        if ($last == null || $last->saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $kas['tanggal'] = $data['tanggal'];
        $kas['uraian'] = "Kasbon ".$karyawan->nama;
        $kas['jenis_transaksi_id'] = 2;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $last->saldo - $data['nominal'];
        $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;
        $kas['transfer_ke'] = substr($karyawan->nama_rekening, 0, 15);
        $kas['bank'] = $karyawan->bank;
        $kas['no_rekening'] = $karyawan->no_rekening;

        KasBon::create($data);

        $store = KasBesar::create($kas);

        $dbWa = new GroupWa();

        $group = $dbWa->where('untuk', 'kas-besar')->first();

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

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Kasbon berhasil ditambahkan');
    }

    public function kas_bon_cicil()
    {
        $kasbon = KasBonCicilan::where('lunas', 0)->pluck('karyawan_id')->unique()->toArray();

        // data where not in
        $data = Karyawan::whereNotIn('id', $kasbon)->where('status', 'aktif')->get();

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

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        $kasBesar = KasBesar::latest()->orderBy('id', 'desc')->first();

        if ($kasBesar == null || $kasBesar->saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $karyawan = Karyawan::find($data['karyawan_id']);


        $data['tanggal'] = date('Y-m-d');
        $data['sisa_kas'] = $data['nominal'];
        $data['cicilan_nominal'] = $data['nominal'] / $data['cicil_kali'];

        $gapok = $karyawan->gaji_pokok * 0.3;

        if ($gapok < $data['cicilan_nominal']) {
            return redirect()->back()->with('error', 'Cicilan melebihi 30% dari Gaji Pokok!!');
        }

        KasBonCicilan::create($data);

        $k['tanggal'] = $data['tanggal'];
        $k['uraian'] = "Kasbon Cicilan ".$karyawan->nama;
        $k['jenis_transaksi_id'] = 2;
        $k['nominal_transaksi'] = $data['nominal'];
        $k['saldo'] = $kasBesar->saldo - $data['nominal'];
        $k['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
        $k['transfer_ke'] = substr($karyawan->nama_rekening, 0, 15);
        $k['bank'] = $karyawan->bank;
        $k['no_rekening'] = $karyawan->no_rekening;

        $store = KasBesar::create($k);

        $dbWa = new GroupWa();

        $group = $dbWa->where('untuk', 'kas-besar')->first();

        // buat nama bulan dari $data['mulai_bulan'] dengan bahasa indonesia
        $bulan = Carbon::createFromDate($data['mulai_tahun'], $data['mulai_bulan'], 1)->locale('id')->monthName;

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Kasbon Staff*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nama : ".$karyawan->nama."\n".
                    "Uraian : Cicilan ".$data['cicil_kali']."X\n".
                    "Mulai : ".$bulan." ".$data['mulai_tahun']."\n\n".
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

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Kasbon berhasil ditambahkan');
    }

    public function kas_bon_cicil_void(KasBonCicilan $kas, Request $request)
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

        $dbKas = new KasBesar;

        $sisa = $kas->sisa_kas;
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $k['uraian'] = 'Void Kasbon '.$kas->karyawan->nama;
        $k['tanggal'] = date('Y-m-d');
        $k['jenis_transaksi_id'] = 1;
        $k['nominal_transaksi'] = $sisa;
        $k['saldo'] = $dbKas->saldoTerakhir() + $sisa;
        $k['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $k['no_rekening'] = $rekening->nomor_rekening;
        $k['bank'] = $rekening->nama_bank;
        $k['modal_investor_terakhir'] = $dbKas->modalInvestorTerakhir();

        // dd($kas);

        try {



            DB::beginTransaction();

            $store = KasBesar::create($k);

            $kas->update([
                'total_bayar' => $kas->total_bayar + $sisa,
                'sisa_kas' => 0,
                'lunas' => 1
            ]);


            DB::commit();

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

            $dbKas->sendWa($group->nama_group, $pesan);

        } catch (\Throwable $th) {

            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus kasbon cicilan');
        }

        return redirect()->back()->with('success', 'Kasbon cicilan berhasil dihapus');
    }
}
