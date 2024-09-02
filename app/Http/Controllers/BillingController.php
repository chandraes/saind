<?php

namespace App\Http\Controllers;

use App\Models\CostOperational;
use App\Models\KasBesar;
use App\Models\RekapGaji;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        return view('billing.index');
    }

    public function form_cost_operational()
    {
        $check = RekapGaji::orderBy('id', 'desc')->first();

        $bulan = $check->bulan + 1 == 13 ? 1 : $check->bulan + 1;
        $tahun = $check->bulan + 1 == 13 ? $check->tahun + 1 : $check->tahun;
        
        return view('billing.form-cost-operational.index',
            [
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]);
    }

    public function cost_operational()
    {
        $data = CostOperational::all();

        if($data->isEmpty()) {
            return redirect()->route('database.cost-operational')->with('error', 'Data cost operational kosong, silahkan tambahkan data cost operational terlebih dahulu');
        }

        return view('billing.form-cost-operational.form-operational.index', [
            'data' => $data,
        ]);
    }

    public function cost_operational_store(Request $request)
    {
        $data = $request->validate([
                    'nominal_transaksi' => 'required',
                    'cost_operational_id' => 'required|exists:cost_operationals,id',
                    'transfer_ke' => 'required',
                    'no_rekening' => 'required',
                    'bank' => 'required',
                ]);


        $db = new KasBesar();

        $res = $db->cost_operational($data);

        return redirect()->route('billing.form-cost-operational')->with($res['status'], $res['message']);

    }
}
