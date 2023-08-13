<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Rute;
use App\Models\CustomerRute;
use App\Models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Customer::all();
        $rute = Rute::select('id', 'nama')->get();
        return view('database.customer.index', [
            'data' => $data,
            'rute' => $rute,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rute = Rute::select('id', 'nama')->get();
        return view('database.customer.create', [
            'rute' => $rute,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $data = $request->validate([
            'nama' => 'required|min:3',
            'singkatan' => 'required',
            'npwp' => 'required',
            'alamat' => 'required|min:3',
            'contact_person' => 'required|min:3',
            'jabatan' => 'required',
            'no_hp' => 'required',
            'no_wa' => 'required',
            'email' => 'required',
            'harga_opname' => 'required|numeric',
            'harga_titipan' => 'required|numeric',
            'rute' => 'required',
        ]);

        $data['created_by'] = auth()->id();

        DB::transaction(function () use($data) {

            $customer = Customer::create($data);

            foreach ($data['rute'] as $rute) {
                CustomerRute::create([
                    'customer_id' => $customer->id,
                    'rute_id' => $rute,
                ]);
            }
        });

        return redirect()->route('customer.index')->with('success', 'Customer berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */

    public function show(Customer $customer)
    {
        $rute = Rute::select('id', 'nama')->get();
        return view('database.customer.show', [
            'data' => $customer,
            'rute' => $rute,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function edit(Customer $customer)
    {
        $rute = Rute::select('id', 'nama')->get();
        return view('database.customer.edit', [
            'data' => $customer,
            'rute' => $rute,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {

        $data = $request->validate([
                    'nama' => 'required|min:3',
                    'singkatan' => 'required',
                    'npwp' => 'required',
                    'alamat' => 'required|min:3',
                    'contact_person' => 'required|min:3',
                    'jabatan' => 'required',
                    'no_hp' => 'required',
                    'no_wa' => 'required',
                    'email' => 'required',
                    'harga_opname' => 'required|numeric',
                    'harga_titipan' => 'required|numeric',
                    'rute' => 'required',
                ]);

        // dd($data);

        $data['edited_by'] = auth()->id();

        DB::transaction(function () use($data, $customer) {

            $customer->update($data);

            CustomerRute::where('customer_id', $customer->id)->delete();

            foreach ($data['rute'] as $rute) {
                CustomerRute::create([
                    'customer_id' => $customer->id,
                    'rute_id' => $rute,
                ]);
            }
        });

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

    public function document_store(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'nama_dokumen' => 'required',
            'file' => 'required|mimes:pdf|max:10000',
        ]);

        $data['customer_id'] = $customer->id;

        $filename = $customer->nama . '-' . $data['nama_dokumen'] .Uuid::uuid4()->toString(). '.' . $request->file('file')->extension();

        $data['file'] = $request->file('file')->storeAs('public/customer', $filename);

        $customer->document()->create($data);


        return redirect()->route('customer.index')->with('success', 'Dokumen berhasil ditambahkan');
    }

    public function document_destroy(CustomerDocument $document)
    {
        // delete file
        $path = storage_path('app/' . $document->file);
        if (file_exists($path)) {
            unlink($path);
        }
        $document->delete();

        return redirect()->route('customer.index')->with('success', 'Dokumen berhasil dihapus');
    }

    public function document_download(CustomerDocument $document)
    {
        $path = storage_path('app/' . $document->file);

        if(!file_exists($path)) {
            return redirect()->route('customer.index')->with('error', 'File tidak ditemukan');
        }

        return response()->file($path);
    }
}
