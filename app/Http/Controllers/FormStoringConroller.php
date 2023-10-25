<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\BbmStoring;
use App\Models\KasBesar;
use App\Models\KasVendor;
use App\Models\Rekening;
use Illuminate\Http\Request;

class FormStoringConroller extends Controller
{
    public function index()
    {
        $vehicle = Vehicle::all();
        $storing = BbmStoring::all();
        $rekening = Rekening::where('untuk', 'mekanik')->first();

        return view('billing.storing.index', [
            'vehicle' => $vehicle,
            'storing' => $storing,
            'rekening' => $rekening,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
                    'id' => 'required',
                    'storing_id' => 'required',
                    'jasa' => 'nullable',
                ]);

        $vendor['vendor_id'] = Vehicle::find($request->id)->value('vendor_id');
        $vendor['vehicle_id'] = $request->id;
        


    }

    public function get_storing(Request $request)
    {
        $storing = BbmStoring::find($request->id);

        return response()->json($storing);
    }

    public function get_status_so(Request $request)
    {
        $data = Vehicle::find($request->id)->value('support_operational');

        return response()->json($data);
    }
}
