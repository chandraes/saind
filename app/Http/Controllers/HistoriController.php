<?php

namespace App\Http\Controllers;

use App\Models\PesanWa;
use App\Services\StarSender;
use Illuminate\Http\Request;

class HistoriController extends Controller
{
    public function index(Request $request)
    {
        // Jika request dari Datatables (AJAX)
        if ($request->ajax()) {
            $data = PesanWa::leftJoin('group_was', 'pesan_was.tujuan', '=', 'group_was.nama_group')
                ->select('pesan_was.*', 'group_was.group_id')
                ->orderBy('pesan_was.id', 'desc');

            return datatables()->of($data)
                ->addIndexColumn()
                ->editColumn('pesan', function ($row) {
                    // Potong pesan agar tabel tetap rapi
                    return substr($row->pesan, 0, 60) . '...';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        return '<span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">Belum Terkirim</span>';
                    }
                    return '<span class="badge bg-success px-3 py-2 rounded-pill shadow-sm">Terkirim</span>';
                })
               ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex justify-content-center gap-2">';

                    // Gunakan htmlspecialchars agar karakter unik WA aman dimasukkan ke dalam atribut HTML
                    $pesanAman = htmlspecialchars($row->pesan, ENT_QUOTES, 'UTF-8');

                    // Hapus onclick, ganti dengan class btn-detail dan atribut data-pesan
                    $btn .= '<button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm btn-detail" data-pesan="'.$pesanAman.'"><i class="fa fa-eye me-1"></i> Detail</button>';

                    // Tombol Kirim Ulang (Hanya jika belum terkirim)
                    if ($row->status == 0) {
                        $resendUrl = route('pengaturan.histori.resend', $row->id);
                        $btn .= '<form action="'.$resendUrl.'" method="post" class="d-inline form-resend">'.
                                csrf_field().
                                '<button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm btn-resend"><i class="fa fa-paper-plane me-1"></i> Kirim Ulang</button>'.
                                '</form>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('pengaturan.histori.index');
    }

    public function resend(PesanWa $pesanWa)
    {
        $starSender =  new StarSender($pesanWa->tujuan, $pesanWa->pesan);
        $res = $starSender->sendGroup();

        if($res == 'true'){
            $pesanWa->update(['status' => 1]);
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
