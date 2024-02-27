<?php

namespace App\Http\Controllers\Api;

use App\Models\KasBesar;
use App\Http\Controllers\Controller;
use App\Models\KasKecil;
use App\Models\KasUangJalan;
use Illuminate\Http\Request;

class RekapController extends BaseController
{
    public function saldo_kas_besar()
    {
        $kb = new KasBesar();
        $saldoKasBesar = $kb->lastKasBesar()->saldo ?? 0;

        $kk = new KasKecil();
        $saldoKasKecil = $kk->saldoKasKecil();

        $kuj = new KasUangJalan();
        $kasUangJalan = $kuj->saldoKasUangJalan();


        $data = [
            'kas_besar' => $saldoKasBesar,
            'kas_kecil' => $saldoKasKecil,
            'kas_uang_jalan' => $kasUangJalan
        ];

        // dd($data);

        return $this->sendResponse($data, 'Saldo Kas Berhasil diambil!');

    }
}
