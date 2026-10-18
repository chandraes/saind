<?php

namespace App\Http\Controllers;

use App\Models\Direksi;
use App\Models\GroupWa;
use App\Models\Karyawan;
use App\Models\KasBesar;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use App\Models\KasDireksi;
use App\Models\PasswordKonfirmasi;
use App\Models\Rekening;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FormKasbonController extends Controller
{
    public function index()
    {
        if (Carbon::now()->day <= 15) {
            // Kamu bisa arahkan ke halaman lain dengan pesan error
            return redirect()->back()->with('error', 'Form Kasbon potong gaji hanya bisa dilakukan setelah tanggal 15.');

            // Atau tampilkan halaman error 403 (Forbidden)
            // abort(403, 'Akses belum diizinkan sebelum tanggal 16.');
        }

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

        $dbWa = new GroupWa;

        $group = $dbWa->where('untuk', 'kas-besar')->first();

        $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Kasbon Direksi*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    'Nama : '.$d->nama."\n".
                    'Uraian : '.$kas['uraian']."\n\n".
                    'Nilai :  *Rp. '.number_format($kas['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    'Bank     : '.$kas['bank']."\n".
                    'Nama    : '.$kas['transfer_ke']."\n".
                    'No. Rek : '.$kas['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    'Rp. '.number_format($kasBesar->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    'Rp. '.number_format($kasBesar->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Total Kasbon : \n".
                    'Rp. '.number_format($store->sisa_kas, 0, ',', '.')."\n\n".
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
        $kas['uraian'] = $d['uraian'].' '.$direksi->nama;
        $kas['jenis_transaksi_id'] = 1;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $lastKasBesar->saldo + $data['nominal'];
        $kas['modal_investor_terakhir'] = $lastKasBesar->modal_investor_terakhir;
        $kas['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $kas['bank'] = $rekening->nama_bank;
        $kas['no_rekening'] = $rekening->nomor_rekening;

        KasDireksi::create($d);

        $store = KasBesar::create($kas);

        $dbWa = new GroupWa;

        $group = $dbWa->where('untuk', 'kas-besar')->first();
        $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Form Bayar Kasbon Direksi*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                'Nama : '.$direksi->nama."\n".
                'Uraian : '.$d['uraian']."\n\n".
                'Nilai :  *Rp. '.number_format($kas['nominal_transaksi'], 0, ',', '.')."*\n\n".
                "Ditransfer ke rek:\n\n".
                'Bank     : '.$kas['bank']."\n".
                'Nama    : '.$kas['transfer_ke']."\n".
                'No. Rek : '.$kas['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                'Rp. '.number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                'Rp. '.number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Total Kasbon : \n".
                'Rp. '.number_format($d['sisa_kas'], 0, ',', '.')."\n\n".
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

        $totalPendapatan = $karyawan->gaji_pokok + ($karyawan->tunjangan_jabatan ?? 0) + ($karyawan->tunjangan_keluarga ?? 0);
        $batasKasbon = $totalPendapatan * 0.5;

        if ($batasKasbon < $data['nominal'] || $batasKasbon < ($kasbon + $data['nominal'])) {
            return redirect()->back()->with('error', 'Kasbon sudah melebihi ketentuan!!');
        }

        $last = KasBesar::latest()->orderBy('id', 'desc')->first();

        if ($last == null || $last->saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $kas['tanggal'] = $data['tanggal'];
        $kas['uraian'] = 'Kasbon '.$karyawan->nama;
        $kas['jenis_transaksi_id'] = 2;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $last->saldo - $data['nominal'];
        $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;
        $kas['transfer_ke'] = substr($karyawan->nama_rekening, 0, 15);
        $kas['bank'] = $karyawan->bank;
        $kas['no_rekening'] = $karyawan->no_rekening;

        try {
            $store = DB::transaction(function () use ($data, $kas): KasBesar {
                KasBon::create($data);

                return KasBesar::create($kas);
            });
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->back()->withInput()->with('error', 'Kasbon gagal disimpan. Tidak ada perubahan data; silakan coba kembali.');
        }

        $dbWa = new GroupWa;

        $group = $dbWa->where('untuk', 'kas-besar')->first();

        $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Kasbon Staff*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    'Nama : '.$karyawan->nama."\n".
                    "Uraian : Potong Gaji\n\n".
                    'Nilai :  *Rp. '.number_format($kas['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    'Bank     : '.$kas['bank']."\n".
                    'Nama    : '.$kas['transfer_ke']."\n".
                    'No. Rek : '.$kas['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    'Rp. '.number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    'Rp. '.number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Kasbon berhasil ditambahkan');
    }

    public function kas_bon_cicil()
    {

        if (Auth::user()->role != 'admin' && Auth::user()->role != 'su') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke menu ini!!');
        }

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
            'karyawan_id' => ['required', 'integer', 'exists:karyawans,id'],
            'nominal' => 'required',
            'cicil_kali' => 'required|integer|min:1',
            'mulai_bulan' => 'required|integer|between:1,12',
            'mulai_tahun' => 'required|integer',
        ], [
            'cicil_kali.min' => 'Jumlah cicilan minimal 1 kali.',
        ]);

        if (Auth::user()->role != 'admin' && Auth::user()->role != 'su') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke menu ini!!');
        }

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        try {
            $result = DB::transaction(function () use ($data): array {
                $karyawan = Karyawan::whereKey($data['karyawan_id'])->lockForUpdate()->firstOrFail();
                if ($karyawan->status !== 'aktif') {
                    throw ValidationException::withMessages(['karyawan_id' => 'Kasbon cicilan hanya dapat diajukan untuk pegawai aktif.']);
                }
                $existing = KasBonCicilan::where('karyawan_id', $karyawan->id)->where('lunas', false)->lockForUpdate()->first();
                if ($existing) {
                    throw ValidationException::withMessages(['karyawan_id' => 'Pegawai masih memiliki kasbon cicilan yang belum lunas. Lunasi terlebih dahulu sebelum membuat pinjaman baru.']);
                }
                $kasBesar = KasBesar::latest('id')->lockForUpdate()->first();
                if (! $kasBesar || $kasBesar->saldo < $data['nominal']) {
                    throw ValidationException::withMessages(['nominal' => 'Saldo Kas Besar tidak mencukupi']);
                }
                $data['tanggal'] = now()->toDateString();
                $data['sisa_kas'] = $data['nominal'];
                $data['cicilan_nominal'] = $data['nominal'] / $data['cicil_kali'];
                $totalPendapatan = $karyawan->gaji_pokok + ($karyawan->tunjangan_jabatan ?? 0) + ($karyawan->tunjangan_keluarga ?? 0);
                if ($totalPendapatan * 0.3 < $data['cicilan_nominal']) {
                    throw ValidationException::withMessages(['nominal' => 'Cicilan melebihi 30% dari total Gaji Pokok, Tunjangan Jabatan, dan Tunjangan Keluarga!!']);
                }
                $k = [
                    'tanggal' => $data['tanggal'], 'uraian' => 'Kasbon Cicilan '.$karyawan->nama, 'jenis_transaksi_id' => 2,
                    'nominal_transaksi' => $data['nominal'], 'saldo' => $kasBesar->saldo - $data['nominal'],
                    'modal_investor_terakhir' => $kasBesar->modal_investor_terakhir,
                    'transfer_ke' => substr($karyawan->nama_rekening, 0, 15), 'bank' => $karyawan->bank, 'no_rekening' => $karyawan->no_rekening,
                ];
                KasBonCicilan::create($data);

                return ['store' => KasBesar::create($k), 'kas' => $k, 'karyawan' => $karyawan];
            }, 3);
        } catch (ValidationException $exception) {
            return redirect()->back()->withInput()->withErrors($exception->errors())->with('error', $exception->validator->errors()->first());
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->back()->withInput()->with('error', 'Kasbon cicilan gagal disimpan. Tidak ada perubahan data; silakan coba kembali.');
        }
        $store = $result['store'];
        $k = $result['kas'];
        $karyawan = $result['karyawan'];

        $dbWa = new GroupWa;

        $group = $dbWa->where('untuk', 'kas-besar')->first();

        // buat nama bulan dari $data['mulai_bulan'] dengan bahasa indonesia
        $bulan = Carbon::createFromDate($data['mulai_tahun'], $data['mulai_bulan'], 1)->locale('id')->monthName;

        $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Kasbon Staff*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    'Nama : '.$karyawan->nama."\n".
                    'Uraian : Cicilan '.$data['cicil_kali']."X\n".
                    'Mulai : '.$bulan.' '.$data['mulai_tahun']."\n\n".
                    'Nilai :  *Rp. '.number_format($k['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    'Bank     : '.$k['bank']."\n".
                    'Nama    : '.$k['transfer_ke']."\n".
                    'No. Rek : '.$k['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    'Rp. '.number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    'Rp. '.number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
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

        if (! $password) {
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
                'lunas' => 1,
            ]);

            DB::commit();

            $group = GroupWa::where('untuk', 'kas-besar')->first();

            $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                    "*Form Void Kasbon Staff*\n".
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    'Nama : '.$kas->karyawan->nama."\n".
                    'Uraian : '.$k['uraian']."\n\n".
                    'Nilai :  *Rp. '.number_format($k['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    'Bank     : '.$k['bank']."\n".
                    'Nama    : '.$k['transfer_ke']."\n".
                    'No. Rek : '.$k['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    'Rp. '.number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    'Rp. '.number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

            $dbKas->sendWa($group->nama_group, $pesan);

        } catch (\Throwable $th) {

            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus kasbon cicilan');
        }

        return redirect()->back()->with('success', 'Kasbon cicilan berhasil dihapus');
    }
}
