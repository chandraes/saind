<?php

namespace App\Http\Controllers;

use App\Models\GroupWa;
use App\Services\WaStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaController extends Controller
{
    public function wa()
    {
        $data = GroupWa::all();

        return view('pengaturan.wa', [
            'data' => $data,
        ]);
    }

    public function edit(int $id, WaStatus $wa): View
    {
        $data = GroupWa::findOrFail($id);
        $result = $wa->getGroup();

        return view('pengaturan.wa-edit', [
            'data' => $data,
            'group' => $result === false ? [] : $result['data'],
            'groupsUnavailable' => $result === false,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_group' => 'required',
            'group_id' => 'required',
        ]);

        $update = GroupWa::where('id', $id)->update([
            'nama_group' => $data['nama_group'],
            'group_id' => $data['group_id'],
        ]);

        if ($update) {
            return redirect()->route('pengaturan.wa')->with('success', 'Berhasil mengubah data');
        } else {
            return redirect()->route('pengaturan.wa')->with('error', 'Gagal mengubah data');
        }
    }
}
