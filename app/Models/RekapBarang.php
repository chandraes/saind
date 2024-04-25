<?php

namespace App\Models;

use App\Services\StarSender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RekapBarang extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal',
        'jenis_transaksi',
        'barang_id',
        'nama_barang',
        'jumlah',
        'harga_satuan',
        'total',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->orderBy('tahun', 'desc')->get();
    }

    public function getRekapBarang($month, $year)
    {
        return $this->with(['barang', 'barang.kategori_barang'])
                    ->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->get();
    }

    public function jual_umum($data)
    {
        $db = new KasBesar();

        $barang = Barang::find($data['barang_id']);

        $data['harga_satuan'] = $barang->harga_jual;
        $data['tanggal'] = now();


        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $total = $data['jumlah'] * $data['harga_satuan'];

        DB::beginTransaction();

        try {

            $kas = $db->create([
                'tanggal' => $data['tanggal'],
                'uraian' => $data['uraian'],
                'jenis_transaksi_id' => 1,
                'nominal_transaksi' => $total,
                'saldo' => $db->saldoTerakhir() + $total,
                'modal_investor_terakhir' => $db->modalInvestorTerakhir() ,
                'transfer_ke' => $rekening->nama_rekening,
                'bank' => $rekening->nama_bank,
                'no_rekening' => $rekening->nomor_rekening,
            ]);

            $store = $this->create([
                'tanggal' => $data['tanggal'],
                'jenis_transaksi' => 2,
                'barang_id' => $data['barang_id'],
                'nama_barang' => $barang->nama,
                'jumlah' => $data['jumlah'],
                'harga_satuan' => $data['harga_satuan'],
                'total' => $total,
            ]);

            $barang->stok -= $data['jumlah'];
            $barang->save();

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

            return $result;
        }

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "==========================\n".
                    "*Form Jual Barang Umum*\n".
                    "==========================\n\n".
                    "Uraian : ".$kas['uraian']."\n".
                    "Barang : ".$barang->nama."\n".
                    "Jumlah : ".$data['jumlah']."\n".
                    "Total :  *Rp. ".number_format($total, 0, ',', '.')."*\n".
                    "==========================\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return $result;

    }

}
