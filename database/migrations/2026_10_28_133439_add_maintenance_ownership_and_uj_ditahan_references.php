<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aki_ganti_carts', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->unique(['user_id', 'vehicle_id', 'posisi_aki_id'], 'aki_cart_user_vehicle_position_unique');
        });
        DB::table('aki_ganti_carts')->whereNull('user_id')->delete();
        Schema::table('aki_ganti_invoices', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('authorized_at')->nullable();
        });
        Schema::table('uj_ditahan_details', function (Blueprint $table): void {
            $table->foreignId('aki_ganti_invoice_id')->nullable()->constrained('aki_ganti_invoices')->restrictOnDelete();
            $table->foreignId('filter_oli_ganti_invoice_id')->nullable()->constrained('filter_oli_ganti_invoices')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('uj_ditahan_details', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('aki_ganti_invoice_id');
            $table->dropConstrainedForeignId('filter_oli_ganti_invoice_id');
        });
        Schema::table('aki_ganti_invoices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('authorized_by');
            $table->dropColumn('authorized_at');
        });
        Schema::table('aki_ganti_carts', function (Blueprint $table): void {
            $table->dropUnique('aki_cart_user_vehicle_position_unique');
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
