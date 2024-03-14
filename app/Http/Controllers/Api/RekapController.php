<?php

namespace App\Http\Controllers\Api;

use App\Models\KasBesar;
use App\Http\Controllers\Controller;
use App\Models\KasKecil;
use App\Models\KasUangJalan;
use App\Models\Rekening;
use Illuminate\Http\Request;

class RekapController extends BaseController
{
    public function saldo_kas_besar()
    {
        $data = [];
        $role = auth()->user()->role;
        $rekening = Rekening::all();

        if ($role == 'su' || $role == 'admin') {
            $kb = new KasBesar();
            $kbS = [
                'nama' => "Kas Besar",
                'acno' => $rekening->where('untuk', 'kas-besar')->first()->nomor_rekening ?? "-",
                'saldo' => $kb->lastKasBesar()->saldo ?? 0,
            ];

            array_push($data, $kbS);

        }

        if ($role == 'su' || $role == 'user' || $role == 'admin') {
            $kk = new KasKecil();
            $kkS = [
                'nama' => "Kas Kecil",
                'acno' => $rekening->where('untuk', 'kas-kecil')->first()->nomor_rekening ?? "-",
                'saldo' => $kk->saldoKasKecil()
            ];
            array_push($data, $kkS);

            $kuj = new KasUangJalan();
            $kujS = [
                'nama' => "Kas Uang Jalan",
                'acno' => $rekening->where('untuk', 'kas-uang-jalan')->first()->nomor_rekening ?? "-",
                'saldo' => $kuj->saldoKasUangJalan()
            ];
            array_push($data, $kujS);

        }

        return $this->sendResponse($data, 'Saldo Kas Berhasil diambil!');
    }
}
