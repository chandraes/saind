<?php

namespace App\Http\Controllers\Api;

use App\Models\KasBesar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RekapController extends BaseController
{
    public function saldo_kas_besar()
    {
        $db = new KasBesar();

        $saldoKasBesar = $db->lastKasBesar()->saldo ?? 0;

        return $this->sendResponse($saldoKasBesar, 'Saldo Kas Besar');

    }
}
