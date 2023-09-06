<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WaStatus;

class WaController extends Controller
{
    public function wa()
    {
        return view('pengaturan.wa');
    }

    public function getGroup()
    {
        $wa = new WaStatus();
        $result = $wa->getGroup();
    }
}
