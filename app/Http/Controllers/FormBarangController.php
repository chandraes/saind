<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use App\Models\Barang;
use App\Models\KeranjangBelanja;
use App\Models\RekapBarang;
use App\Models\KasVendor;
use App\Models\Vendor;
use App\Models\Vehicle;
use App\Models\KasBesar;
use App\Models\Transaksi;
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

    public function beli_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'bank' => 'required',
            'transfer_ke' => 'required',
            'no_rekening' => 'required',
        ]);

        $user_id = auth()->user()->id;

        $keranjang = KeranjangBelanja::where('user_id', $user_id)->get();

        if ($keranjang->count() == 0) {
            return redirect()->route('billing.form-barang.beli')->with('error', 'Keranjang kosong');
        }

        $last = KasBesar::latest()->first();

        $total = $keranjang->sum('total');

        if ($total > $last->saldo) {
            return redirect()->route('billing.form-barang.beli')->with('error', 'Saldo kas besar tidak cukup!');
        }

        $kas['tanggal'] = now();
        $kas['jenis_transaksi_id'] = 2;
        $kas['nominal_transaksi'] = $total;
        $kas['saldo'] = $last->saldo - $total;
        $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;
        $kas['uraian'] = $data['uraian'];
        $kas['transfer_ke'] = $data['transfer_ke'];
        $kas['bank'] = $data['bank'];
        $kas['no_rekening'] = $data['no_rekening'];

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
                    "Uraian : ".$kas['uraian']."\n".
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

    public function get_harga_jual(Request $request)
    {
        $barang = Barang::find($request->barang_id);

        return response()->json($barang);
    }

    public function jual()
    {
        $vehicle = Vehicle::where('status', 'aktif')->get();

        $kategori = KategoriBarang::all();

        return view('billing.barang.jual', [
            'kategori' => $kategori,
            'vehicle' => $vehicle,
        ]);
    }

    public function jual_store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required',
            'id' => 'required',
            'barang_id' => 'required',
            'jumlah' => 'required',
        ]);

        $barang = Barang::find($data['barang_id']);

        $vehicle = Vehicle::find($data['id']);

        if ($barang->stok < $data['jumlah']) {
            return redirect()->route('billing.index')->with('error', 'Stok barang tidak cukup');
        }

        $vendor = Vendor::find($data['vendor_id']);

        $sisa = KasVendor::where('vendor_id', $data['vendor_id'])->latest()->orderBy('id', 'desc')->first()->sisa ?? 0;

        $plafon = ($vendor->plafon_lain * $vendor->vehicle->count()) + ($vendor->plafon_titipan * $vendor->vehicle->count()) - $sisa;

        $kas['vendor_id'] = $data['vendor_id'];
        $kas['vehicle_id'] = $data['id'];
        $kas['tanggal'] = now();
        $kas['quantity'] = $data['jumlah'];
        $kas['harga_satuan'] = Barang::find($data['barang_id'])->harga_jual;
        $kas['pinjaman'] = $kas['harga_satuan'] * $kas['quantity'];
        $kas['uraian'] = 'Beli '.$barang->kategori_barang->nama.' '.$barang->nama;

        $last = KasVendor::where('vendor_id', $kas['vendor_id'])->latest()->orderBy('id', 'desc')->first();

        if ($last) {
            $kas['sisa'] = $last->sisa + $kas['pinjaman'];
        } else {
            $kas['sisa'] = $kas['pinjaman'];
        }

        $data['tanggal'] = now();
        $data['jenis_transaksi'] = 2;
        $data['harga_satuan'] = Barang::find($data['barang_id'])->harga_jual;
        $data['total'] = $data['jumlah'] * $data['harga_satuan'];
        $data['nama_barang'] = Barang::find($data['barang_id'])->nama;

        if ($plafon < $data['total']) {
            return redirect()->back()->with('error', 'Kas sudah melebihi plafon');
        }

        $store = KasVendor::create($kas);

        RekapBarang::create($data);

        $barang->stok -= $data['jumlah'];
        $barang->save();

        $group = GroupWa::where('untuk', 'team')->first();

        $pesan =    "==========================\n".
                    "*Form Jual Barang*\n".
                    "==========================\n\n".
                    "No. Lambung : ".$vehicle->nomor_lambung."\n".
                    "Vendor : ".$vehicle->vendor->nama."\n\n".
                    "Barang : ".$barang->nama."\n".
                    "Jumlah : ".$data['jumlah']."\n".
                    "Total :  *Rp. ".number_format($data['total'], 0, ',', '.')."*\n".
                    "==========================\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Berhasil menjual barang');

    }
}
