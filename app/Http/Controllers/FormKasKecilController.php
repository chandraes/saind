<?php

namespace App\Http\Controllers;

use App\Models\KasKecil;
use App\Models\Rekening;
use App\Models\KasBesar;
use Illuminate\Http\Request;
use App\Services\WaStatus;

class FormKasKecilController extends Controller
{
    public function masuk()
    {
        $nomor = KasKecil::latest()->first();

        if($nomor == null){
            $nomor = 1;
        }else{
            $nomor = $nomor->nomor_kode_deposit + 1;
        }

        $rekening = Rekening::where('untuk', 'kas-kecil')->first();

        return view('billing.kas-kecil.masuk', [
            'nomor' => $nomor,
            'rekening' => $rekening,
        ]);
    }

    public function masuk_store()
    {
        $kk = KasKecil::latest()->first();
        $kb = KasBesar::latest()->first();
        $rekening = Rekening::where('untuk', 'kas-kecil')->first();
        
        if($kk == null){
            $data['nomor_kode_kas_kecil'] = 1;
            $data['saldo'] = 1000000;
        }else{
            $data['nomor_kode_kas_kecil']= $kk->nomor_kode_deposit + 1;
            $data['saldo'] = $kk->saldo + 1000000;
        }

        $data['tanggal'] = date('Y-m-d');
        $data['uraian'] = 'Permintaan Dana';
        $data['nominal_transaksi'] = 1000000;
        $data['transfer_ke'] = $rekening->nama_rekening;
        $data['bank'] = $rekening->nama_bank;
        $data['no_rekening'] = $rekening->nomor_rekening;




    }

    public function keluar()
    {
        $wa = new WaStatus();
        $req = $wa->getStatusWa();
        dd($req);
    }

    public function keluar_store(Request $request)
    {

    }
}
