<?php

namespace App\Http\Controllers;

use App\Models\GroupWa;
use Illuminate\Http\Request;
use App\Services\WaStatus;

class WaController extends Controller
{
    public function wa()
    {
        $data = GroupWa::all();
        return view('pengaturan.wa', [
            'data' => $data
        ]);
    }

    public function edit($id)
    {
        $data = GroupWa::find($id);

        $wa = new WaStatus();
        $group = $wa->getGroup();
        // dd($group['data']);
        return view('pengaturan.wa-edit', [
            'data' => $data,
            'group' => $group['data']
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_group' => 'required',
            'group_id' => 'required'
        ]);


        $update = GroupWa::where('id', $id)->update([
            'nama_group' => $data['nama_group'],
            'group_id' => $data['group_id']
        ]);

        if ($update) {
            return redirect()->route('pengaturan.wa')->with('success', 'Berhasil mengubah data');
        } else {
            return redirect()->route('pengaturan.wa')->with('error', 'Gagal mengubah data');
        }
    }

}
