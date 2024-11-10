<?php

use App\Models\InvoiceTagihan;
use App\Models\Pajak\PphPerusahaan;
use App\Models\Pajak\PpnKeluaran;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $invoiceTagihan = InvoiceTagihan::where('lunas', 0)->where('ppn', '>', 0)->get();
        $ppnInvoice = InvoiceTagihan::where('lunas', 0)->where('pph', '>', 0)->get();

        foreach ($invoiceTagihan as $invoice) {
            PpnKeluaran::create([
                'invoice_tagihan_id' => $invoice->id,
                'uraian' => 'PPN '. $invoice->periode,
                'nominal' => $invoice->ppn,
                'dipungut' => $invoice->ppn_dipungut,
            ]);
        }

        foreach ($ppnInvoice as $invoice) {
            PphPerusahaan::create([
                'invoice_tagihan_id' => $invoice->id,
                'uraian' => 'PPH '. $invoice->periode,
                'nominal' => $invoice->pph,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
