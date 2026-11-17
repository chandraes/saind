<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan tampilan Dashboard Pengingat per akun: urutan tabel dan aktif/nonaktif.
 * Tanpa baris = semua tabel milik role aktif dengan urutan bawaan (ReminderRegistry).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_reminder_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reminder_key', 30);
            $table->unsignedTinyInteger('position');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'reminder_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reminder_settings');
    }
};
