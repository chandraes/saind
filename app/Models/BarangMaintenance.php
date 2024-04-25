<?php

namespace App\Models;

use App\Services\StarSender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BarangMaintenance extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['nf_harga_jual'];

    public function kategori()
    {
        return $this->belongsTo(KategoriBarangMaintenance::class, 'kategori_barang_maintenance_id');
    }

    public function getNfHargaJualAttribute()
    {
        return number_format($this->harga_jual, 0, ',', '.');
    }

    public function jualVendorStore($data)
    {
        $kv = new KasVendor();

        $data['tanggal'] = date('Y-m-d');

        $barang = $this->find($data['barang_maintenance_id']);
        $vehicle = Vehicle::find($data['vehicle_id']);

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

            $store = $kv->create($data);

            $pesan =    "==========================\n".
                        "*Form Jual Barang Maintenance*\n".
                        "==========================\n\n".
                        "No. Lambung : ".$vehicle->nomor_lambung."\n".
                        "Vendor : ".$vehicle->vendor->nama."\n\n".
                        "Barang : ".$barang->nama."\n".
                        "Jumlah : ".$data['quantity']."\n".
                        "Total :  *Rp. ".number_format($data['pinjaman'], 0, ',', '.')."*\n".
                        "==========================\n\n".
                        "Total Kasbon: Rp. ".number_format($store->sisa, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            $barang->update(['stok' => $barang->stok - $data['quantity']]);
            // $this->maintenance_store($data);

            DB::commit();

            $tujuan = GroupWa::where('untuk', 'team')->first()->nama_group;

            $this->send_wa($tujuan, $pesan);

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

        $barang = $this->find($data['barang_maintenance_id']);

        $store = $db->create([
            'barang_maintenance_id' => $data['barang_maintenance_id'],
            'vehicle_id' => $data['vehicle_id'],
            'kategori_barang_maintenance_id' => $barang->kategori_barang_maintenance_id,
            'qty' => $data['quantity'],
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

    private function send_wa($tujuan, $pesan)
    {

        $send = new StarSender($tujuan, $pesan);
        $res = $send->sendGroup();

        return true;
    }

    public function jual_umum($data)
    {
        $db = new KasBesar();

        $rekap = new RekapBarangMaintenance();

        $barang = $this->find($data['barang_maintenance_id']);

        try {
            $data['harga_satuan'] = $barang->harga_jual;
            $data['tanggal'] = now();

            $rekening = Rekening::where('untuk', 'kas-besar')->first();

            $total = $data['jumlah'] * $data['harga_satuan'];

            DB::beginTransaction();

            $kas = $db->create([
                'tanggal' => $data['tanggal'],
                'uraian' => $data['uraian'],
                'jenis_transaksi_id' => 1,
                'nominal_transaksi' => $total,
                'saldo' => $db->saldoTerakhir() + $total,
                'modal_investor_terakhir' => $db->modalInvestorTerakhir(),
                'transfer_ke' => $rekening->nama_rekening,
                'bank' => $rekening->nama_bank,
                'no_rekening' => $rekening->nomor_rekening,
            ]);

            $store = $rekap->create([
                'tanggal' => $data['tanggal'],
                'jenis_transaksi' => 1,
                'barang_maintenance_id' => $data['barang_maintenance_id'],
                'nama_barang' => $barang->nama,
                'jumlah' => $data['jumlah'],
                'harga_satuan' => $data['harga_satuan'],
                'total' => $total,
            ]);

            DB::commit();

            $result = [
                'status' => 'success',
                'message' => 'Data berhasil disimpan!',
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            $result = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return $result;
    }
}
