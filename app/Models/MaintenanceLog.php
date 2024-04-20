<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) tahun')->groupBy('tahun')->orderBy('tahun', 'desc')->get();
    }
}
