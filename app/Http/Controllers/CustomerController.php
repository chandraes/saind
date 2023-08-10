<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Rute;
use App\Models\CustomerRute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Customer::all();
        $rute = Rute::select('id', 'nama')->get();
        return view('database.customer', [
            'data' => $data,
            'rute' => $rute,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'singkatan' => 'required',
            'contact_person' => 'required',
            'harga_opname' => 'required|numeric',
            'harga_titipan' => 'required|numeric',
        ]);

        $data['created_by'] = auth()->id();

        $store = Customer::create($data);

        return redirect()->route('customer.index')->with('success', 'Customer berhasil ditambahkan');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'nama' => 'required',
            'singkatan' => 'required',
            'contact_person' => 'required',
            'harga_opname' => 'required|numeric',
            'harga_titipan' => 'required|numeric',
            'rute' => 'required',
        ]);

        $data['edited_by'] = auth()->id();

        $customer->update($data);

        return redirect()->route('customer.index')->with('success', 'Customer berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {

        DB::transaction(function () use($customer) {

            CustomerRute::where('customer_id', $customer->id)->delete();

            $customer->delete();
        });

        return redirect()->route('customer.index')->with('success', 'Customer berhasil dihapus');
    }
}
