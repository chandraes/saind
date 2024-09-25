<?php

use App\Models\InvoiceTagihan;
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
        Schema::table('transaksis', function (Blueprint $table) {
            $table->boolean('keranjang')->default(0)->after('csr');
        });

        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->bigInteger('total_awal')->default(0)->after('customer_id');
            $table->bigInteger('penyesuaian')->default(0)->after('total_awal');
            $table->date('tanggal_hardcopy')->nullable()->after('sisa_tagihan');
            $table->date('estimasi_pembayaran')->nullable()->after('tanggal_hardcopy');
            $table->bigInteger('ppn')->default(0)->after('customer_id');
            $table->bigInteger('pph')->default(0)->after('ppn');
        });

        $data = InvoiceTagihan::all();

        foreach ($data as $invoice) {
            $invoice->total_awal = $invoice->total_tagihan;
            $invoice->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('keranjang');
        });

        Schema::table('invoice_tagihans', function (Blueprint $table) {
            $table->dropColumn('total_awal');
            $table->dropColumn('ppn');
            $table->dropColumn('pph');
            $table->dropColumn('penyesuaian');
            $table->dropColumn('tanggal_hardcopy');
            $table->dropColumn('estimasi_pembayaran');
        });
    }
};
