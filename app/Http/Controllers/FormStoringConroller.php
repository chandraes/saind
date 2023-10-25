<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormStoringConroller extends Controller
{
    public function index()
    {
        return view('billing.storing.index');
    }

    public function store(Request $request)
    {
        
    }
}
