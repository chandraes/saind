<?php

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
       Schema::create('ban_ganti_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice', 50)->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('pembayaran', 50)->default('dibayar_sendiri');
            $table->decimal('total_nominal', 15, 2)->default(0);
            $table->date('tanggal');
            $table->timestamps();
        });

        // Detail Ban dalam Invoice
        Schema::create('ban_ganti_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ban_ganti_invoice_id')->constrained('ban_ganti_invoices')->onDelete('cascade');
            $table->foreignId('ban_log_id')->nullable()->constrained('ban_logs')->onDelete('set null');
            $table->foreignId('posisi_ban_id')->constrained('posisi_bans')->onDelete('cascade');
            $table->string('sumber_ban', 20)->default('baru');
            $table->string('merk', 100);
            $table->string('no_seri', 100);
            $table->integer('kondisi');
            $table->timestamps();
        });

        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->foreignId('ban_ganti_invoice_id')->nullable()->constrained('ban_ganti_invoices')->onDelete('set null');
        });

        Schema::table('kas_besars', function (Blueprint $table) {
            $table->foreignId('ban_ganti_invoice_id')->nullable()->constrained('ban_ganti_invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropForeign(['ban_ganti_invoice_id']);
            $table->dropColumn('ban_ganti_invoice_id');
        });
        
        Schema::table('kas_vendors', function (Blueprint $table) {
            $table->dropForeign(['ban_ganti_invoice_id']);
            $table->dropColumn('ban_ganti_invoice_id');
        });

        Schema::dropIfExists('ban_ganti_invoice_details');
        Schema::dropIfExists('ban_ganti_invoices');
    }
};
