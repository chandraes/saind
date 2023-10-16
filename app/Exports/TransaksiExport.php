<?php

namespace App\Exports;

use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransaksiExport implements FromQuery, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function title(): string
    {
        return 'Judul Excel';
    }

    public function query()
    {
        return Transaksi::query()->select('kuj.tanggal', DB::raw('CONCAT(kuj.kode_uang_jalan,kuj.nomor_uang_jalan)'), 'v.nomor_lambung', 'r.nama', 'transaksis.tanggal_muat',
                                        'transaksis.nota_muat', 'transaksis.tonase', 'transaksis.tanggal_bongkar', 'transaksis.nota_bongkar', 'transaksis.timbangan_bongkar', 'transaksis.nominal_tagihan')
                            ->join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')->where('transaksis.status', 3)
                            ->join('vehicles as v', 'kuj.vehicle_id', 'v.id')
                            ->join('rutes as r', 'kuj.rute_id', 'r.id')
                            ->where('transaksis.tagihan', 0)->where('kuj.customer_id', $this->id);
    }

    public function headings(): array
    {
        return [
           ['Tanggal', 'Kode UJ', 'Nomor Lambung', 'Rute', 'Tanggal Muat', 'Nota Muat', 'Timbangan Muat', 'Tanggal Bongkar', 'Nota Bongkar', 'Timbangan Bongkar', 'Tagihan'],
        ];
    }


}
