<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('load_note_drafts', function (Blueprint $table): void {
            $table->unsignedInteger('revision')->default(0);
            $table->foreignId('transaksi_id')->nullable()->unique()->constrained('transaksis')->restrictOnDelete();
        });
        Schema::create('load_note_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('load_note_draft_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 30);
            $table->text('reason');
            $table->json('before');
            $table->json('after');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('load_note_events');
        Schema::table('load_note_drafts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('transaksi_id');
            $table->dropColumn('revision');
        });
    }
};
