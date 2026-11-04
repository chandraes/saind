<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('load_note_drafts', function (Blueprint $table): void {
            $table->unsignedBigInteger('transfer_override')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('load_note_drafts', function (Blueprint $table): void {
            $table->dropColumn('transfer_override');
        });
    }
};
