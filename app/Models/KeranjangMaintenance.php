<?php

namespace App\Models;

use App\Services\StarSender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KeranjangMaintenance extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['nf_harga_satuan', 'nf_total'];

    public function barang_maintenance()
    {
        return $this->belongsTo(BarangMaintenance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNfHargaSatuanAttribute()
    {
        return number_format($this->harga_satuan, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function beliStore($data)
    {
        $db = new KasBesar();
        $rekap = new RekapBarangMaintenance();

        $keranjang = $this->where('user_id', auth()->user()->id)->get();

        if ($keranjang->count() == 0) {

            $result = [
                'status' => 'error',
                'message' => 'Keranjang belanja masih kosong',
            ];

            return $result;
        }

        $data['nominal_transaksi'] = $keranjang->sum('total');

        DB::beginTransaction();

        try {

            $rekap->beliStore();

            $store = $db->keluarStore($data);

            $this->where('user_id', auth()->user()->id)->delete();

            $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Beli Barang Maintenance*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Uraian : ".$store->uraian."\n".
                        "Nilai :  *Rp. ".number_format($store->nominal_transaksi, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Bank     : ".$store->bank."\n".
                        "Nama    : ".$store->transfer_ke."\n".
                        "No. Rek : ".$store->no_rekening."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar : \n".
                        "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            $result = [
                'status' => 'success',
                'message' => 'Berhasil beli barang maintenance',
            ];
            
            $this->sendWa($pesan);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $result = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];

        }

        return $result;
    }

    private function sendWa($pesan)
    {
        $group = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

        $send = new StarSender($group, $pesan);
        $res = $send->sendGroup();

        return $res;
    }
}
