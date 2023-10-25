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
}
