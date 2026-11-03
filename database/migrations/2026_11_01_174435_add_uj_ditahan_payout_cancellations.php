<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['uj_ditahan_details' => ['aki_ganti_invoice_id', 'filter_oli_ganti_invoice_id'], 'uj_ditahan_ban_details' => ['ban_ganti_invoice_id']] as $table => $references) {
            Schema::table($table, function (Blueprint $t): void {
                $t->string('payout_origin', 20)->nullable();
            });
            $query = DB::table($table)->where('jenis', 'keluar')->whereNotNull('file_pdf')->where('file_pdf', '<>', '')->whereNull('transaksi_id')->where(function ($q): void {
                $q->whereNull('keterangan')->orWhere('keterangan', '<>', 'Cutoff');
            });
            foreach ($references as $reference) {
                $query->whereNull($reference);
            }
            $query->update(['payout_origin' => 'manual']);
        }
        Schema::create('uj_ditahan_payout_cancellations', function (Blueprint $t): void {
            $t->id();
            $t->string('fund', 12);
            $t->unsignedBigInteger('detail_id');
            $t->unsignedBigInteger('account_id');
            $t->bigInteger('nominal');
            $t->text('reason');
            $t->foreignId('cancelled_by')->constrained('users')->restrictOnDelete();
            $t->timestamps();
            $t->unique(['fund', 'detail_id'], 'uj_payout_cancellation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uj_ditahan_payout_cancellations');
        foreach (['uj_ditahan_details', 'uj_ditahan_ban_details'] as $table) {
            Schema::table($table, function (Blueprint $t): void {
                $t->dropColumn('payout_origin');
            });
        }
    }
};
