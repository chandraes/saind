<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('load_note_drafts', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('rute_id')->nullable()->constrained('rutes')->restrictOnDelete();
            $table->string('header_name', 200)->nullable();
            $table->string('unit_code', 30)->nullable();
            $table->string('route_reference', 191)->nullable();
            $table->string('date_source', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('load_note_drafts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropConstrainedForeignId('rute_id');
            $table->dropColumn(['header_name', 'unit_code', 'route_reference', 'date_source']);
        });
    }
};
