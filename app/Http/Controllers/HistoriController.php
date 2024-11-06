<?php

namespace App\Http\Controllers;

use App\Models\PesanWa;
use App\Services\StarSender;
use Illuminate\Http\Request;

class HistoriController extends Controller
{
    public function index()
    {
        $data = PesanWa::leftJoin('group_was', 'pesan_was.tujuan', '=', 'group_was.nama_group')
            ->select('pesan_was.*', 'group_was.group_id')
            ->orderBy('pesan_was.id', 'desc')
            ->limit(10)
            ->get();

        return view('pengaturan.histori.index', [
            'data' => $data
        ]);
    }

    public function resend(PesanWa $pesanWa)
    {
        $starSender =  new StarSender($pesanWa->tujuan, $pesanWa->pesan);
        $res = $starSender->sendGroup();

        if($res == 'true'){


            $pesanWa->update([
                'status' => 1
            ]);

        } else {
            return redirect()->back()->with('error', 'Gagal mengirim ulang pesan! Silahkan hubungi admin');
        }

        return redirect()->back()->with('success', 'Berhasil mengirim ulang pesan');
    }

    public function delete_sended()
    {
        PesanWa::where('status', 1)->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus pesan yang sudah terkirim');
    }
}
