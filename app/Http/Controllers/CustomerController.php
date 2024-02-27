<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Rute;
use App\Models\CustomerRute;
use App\Models\PasswordKonfirmasi;
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
        // dd($request->all());
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
                'harga_opname' => 'nullable',
                'harga_titipan' => 'nullable',
                'rute.*' => 'required',
                'tanggal_muat' => 'nullable',
                'nota_muat' => 'nullable',
                'tonase' => 'nullable',
                'tanggal_bongkar' => 'nullable',
                'selisih' => 'nullable',
                'ppn' => 'nullable',
                'pph' => 'nullable',
                'tagihan_dari' => 'required',
                'csr' => 'nullable',
                'csr_transfer_ke' => 'nullable',
                'csr_bank' => 'nullable',
                'csr_no_rekening' => 'nullable',
                'harga_csr_atas' => 'nullable',
                'harga_csr_bawah' => 'nullable',
                'gt_muat' => 'nullable',
                'gt_bongkar' => 'nullable',
        ]);

        $rute = $data['rute'];
        unset($data['rute']);

        $data['harga_csr_atas'] = $data['harga_csr_atas'] ? str_replace('.', '', $data['harga_csr_atas']) : 0;
        $data['harga_csr_bawah'] = $data['harga_csr_bawah'] ? str_replace('.', '', $data['harga_csr_bawah']) : 0;

        $data['created_by'] = auth()->id();

        $checkboxFields = ['ppn', 'pph', 'csr', 'tanggal_muat', 'nota_muat', 'tonase', 'gt_muat', 'gt_bongkar', 'tanggal_bongkar', 'selisih'];
        foreach ($checkboxFields as $field) {
            if ($field == 'ppn' && array_key_exists($field, $data)) {
                $data['ppn'] = 1;
                $data['pph'] = 1;
            } elseif($field == 'ppn' && !array_key_exists($field, $data)) {
                $data['ppn'] = 0;
                $data['pph'] = 0;
            } elseif($field != 'pph'){
                $data[$field] = array_key_exists($field, $data) ? 1 : 0;
            }
        }

        if (!$data['csr']) {
            $data['harga_csr_atas'] = 0;
            $data['harga_csr_bawah'] = 0;
            $data['csr_transfer_ke'] = null;
            $data['csr_bank'] = null;
            $data['csr_no_rekening'] = null;
        }

        DB::beginTransaction();

        try {

            $customer = Customer::create($data);

            foreach ($rute as $r) {
                CustomerRute::create([
                    'customer_id' => $customer->id,
                    'rute_id' => $r,
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }


        return redirect()->route('customer.tagihan', $customer->id);
    }

    public function tagihan(Customer $customer)
    {
        return view('database.customer.create-tagihan', [
            'data' => $customer,
        ]);
    }

    public function tagihan_store(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'rute_id' => 'required',
            'harga_tagihan' => 'required',
            'opname' => 'required',
            'titipan' => 'required',
        ]);


        for ($i=0; $i < count($data['rute_id']); $i++) {
            $customer->customer_tagihan()->create([
                'rute_id' => $data['rute_id'][$i],
                'harga_tagihan' => str_replace('.', '', $data['harga_tagihan'][$i]),
                'opname' => str_replace('.', '', $data['opname'][$i]),
                'titipan' => str_replace('.', '', $data['titipan'][$i]),
            ]);
        }

        return redirect()->route('customer.index')->with('success', 'Tagihan berhasil ditambahkan');
    }

    public function tagihan_edit(Customer $customer)
    {
        return view('database.customer.edit-tagihan', [
            'data' => $customer,
        ]);
    }

    public function tagihan_update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'rute_id' => 'required',
            'harga_tagihan' => 'required',
            'opname' => 'required',
            'titipan' => 'required',
        ]);

        $customer->customer_tagihan()->delete();

        for ($i=0; $i < count($data['rute_id']); $i++) {
            $customer->customer_tagihan()->create([
                'rute_id' => $data['rute_id'][$i],
                'harga_tagihan' => str_replace('.', '', $data['harga_tagihan'][$i]),
                'opname' => str_replace('.', '', $data['opname'][$i]),
                'titipan' => str_replace('.', '', $data['titipan'][$i]),
            ]);
        }

        return redirect()->route('customer.index')->with('success', 'Tagihan berhasil diupdate');
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
                    'harga_opname' => 'nullable',
                    'harga_titipan' => 'nullable',
                    'rute.*' => 'required',
                    'tanggal_muat' => 'nullable',
                    'nota_muat' => 'nullable',
                    'tonase' => 'nullable',
                    'tanggal_bongkar' => 'nullable',
                    'selisih' => 'nullable',
                    'ppn' => 'nullable',
                    'pph' => 'nullable',
                    'tagihan_dari' => 'required',
                    'csr' => 'nullable',
                    'csr_transfer_ke' => 'nullable',
                    'csr_bank' => 'nullable',
                    'csr_no_rekening' => 'nullable',
                    'harga_csr_atas' => 'nullable',
                    'harga_csr_bawah' => 'nullable',
                    'gt_muat' => 'nullable',
                    'gt_bongkar' => 'nullable',
                ]);

        $data['harga_csr_atas'] = $data['harga_csr_atas'] ? str_replace('.', '', $data['harga_csr_atas']) : 0;
        $data['harga_csr_bawah'] = $data['harga_csr_bawah'] ? str_replace('.', '', $data['harga_csr_bawah']) : 0;

        $data['edited_by'] = auth()->id();

        $checkboxFields = ['ppn', 'pph', 'csr', 'tanggal_muat', 'nota_muat', 'tonase', 'gt_muat', 'gt_bongkar', 'tanggal_bongkar', 'selisih'];
        foreach ($checkboxFields as $field) {
            if ($field == 'ppn' && array_key_exists($field, $data)) {
                $data['ppn'] = 1;
                $data['pph'] = 1;
            } elseif($field == 'ppn' && !array_key_exists($field, $data)) {
                $data['ppn'] = 0;
                $data['pph'] = 0;
            } elseif($field != 'pph'){
                $data[$field] = array_key_exists($field, $data) ? 1 : 0;
            }
        }

        if (!$data['csr']) {
            $data['harga_csr_atas'] = 0;
            $data['harga_csr_bawah'] = 0;
            $data['csr_transfer_ke'] = null;
            $data['csr_bank'] = null;
            $data['csr_no_rekening'] = null;
        }

        DB::transaction(function () use($data, $customer) {

            CustomerRute::where('customer_id', $customer->id)->delete();

            foreach ($data['rute'] as $rute) {
                CustomerRute::create([
                    'customer_id' => $customer->id,
                    'rute_id' => $rute,
                ]);
            }
            unset($data['rute']);

            $customer->update($data);
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

    public function ubah_status(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (!$password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        $customer->update([
            'status' => $customer->status == 0 ? 1 : 0,
        ]);

        return redirect()->route('customer.index')->with('success', 'Status berhasil diubah');
    }

    public function preview_customer()
    {
        $data = Customer::all();

        $pdf = PDF::loadview('database.customer.preview-customer', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Daftar Customer.pdf');
    }
}
