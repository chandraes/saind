<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilterOliGantiInvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = ['filter_oli_ganti_invoice_id', 'filter_oli_log_id', 'kategori_filter_oli_mesin_id', 'merk', 'kondisi', 'ritase', 'limit_ritase', 'created_at'];

    protected $attributes = ['kondisi' => 100, 'ritase' => 0];

    protected function casts(): array
    {
        return ['kondisi' => 'integer', 'ritase' => 'decimal:1'];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriFilterOliMesin::class, 'kategori_filter_oli_mesin_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FilterOliGantiInvoice::class, 'filter_oli_ganti_invoice_id');
    }
}
