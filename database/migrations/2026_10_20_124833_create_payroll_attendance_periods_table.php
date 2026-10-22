<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_attendance_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
        DB::table('payroll_attendance_settings')->insert(['id' => 1, 'enabled' => true, 'created_at' => now(), 'updated_at' => now()]);
        Schema::create('payroll_attendance_periods', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->date('from_date');
            $table->date('to_date');
            $table->uuid('request_id')->unique();
            $table->json('snapshot');
            $table->json('overrides')->nullable();
            $table->timestamp('fetched_at');
            $table->timestamp('lock_attempted_at')->nullable();
            $table->timestamp('remote_locked_at')->nullable();
            $table->timestamp('sealed_at')->nullable();
            $table->unsignedBigInteger('rekap_gaji_id')->nullable();
            $table->timestamps();
            $table->unique(['year', 'month']);
        });
        Schema::create('payroll_attendance_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name');
            $table->string('action');
            $table->unsignedBigInteger('period_id')->nullable()->index();
            $table->json('details');
            $table->timestamp('created_at');
        });
        Schema::table('rekap_gaji_details', function (Blueprint $table): void {
            $table->unsignedBigInteger('potongan_absensi')->default(0);
            $table->unsignedBigInteger('potongan_absensi_sebelum_batas')->default(0);
            $table->json('rincian_absensi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rekap_gaji_details', fn (Blueprint $table) => $table->dropColumn(['potongan_absensi', 'potongan_absensi_sebelum_batas', 'rincian_absensi']));
        Schema::dropIfExists('payroll_attendance_events');
        Schema::dropIfExists('payroll_attendance_periods');
        Schema::dropIfExists('payroll_attendance_settings');
    }
};
