<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use App\Models\Barang;
use App\Models\KeranjangBelanja;
use App\Models\RekapBarang;
use App\Models\KasBesar;
use App\Models\Rekening;
use App\Models\GroupWa;
use App\Services\StarSender;
use Illuminate\Http\Request;

class FormBarangController extends Controller
{
    public function beli()
    {
        $kategori = KategoriBarang::all();
        $keranjang = KeranjangBelanja::where('user_id', auth()->user()->id)->get();
        return view('billing.barang.beli', [
            'kategori' => $kategori,
            'keranjang' => $keranjang,
        ]);
    }

    public function get_barang(Request $request)
    {
        $barang = Barang::where('kategori_barang_id', $request->kategori_barang_id)->get();

        return response()->json($barang);
    }

    public function keranjang()
    {

    }

    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'barang_id' => 'required',
            'jumlah' => 'required',
            'harga_satuan' => 'required',
        ]);

        $data['user_id'] = auth()->user()->id;

        $data['harga_satuan'] = str_replace('.', '', $data['harga_satuan']);

        $data['total'] = $data['jumlah'] * $data['harga_satuan'];

        KeranjangBelanja::create($data);

        return redirect()->route('billing.form-barang.beli')->with('success', 'Berhasil menambahkan barang ke keranjang');
    }

    public function keranjang_destroy(KeranjangBelanja $keranjang)
    {
        $keranjang->delete();

        return redirect()->route('billing.form-barang.beli')->with('success', 'Berhasil menghapus barang dari keranjang');
    }

    public function keranjang_empty()
    {
        KeranjangBelanja::where('user_id', auth()->user()->id)->delete();

        return redirect()->route('billing.form-barang.beli')->with('success', 'Berhasil mengosongkan keranjang');
    }

    public function beli_store()
    {
        $user_id = auth()->user()->id;

        $keranjang = KeranjangBelanja::where('user_id', $user_id)->get();

        if ($keranjang->count() == 0) {
            return redirect()->route('billing.form-barang.beli')->with('error', 'Keranjang kosong');
        }

        $last = KasBesar::latest()->first();

        $total = $keranjang->sum('total');

        if ($total > $last->saldo) {
            return redirect()->route('billing.form-barang.beli')->with('error', 'Saldo tidak cukup');
        }


        $kas['tanggal'] = now();
        $kas['jenis_transaksi_id'] = 2;
        $kas['nominal_transaksi'] = $total;
        $kas['saldo'] = $last->saldo - $total;
        $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;
        $kas['uraian'] = 'Pembelian barang';
        $kas['transfer_ke'] = 'Toko';
        $kas['bank'] = '-';
        $kas['no_rekening'] = '-';

        foreach ($keranjang as $k) {
            $data = [
                'tanggal' => now(),
                'jenis_transaksi' => 1,
                'barang_id' => $k->barang_id,
                'nama_barang' => $k->barang->nama,
                'jumlah' => $k->jumlah,
                'harga_satuan' => $k->harga_satuan,
                'total' => $k->total,
            ];


            // increment stok barang
            $barang = Barang::find($k->barang_id);
            $barang->stok += $k->jumlah;
            $barang->save();

            RekapBarang::create($data);
        }

        $store = KasBesar::create($kas);

        KeranjangBelanja::where('user_id', $user_id)->delete();

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Beli Barang*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nilai :  *Rp. ".number_format($kas['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$kas['bank']."\n".
                    "Nama    : ".$kas['transfer_ke']."\n".
                    "No. Rek : ".$kas['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.form-barang.beli')->with('success', 'Berhasil membeli barang');

    }
}
