<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\BbmStoring;
use Illuminate\Http\Request;

class FormStoringConroller extends Controller
{
    public function index()
    {
        $vehicle = Vehicle::all();
        $storing = BbmStoring::all();

        return view('billing.storing.index', [
            'vehicle' => $vehicle,
            'storing' => $storing,
        ]);
    }

    public function store(Request $request)
    {

    }

    public function get_storing(Request $request)
    {
        $storing = BbmStoring::find($request->id);

        return response()->json($storing);
    }

    public function get_status_so(Request $request)
    {
        $data = Vehicle::find($request->vehicle_id)->support_operational;

        return response()->json($data);
    }
}
