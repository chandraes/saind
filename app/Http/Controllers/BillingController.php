<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        return view('billing.index');
    }

    public function form_cost_operational()
    {
        return view('billing.cost-operational.index');
    }
}
