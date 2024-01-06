<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KasDireksi extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function direksi()
    {
        return $this->belongsTo(Direksi::class);
    }

    public function getIdTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal));
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->orderBy('tahun', 'desc')->get();
    }

    public function lastKas($direksi_id, $month, $year)
    {
        return $this->where('direksi_id', $direksi_id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->latest()->orderBy('id', 'desc')->first();
    }

    public function kas_now($direksi_id, $month, $year)
    {
        return $this->where('direksi_id', $direksi_id)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->get();
    }

    public function sisa_kas_terakhir($direksiId)
    {
        return $this->where('direksi_id', $direksiId)->latest()->orderBy('id', 'desc')->first()->sisa_kas ?? 0;
    }

    public function total_kas($direksi_id, $month, $year)
    {
        // sum total kas - sum total bayar in <= this month
        $date = $year . '-' . $month . '-31';

        return $this->where('direksi_id', $direksi_id)
                    ->whereDate('tanggal', '<=', $date)
                    ->selectRaw('sum(total_kas) - sum(total_bayar) as total')
                    ->pluck('total')
                    ->first() ?? 0;
    }

}
