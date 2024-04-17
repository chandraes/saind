<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BarangMaintenance extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['nf_harga_jual'];

    public function getNfHargaJualAttribute()
    {
        return number_format($this->harga_jual, 0, ',', '.');
    }

    public function jualVendorStore($data)
    {
        $kv = new KasVendor();

        $data['tanggal'] = date('Y-m-d');

        $barang = $this->find($data['barang_maintenance_id']);

        $data['uraian'] = 'Maintenance ' . $barang->nama;

        $data['quantity'] = $data['jumlah'];
        $data['harga_satuan'] = $barang->harga_jual;
        $data['pinjaman'] = $barang->harga_jual * $data['quantity'];
        $data['sisa'] = $kv->sisa_terakhir($data['vendor_id']) + $data['pinjaman'];
        $data['nama_barang'] = $barang->nama;



        DB::beginTransaction();

        try {

            $this->rekap_maintenance_store($data);
            $this->maintenance_store($data);

            unset($data['barang_maintenance_id']);
            unset($data['jumlah']);
            unset($data['nama_barang']);

            $kv->create($data);

            $barang->update(['stok' => $barang->stok - $data['quantity']]);
            // $this->maintenance_store($data);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            $result = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];

            return $result;
        }

        $result = [
            'status' => 'success',
            'message' => 'Berhasil menambahkan data',
        ];

        return $result;

    }

    private function maintenance_store($data)
    {
        $db = new MaintenanceLog();

        $store = $db->create([
            'barang_maintenance_id' => $data['barang_maintenance_id'],
            'vehicle_id' => $data['vehicle_id'],
            'odometer' => $data['odometer'] ?? null,
        ]);

        return true;
    }

    private function rekap_maintenance_store($data)
    {
        $db = new RekapBarangMaintenance();

        $db->create([
            'jenis_transaksi' => 1,
            'barang_maintenance_id' => $data['barang_maintenance_id'],
            'nama_barang' => $data['nama_barang'],
            'jumlah' => $data['quantity'],
            'harga_satuan' => $data['harga_satuan'],
            'total' => $data['pinjaman'],
            'vendor_id' => $data['vendor_id'] ?? null,
        ]);
    }
}
