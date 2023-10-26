<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormDevidenController extends Controller
{
    public function index()
    {
        return view('billing.deviden.index');
    }
}
