<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use App\Models\Barang;
use App\Models\KeranjangBelanja;
use App\Models\RekapBarang;
use App\Models\KasBesar;
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

        $last = KasBesar::latest()->first();

        $total = $keranjang->sum('total');

        if ($total > $last->saldo) {
            return redirect()->route('billing.form-barang.beli')->with('error', 'Saldo tidak cukup');
        }


        $data['tanggal'] = now();
        $data['jenis_transaksi_id'] = 2;
        $data['nominal_transaksi'] = $total;
        $data['saldo'] = $last->saldo - $total;
        $data['modal_investor_terakhir'] = $last->modal_investor_terakhir;
        $data['uraian'] = 'Pembelian barang';

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

        KasBesar::create($data);

        KeranjangBelanja::where('user_id', $user_id)->delete();

        return redirect()->route('billing.form-barang.beli')->with('success', 'Berhasil membeli barang');

    }
}
