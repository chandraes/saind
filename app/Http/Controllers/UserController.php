<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vendor;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendor = Vendor::select('id', 'nama')->where('status', 'aktif')->get();
        $customer = Customer::select('id', 'nama')->where('status', true)->get();

        $data = User::with('customer')->where('role', '!=', 'su')->leftJoin('vendors', 'users.vendor_id', '=', 'vendors.id')
            ->select('users.*', 'vendors.nama as vendor')
            ->orderBy('users.role', 'asc')
            ->get();
        return view('pengguna.index', compact('data','vendor', 'customer'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $data = $request->validate([
            "name" => "required|min:3",
            "username" => "required|unique:users",
            "email" => "nullable",
            "password" => "required|min:6",
            "role" => "required|in:admin,user,vendor,vendor-operational,customer,operasional",
            'vendor_id' => 'nullable',
            'customer_id' => 'nullable',
        ]);

        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return redirect()->route('pengguna.index')->with('success', 'Data berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            "name" => "required|min:3",
            "username" => "required|unique:users,username,$id",
            "email" => "nullable",
            "password" => "nullable",
            "role" => "required",
            'vendor_id' => 'nullable',
        ]);

        if ($request->password) {
            $data['password'] = bcrypt($data['password']);
            User::findOrFail($id)->update([
                "name" => $data['name'],
                "username" => $data['username'],
                "email" => $data['email'],
                "password" => $data['password'],
                "role" => $data['role'],
                "vendor_id" => $data['vendor_id'] ? $data['vendor_id'] : null,
            ]);
        } else {
            User::findOrFail($id)->update([
                "name" => $data['name'],
                "username" => $data['username'],
                "email" => $data['email'],
                "role" => $data['role'],
                "vendor_id" => $data['vendor_id'] ? $data['vendor_id'] : null,
            ]);
        }

        return redirect()->route('pengguna.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // check if user admin atleast 1, if yes, return error
        $admin = User::where('role', 'admin')->count();
        if ($admin == 1) {
            return redirect()->route('pengguna.index')->with('success', 'Tidak dapat menghapus data admin');
        } else {
            User::findOrFail($id)->delete();
        }

        return redirect()->route('pengguna.index')->with('success', 'Data berhasil dihapus');
    }
}
