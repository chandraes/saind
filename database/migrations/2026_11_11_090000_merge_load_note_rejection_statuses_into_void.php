<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Tolak dan Batalkan digabung menjadi satu keputusan: Void. */
    public function up(): void
    {
        DB::table('load_note_drafts')->whereIn('status', ['rejected', 'cancelled'])->update(['status' => 'void']);
    }

    public function down(): void
    {
        DB::table('load_note_drafts')->where('status', 'void')->update(['status' => 'cancelled']);
    }
};
