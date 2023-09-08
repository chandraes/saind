<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WaStatus;

class WaController extends Controller
{
    public function wa()
    {
        $wa = new WaStatus();
        $result = $wa->getGroup();
        // dd($result);
        return view('pengaturan.wa');
    }

    public function getGroup()
    {
        $wa = new WaStatus();
        $result = $wa->getGroup();

        if ($result) {
            return response()->json([
                'status' => true,
                'data' => $result['data']
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => null
            ]);
        }
    }
}
