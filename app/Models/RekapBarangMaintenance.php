<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapBarangMaintenance extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function barang_maintenance()
    {
        return $this->belongsTo(BarangMaintenance::class);
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function getNfHargaSatuanAttribute()
    {
        return number_format($this->harga_satuan, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function beliStore()
    {

        $keranjang = KeranjangMaintenance::where('user_id', auth()->user()->id)->get();

        foreach ($keranjang as $k) {
            $data = [
                'jenis_transaksi' => 0,
                'barang_maintenance_id' => $k->barang_maintenance_id,
                'nama_barang' => $k->barang_maintenance->nama,
                'jumlah' => $k->jumlah,
                'harga_satuan' => $k->harga_satuan,
                'total' => $k->total,
            ];

            // increment stok barang
            $barang = BarangMaintenance::find($k->barang_maintenance_id);
            $barang->stok += $k->jumlah;
            $barang->save();

            $this->create($data);
        }

        return true;
    }
}
