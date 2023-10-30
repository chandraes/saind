<?php

namespace App\Http\Controllers;

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
        $data = User::select('id', 'name', 'username', 'role')->get();
        return view('pengguna.index', compact('data','vendor'));
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
        // dd($request->password);
        $data = $request->validate([
            "name" => "required|min:3",
            "username" => "required|unique:users",
            "email" => "nullable",
            "password" => "required|min:6",
            "role" => "required",
            'vendor_id' => 'nullable',
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
            ]);
        } else {
            User::findOrFail($id)->update([
                "name" => $data['name'],
                "username" => $data['username'],
                "email" => $data['email'],
                "role" => $data['role'],
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
