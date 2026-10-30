<?php

namespace App\Http\Controllers;

use App\Models\GroupWa;
use App\Services\WaStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WaController extends Controller
{
    public function wa()
    {
        $data = GroupWa::all();

        return view('pengaturan.wa', [
            'data' => $data,
        ]);
    }

    public function edit(int $id, WaStatus $wa): View|RedirectResponse
    {
        $data = GroupWa::findOrFail($id);
        $group = $wa->getGroup();

        if ($group === false) {
            return redirect()->route('pengaturan.wa')->with('error', 'Daftar grup WhatsApp belum dapat dimuat. Periksa konfigurasi Starsender, koneksi, dan status perangkat WhatsApp lalu coba kembali.');
        }

        return view('pengaturan.wa-edit', [
            'data' => $data,
            'group' => $group['data'],
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
