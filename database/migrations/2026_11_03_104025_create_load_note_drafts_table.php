<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('load_note_drafts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->uuid('request_id');
            $table->string('payload_hash', 64);
            $table->string('ticket_number', 100);
            $table->string('mine_name', 200);
            $table->date('loaded_on');
            $table->string('vehicle_number', 100);
            $table->string('driver_name', 120)->nullable();
            $table->unsignedInteger('gross_kg');
            $table->unsignedInteger('tare_kg');
            $table->unsignedInteger('net_kg');
            $table->string('photo_path');
            $table->string('photo_mime', 50);
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'request_id']);
            $table->index(['vendor_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('load_note_drafts');
    }
};
