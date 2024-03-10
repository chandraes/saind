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
use App\Services\StarSender;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FormGajiController extends Controller
{
    public function index()
    {
        $check = RekapGaji::where('bulan', date('m'))->whereYear('tahun', date('Y'))->first();

        if ($check) {
            return redirect()->route('billing.index')->with('error', 'Form Gaji Bulan Ini Sudah Dibuat');
        }
        // monthName now in indo
        $month = Carbon::now()->locale('id')->monthName;

        $data = Karyawan::where('status', 'aktif')->get();
        $direksi = Direksi::where('status', 'aktif')->get();
        return view('billing.gaji.index', [
            'data' => $data,
            'direksi' => $direksi,
            'month' => $month
        ]);
    }

    public function store(Request $request)
    {
        $ds = $request->validate([
            'total' => 'required',
        ]);

        $data = Karyawan::where('status', 'aktif')->get();
        $direksi = Direksi::where('status', 'aktif')->get();

        $kasBesar = KasBesar::latest()->orderBy('id', 'desc')->first();

        if ($kasBesar == null || $kasBesar->saldo < $ds['total']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Cukup');
        }

        $rekap = RekapGaji::create([
            'uraian' => "Gaji Bulan ".date('F')." Tahun ".date('Y'),
            'bulan' => date('m'),
            'tahun' => date('Y'),
            'total' => $ds['total'],
        ]);

        foreach ($direksi as $d) {
            $bpjs_tk_direksi = 0;
            $bpjs_k_direksi = 0;
            $potongan_bpjs_tk_direksi = 0;
            $potongan_bpjs_kesehatan_direksi = 0;
            $pendapatan_kotor_direksi = 0;
            $pendapatan_bersih_direksi = 0;

            $bpjs_tk_direksi = $d->gaji_pokok * 0.049;
            $bpjs_k_direksi = $d->gaji_pokok * 0.04;
            $potongan_bpjs_tk_direksi = $d->gaji_pokok * 0.02;
            $potongan_bpjs_kesehatan_direksi = $d->gaji_pokok * 0.01;
            $pendapatan_kotor_direksi = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga + $bpjs_tk_direksi + $bpjs_k_direksi;
            $pendapatan_bersih_direksi = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga - $potongan_bpjs_tk_direksi - $potongan_bpjs_kesehatan_direksi;

            RekapGajiDetail::create([
                'rekap_gaji_id' => $rekap->id,
                'nik' => "Direksi",
                'nama' => $d->nama,
                'jabatan' => $d->jabatan,
                'gaji_pokok' => $d->gaji_pokok,
                'tunjangan_jabatan' => $d->tunjangan_jabatan,
                'tunjangan_keluarga' => $d->tunjangan_keluarga,
                'bpjs_tk' => $bpjs_tk_direksi,
                'bpjs_k' => $bpjs_k_direksi,
                'potongan_bpjs_tk' => $potongan_bpjs_tk_direksi,
                'potongan_bpjs_kesehatan' => $potongan_bpjs_kesehatan_direksi,
                'pendapatan_kotor' => $pendapatan_kotor_direksi,
                'pendapatan_bersih' => $pendapatan_bersih_direksi,
                'kasbon' => 0,
                'sisa_gaji_dibayar' => $pendapatan_bersih_direksi,
                'transfer_ke' => $d->nama_rekening,
                'bank' => $d->bank,
                'no_rekening' => $d->no_rekening,
            ]);
        }

        foreach ($data as $d) {

            $bpjs_tk = 0;
            $bpjs_k = 0;
            $potongan_bpjs_tk = 0;
            $potongan_bpjs_kesehatan = 0;
            $pendapatan_kotor = 0;
            $pendapatan_bersih = 0;

            $bpjs_tk = $d->gaji_pokok * 0.049;
            $bpjs_k = $d->gaji_pokok * 0.04;
            $potongan_bpjs_tk = $d->gaji_pokok * 0.02;
            $potongan_bpjs_kesehatan = $d->gaji_pokok * 0.01;
            $pendapatan_kotor = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga + $bpjs_tk + $bpjs_k;
            $pendapatan_bersih = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga - $potongan_bpjs_tk - $potongan_bpjs_kesehatan;

            $kasbon_cicil = 0;

            $cicilan = $d->kas_bon_cicilan->where('lunas', 0)->first();
                // create tanggal from $cicilan->mulai_bulan and $cicilan->mulai_tahun
            if ($cicilan) {

                $mulai = $cicilan->mulai_tahun.'-'.$cicilan->mulai_bulan.'-01';

                $mulai = date('Y-m-d', strtotime($mulai));
                // check if $mulai month and year is > from now
                $now = date('Y-m-d');

                if($mulai < $now){
                    $kasbon_cicil = $d->kas_bon_cicilan->where('lunas', 0)->first()->cicilan_nominal;
                }

            }

            $kasbon = $d->kas_bon->where('lunas', 0)->sum('nominal');

            $total_kasbon = $kasbon_cicil + $kasbon;

            RekapGajiDetail::create([
                'rekap_gaji_id' => $rekap->id,
                'nik' => $d->kode.sprintf("%03d", $d->nomor),
                'nama' => $d->nama,
                'jabatan' => $d->jabatan->nama,
                'gaji_pokok' => $d->gaji_pokok,
                'tunjangan_jabatan' => $d->tunjangan_jabatan,
                'tunjangan_keluarga' => $d->tunjangan_keluarga,
                'bpjs_tk' => $bpjs_tk,
                'bpjs_k' => $bpjs_k,
                'potongan_bpjs_tk' => $potongan_bpjs_tk,
                'potongan_bpjs_kesehatan' => $potongan_bpjs_kesehatan,
                'pendapatan_kotor' => $pendapatan_kotor,
                'pendapatan_bersih' => $pendapatan_bersih,
                'kasbon' => $total_kasbon,
                'sisa_gaji_dibayar' => $pendapatan_bersih-$total_kasbon,
                'transfer_ke' => $d->nama_rekening,
                'bank' => $d->bank,
                'no_rekening' => $d->no_rekening,
            ]);

            if ($kasbon_cicil > 0) {

                $rekapCicilan = KasBonCicilan::where('lunas', 0)->where('karyawan_id', $d->id)->first();

                $update = $rekapCicilan->update([
                    'total_bayar' => $rekapCicilan->total_bayar + $kasbon_cicil,
                    'sisa_kas' => $rekapCicilan->sisa_kas - $kasbon_cicil,
                    'lunas' => $rekapCicilan->sisa_kas - $kasbon_cicil == 0 ? 1 : 0,
                ]);
            }

            // update kas bon where lunas = 0 and karyawan_id = $d->id and update total_bayar
            $kasbon = KasBon::where('lunas', 0)->where('karyawan_id', $d->id)->get();

            foreach ($kasbon as $k) {
                $update = $k->update([
                    'total_bayar' => $k->total_bayar + $k->nominal,
                    'sisa_kas' => $k->sisa_kas - $k->nominal,
                    'lunas' => $k->sisa_kas - $k->nominal == 0 ? 1 : 0,
                ]);

            }

        }

        $arrayKasBesar['uraian'] = "Gaji Bulan ".date('F')." ".date('Y');
        $arrayKasBesar['tanggal'] = date('Y-m-d');
        $arrayKasBesar['nominal_transaksi'] = $ds['total'];
        $arrayKasBesar['jenis_transaksi_id'] = 2;
        $arrayKasBesar['saldo'] = $kasBesar->saldo - $ds['total'];
        $arrayKasBesar['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
        $arrayKasBesar['transfer_ke'] = "Msng2 Karyawan";
        $arrayKasBesar['bank'] = 'BCA';
        $arrayKasBesar['no_rekening'] = '-';

        $storeKasBesar = KasBesar::create($arrayKasBesar);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Gaji Karyawan*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nilai :  *Rp. ".number_format($ds['total'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Nama     : Masing2 Karyawan\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($storeKasBesar->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($storeKasBesar->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Form Gaji Berhasil Dibuat');

    }
}
