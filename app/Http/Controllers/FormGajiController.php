<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KasBesar;
use App\Models\Direksi;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\GroupWa;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\PayrollService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormGajiController extends Controller
{

    protected $payroll;

    public function __construct(PayrollService $payroll)
    {
        $this->payroll = $payroll;
    }

    public function index(Request $request)
    {
        $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $check = RekapGaji::where('bulan', $request->bulan)->where('tahun', $request->tahun)->first();

        dd($check, $request->bulan, $request->tahun);
        if ($check) {
            return redirect()->route('billing.index')->with('error', 'Form Gaji Bulan Ini Sudah Dibuat');
        }
        // Cek jika bulan yang dipilih lebih besar dari bulan sekarang di tahun yang sama
        if ($request->tahun == date('Y') && $request->bulan > date('m')) {
            return redirect()->route('billing.index')->with('error', 'Form Gaji bulan ini belum dapat dibuat!');
        }

        $month = Carbon::create()->month($request->bulan)->locale('id')->monthName;

        // Optimasi: Eager Loading relasi untuk menghindari N+1 Query
        $data = Karyawan::where('status', 'aktif')->with(['jabatan', 'kas_bon', 'kas_bon_cicilan'])->get();
        $direksi = Direksi::where('status', 'aktif')->get();

        return view('billing.gaji.index', [
            'data' => $data,
            'direksi' => $direksi,
            'monthName' => $month,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'payroll' => $this->payroll
        ]);
    }

    public function store(Request $request)
    {
        $ds = $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $data = Karyawan::where('status', 'aktif')->with(['jabatan', 'kas_bon', 'kas_bon_cicilan'])->get();
        $direksi = Direksi::where('status', 'aktif')->get();

        $kasBesar = KasBesar::orderBy('id', 'desc')->first();
        $monthName = Carbon::create()->month($ds['bulan'])->locale('id')->monthName;
        $totalGajiSistem = 0;

        try {
            DB::beginTransaction();

            // Simpan Header Rekap Gaji (Total diupdate setelah loop selesai)
            $rekap = RekapGaji::create([
                'uraian' => "Gaji Bulan ".$monthName." Tahun ".$ds['tahun'],
                'bulan' => $ds['bulan'],
                'tahun' => $ds['tahun'],
                'total' => 0,
            ]);

            // 1. Proses Direksi
            foreach ($direksi as $d) {
                $calc = $this->payroll->calculateComponent($d);
                $totalGajiSistem += $calc['pendapatan_bersih'];

                RekapGajiDetail::create([
                    'rekap_gaji_id' => $rekap->id,
                    'nik' => "Direksi",
                    'nama' => $d->nama,
                    'jabatan' => $d->jabatan,
                    'gaji_pokok' => $d->gaji_pokok,
                    'tunjangan_jabatan' => $d->tunjangan_jabatan,
                    'tunjangan_keluarga' => $d->tunjangan_keluarga,
                    'bpjs_tk' => $calc['bpjs_tk'],
                    'bpjs_k' => $calc['bpjs_k'],
                    'potongan_bpjs_tk' => $calc['pot_tk'],
                    'potongan_bpjs_kesehatan' => $calc['pot_k'],
                    'pendapatan_kotor' => $calc['pendapatan_kotor'],
                    'pendapatan_bersih' => $calc['pendapatan_bersih'],
                    'kasbon' => 0,
                    'sisa_gaji_dibayar' => $calc['pendapatan_bersih'],
                    'transfer_ke' => $d->nama_rekening,
                    'bank' => $d->bank,
                    'no_rekening' => $d->no_rekening,
                ]);
            }

            // 2. Proses Karyawan
            foreach ($data as $k) {
                $calc = $this->payroll->calculateComponent($k);
                $kasbon = $this->payroll->calculateKasbon($k, $ds['bulan'], $ds['tahun']);
                $sisaGaji = $calc['pendapatan_bersih'] - $kasbon;
                $totalGajiSistem += $sisaGaji;

                RekapGajiDetail::create([
                    'rekap_gaji_id' => $rekap->id,
                    'nik' => $k->kode.sprintf("%03d", $k->nomor),
                    'nama' => $k->nama,
                    'jabatan' => $k->jabatan->nama,
                    'gaji_pokok' => $k->gaji_pokok,
                    'tunjangan_jabatan' => $k->tunjangan_jabatan,
                    'tunjangan_keluarga' => $k->tunjangan_keluarga,
                    'bpjs_tk' => $calc['bpjs_tk'],
                    'bpjs_k' => $calc['bpjs_k'],
                    'potongan_bpjs_tk' => $calc['pot_tk'],
                    'potongan_bpjs_kesehatan' => $calc['pot_k'],
                    'pendapatan_kotor' => $calc['pendapatan_kotor'],
                    'pendapatan_bersih' => $calc['pendapatan_bersih'],
                    'kasbon' => $kasbon,
                    'sisa_gaji_dibayar' => $sisaGaji,
                    'transfer_ke' => $k->nama_rekening,
                    'bank' => $k->bank,
                    'no_rekening' => $k->no_rekening,
                ]);

                // Update status pelunasan kasbon
                $this->updateKasbonStatus($k, $ds['bulan'], $ds['tahun']);
            }

            // Validasi Saldo Kas Besar
            if (!$kasBesar || $kasBesar->saldo < $totalGajiSistem) {
                throw new \Exception('Saldo Kas Besar Tidak Cukup');
            }

            // Update Total di Header Rekap
            $rekap->update(['total' => $totalGajiSistem]);

            // Simpan Transaksi Kas Besar
            $storeKasBesar = KasBesar::create([
                'uraian' => $rekap->uraian,
                'tanggal' => date('Y-m-d'),
                'nominal_transaksi' => $totalGajiSistem,
                'jenis_transaksi_id' => 2,
                'saldo' => $kasBesar->saldo - $totalGajiSistem,
                'modal_investor_terakhir' => $kasBesar->modal_investor_terakhir,
                'transfer_ke' => "Msng2 Karyawan",
                'bank' => 'BCA',
                'no_rekening' => '-',
            ]);

            DB::commit();

            // Kirim Notifikasi WA (di luar commit agar jika gagal tidak rollback data gaji)
            $this->sendWaNotification($totalGajiSistem, $storeKasBesar);

            return redirect()->route('billing.index')->with('success', 'Form Gaji Berhasil Dibuat');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Payroll Error: " . $th->getMessage());
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    private function updateKasbonStatus($karyawan, $bulan, $tahun)
    {
        // Update Kasbon Cicilan
        $cicilan = $karyawan->kas_bon_cicilan->where('lunas', 0)->first();
        if ($cicilan) {
            $mulai = Carbon::createFromDate($cicilan->mulai_tahun, $cicilan->mulai_bulan, 1);
            $sekarang = Carbon::createFromDate($tahun, $bulan, 1);

            if ($sekarang->greaterThanOrEqualTo($mulai)) {
                $nominal = $cicilan->cicilan_nominal;
                $sisa = $cicilan->sisa_kas - $nominal;
                $cicilan->update([
                    'total_bayar' => $cicilan->total_bayar + $nominal,
                    'sisa_kas' => $sisa,
                    'lunas' => $sisa <= 0 ? 1 : 0
                ]);
            }
        }

        // Update Kasbon Biasa (Lunas Sekaligus)
        $kasbons = $karyawan->kas_bon->where('lunas', 0);
        foreach ($kasbons as $k) {
            $k->update([
                'total_bayar' => $k->total_bayar + $k->nominal,
                'sisa_kas' => 0,
                'lunas' => 1
            ]);
        }
    }

    private function sendWaNotification($total, $kasBesar)
    {
        try {
            $dbWa = new GroupWa();
            $group = $dbWa->where('untuk', 'kas-besar')->first();

            if (!$group) return;

            $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Gaji Karyawan*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Nilai :  *Rp. ".number_format($total, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Nama     : Masing2 Karyawan\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar : \n".
                        "Rp. ".number_format($kasBesar->saldo, 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        "Rp. ".number_format($kasBesar->modal_investor_terakhir, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            $dbWa->sendWa($group->nama_group, $pesan);
        } catch (\Exception $e) {
            Log::warning("WA Notifikasi Gagal: " . $e->getMessage());
        }
    }
    // public function index(Request $request)
    // {
    //     $request->validate([
    //         'bulan' => 'required',
    //         'tahun' => 'required',
    //     ]);

    //     // check if bulan > month now
    //     if ($request->bulan > date('m')) {
    //         return redirect()->route('billing.index')->with('error', 'Form Gaji Bulan ini sudah di buat!!');
    //     }

    //     $check = RekapGaji::where('bulan', $request->bulan)->whereYear('tahun', $request->tahun)->first();

    //     if ($check) {
    //         return redirect()->route('billing.index')->with('error', 'Form Gaji Bulan Ini Sudah Dibuat');
    //     }
    //     // monthName from $request->bulan
    //     $month = Carbon::create()->month($request->bulan)->locale('id')->monthName;

    //     $data = Karyawan::where('status', 'aktif')->get();
    //     $direksi = Direksi::where('status', 'aktif')->get();

    //     return view('billing.gaji.index', [
    //         'data' => $data,
    //         'direksi' => $direksi,
    //         'month' => $month,
    //         'bulan' => $request->bulan,
    //         'tahun' => $request->tahun,
    //     ]);
    // }

    // public function store(Request $request)
    // {

    //     $ds = $request->validate([
    //         'bulan' => 'required',
    //         'tahun' => 'required',
    //         'total' => 'required',
    //     ]);

    //     $data = Karyawan::where('status', 'aktif')->get();
    //     $direksi = Direksi::where('status', 'aktif')->get();

    //     $kasBesar = KasBesar::orderBy('id', 'desc')->first();
    //     // dd($ds['total'], $kasBesar->saldo);
    //     if ($kasBesar == null || $kasBesar->saldo < $ds['total']) {
    //         return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Cukup');
    //     }
    //     $month = Carbon::create()->month($ds['bulan'])->locale('id')->monthName;
    //     $tahun = $ds['tahun'];

    //     try {

    //         DB::beginTransaction();

    //         $rekap = RekapGaji::create([
    //             'uraian' => "Gaji Bulan ".$month." Tahun ".$tahun,
    //             'bulan' => $ds['bulan'],
    //             'tahun' => $tahun,
    //             'total' => $ds['total'],
    //         ]);

    //         foreach ($direksi as $d) {
    //             $bpjs_tk_direksi = 0;
    //             $bpjs_k_direksi = 0;
    //             $potongan_bpjs_tk_direksi = 0;
    //             $potongan_bpjs_kesehatan_direksi = 0;
    //             $pendapatan_kotor_direksi = 0;
    //             $pendapatan_bersih_direksi = 0;

    //             $gapokPlusTunjanganDir = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga;
    //             $bpjs_tk_direksi = $d->apa_bpjs_tk == 1 ? $gapokPlusTunjanganDir * 0.0489 : 0;
    //             $potongan_bpjs_tk_direksi = $d->apa_bpjs_tk == 1 ? $gapokPlusTunjanganDir * 0.02 : 0;
    //             $bpjs_k_direksi = $d->apa_bpjs_kesehatan == 1 ? $gapokPlusTunjanganDir * 0.04 : 0;
    //             $potongan_bpjs_kesehatan_direksi = $d->apa_bpjs_kesehatan == 1 ? $gapokPlusTunjanganDir * 0.01 : 0;

    //             $pendapatan_kotor_direksi = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga + $bpjs_tk_direksi + $bpjs_k_direksi;
    //             $pendapatan_bersih_direksi = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga - $potongan_bpjs_tk_direksi - $potongan_bpjs_kesehatan_direksi;

    //             RekapGajiDetail::create([
    //                 'rekap_gaji_id' => $rekap->id,
    //                 'nik' => "Direksi",
    //                 'nama' => $d->nama,
    //                 'jabatan' => $d->jabatan,
    //                 'gaji_pokok' => $d->gaji_pokok,
    //                 'tunjangan_jabatan' => $d->tunjangan_jabatan,
    //                 'tunjangan_keluarga' => $d->tunjangan_keluarga,
    //                 'bpjs_tk' => $bpjs_tk_direksi,
    //                 'bpjs_k' => $bpjs_k_direksi,
    //                 'potongan_bpjs_tk' => $potongan_bpjs_tk_direksi,
    //                 'potongan_bpjs_kesehatan' => $potongan_bpjs_kesehatan_direksi,
    //                 'pendapatan_kotor' => $pendapatan_kotor_direksi,
    //                 'pendapatan_bersih' => $pendapatan_bersih_direksi,
    //                 'kasbon' => 0,
    //                 'sisa_gaji_dibayar' => $pendapatan_bersih_direksi,
    //                 'transfer_ke' => $d->nama_rekening,
    //                 'bank' => $d->bank,
    //                 'no_rekening' => $d->no_rekening,
    //             ]);
    //         }

    //         foreach ($data as $d) {

    //             $bpjs_tk = 0;
    //             $bpjs_k = 0;
    //             $potongan_bpjs_tk = 0;
    //             $potongan_bpjs_kesehatan = 0;
    //             $pendapatan_kotor = 0;
    //             $pendapatan_bersih = 0;

    //             $gapokPlusTunjangan = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga;
    //             $bpjs_tk = $d->apa_bpjs_tk == 1 ? $gapokPlusTunjangan * 0.0489 : 0;
    //             $potongan_bpjs_tk = $d->apa_bpjs_tk == 1 ? $gapokPlusTunjangan * 0.02 : 0;
    //             $bpjs_k = $d->apa_bpjs_kesehatan == 1 ? $gapokPlusTunjangan * 0.04 : 0;
    //             $potongan_bpjs_kesehatan = $d->apa_bpjs_kesehatan == 1 ? $gapokPlusTunjangan* 0.01 : 0;

    //             $pendapatan_kotor = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga + $bpjs_tk + $bpjs_k;
    //             $pendapatan_bersih = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga - $potongan_bpjs_tk - $potongan_bpjs_kesehatan;

    //             $kasbon_cicil = 0;

    //             $cicilan = $d->kas_bon_cicilan->where('lunas', 0)->first();
    //                 // create tanggal from $cicilan->mulai_bulan and $cicilan->mulai_tahun
    //             if ($cicilan) {

    //                 $mulai = $cicilan->mulai_tahun.'-'.$cicilan->mulai_bulan.'-01';

    //                 $mulai = date('Y-m-d', strtotime($mulai));
    //                 // check if $mulai month and year is > from now
    //                 $now = date('Y-m-d');

    //                 if($mulai < $now){
    //                     $kasbon_cicil = $d->kas_bon_cicilan->where('lunas', 0)->first()->cicilan_nominal;
    //                 }

    //             }

    //             $kasbon = $d->kas_bon->where('lunas', 0)->sum('nominal');

    //             $total_kasbon = $kasbon_cicil + $kasbon;

    //             RekapGajiDetail::create([
    //                 'rekap_gaji_id' => $rekap->id,
    //                 'nik' => $d->kode.sprintf("%03d", $d->nomor),
    //                 'nama' => $d->nama,
    //                 'jabatan' => $d->jabatan->nama,
    //                 'gaji_pokok' => $d->gaji_pokok,
    //                 'tunjangan_jabatan' => $d->tunjangan_jabatan,
    //                 'tunjangan_keluarga' => $d->tunjangan_keluarga,
    //                 'bpjs_tk' => $bpjs_tk,
    //                 'bpjs_k' => $bpjs_k,
    //                 'potongan_bpjs_tk' => $potongan_bpjs_tk,
    //                 'potongan_bpjs_kesehatan' => $potongan_bpjs_kesehatan,
    //                 'pendapatan_kotor' => $pendapatan_kotor,
    //                 'pendapatan_bersih' => $pendapatan_bersih,
    //                 'kasbon' => $total_kasbon,
    //                 'sisa_gaji_dibayar' => $pendapatan_bersih-$total_kasbon,
    //                 'transfer_ke' => $d->nama_rekening,
    //                 'bank' => $d->bank,
    //                 'no_rekening' => $d->no_rekening,
    //             ]);

    //             if ($kasbon_cicil > 0) {

    //                 $rekapCicilan = KasBonCicilan::where('lunas', 0)->where('karyawan_id', $d->id)->first();

    //                 $update = $rekapCicilan->update([
    //                     'total_bayar' => $rekapCicilan->total_bayar + $kasbon_cicil,
    //                     'sisa_kas' => $rekapCicilan->sisa_kas - $kasbon_cicil,
    //                     'lunas' => $rekapCicilan->sisa_kas - $kasbon_cicil == 0 ? 1 : 0,
    //                 ]);
    //             }

    //             // update kas bon where lunas = 0 and karyawan_id = $d->id and update total_bayar
    //             $kasbon = KasBon::where('lunas', 0)->where('karyawan_id', $d->id)->get();

    //             foreach ($kasbon as $k) {
    //                 $update = $k->update([
    //                     'total_bayar' => $k->total_bayar + $k->nominal,
    //                     'sisa_kas' => $k->sisa_kas - $k->nominal,
    //                     'lunas' => $k->sisa_kas - $k->nominal == 0 ? 1 : 0,
    //                 ]);

    //             }

    //         }

    //         $arrayKasBesar['uraian'] = "Gaji Bulan ".$month." ".$tahun;
    //         $arrayKasBesar['tanggal'] = date('Y-m-d');
    //         $arrayKasBesar['nominal_transaksi'] = $ds['total'];
    //         $arrayKasBesar['jenis_transaksi_id'] = 2;
    //         $arrayKasBesar['saldo'] = $kasBesar->saldo - $ds['total'];
    //         $arrayKasBesar['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
    //         $arrayKasBesar['transfer_ke'] = "Msng2 Karyawan";
    //         $arrayKasBesar['bank'] = 'BCA';
    //         $arrayKasBesar['no_rekening'] = '-';

    //         $storeKasBesar = KasBesar::create($arrayKasBesar);

    //         DB::commit();

    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return redirect()->back()->with('error', 'Terjadi Kesalahan');
    //     }

    //     $dbWa = new GroupWa();

    //     $group = $dbWa->where('untuk', 'kas-besar')->first();

    //     $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
    //                 "*Form Gaji Karyawan*\n".
    //                 "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
    //                 "Nilai :  *Rp. ".number_format($ds['total'], 0, ',', '.')."*\n\n".
    //                 "Ditransfer ke rek:\n\n".
    //                 "Nama     : Masing2 Karyawan\n\n".
    //                 "==========================\n".
    //                 "Sisa Saldo Kas Besar : \n".
    //                 "Rp. ".number_format($storeKasBesar->saldo, 0, ',', '.')."\n\n".
    //                 "Total Modal Investor : \n".
    //                 "Rp. ".number_format($storeKasBesar->modal_investor_terakhir, 0, ',', '.')."\n\n".
    //                 "Terima kasih 🙏🙏🙏\n";

    //     $send = $dbWa->sendWa($group->nama_group, $pesan);

    //     return redirect()->route('billing.index')->with('success', 'Form Gaji Berhasil Dibuat');

    // }
}
