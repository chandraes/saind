<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uj_ditahan_payout_cancellations', function (Blueprint $t): void {
            $t->unsignedBigInteger('reversal_detail_id')->nullable();
            $t->unique(['fund', 'reversal_detail_id'], 'uj_payout_reversal_unique');
        });
        DB::table('uj_ditahan_payout_cancellations')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::transaction(function () use ($row): void {
                    $audit = DB::table('uj_ditahan_payout_cancellations')->where('id', $row->id)->lockForUpdate()->first();
                    if ($audit->reversal_detail_id !== null) {
                        return;
                    }
                    if (! in_array($audit->fund, ['ban', 'sparepart'], true)) {
                        throw new RuntimeException('Jenis dana pembatalan tidak valid.');
                    }
                    $ban = $audit->fund === 'ban';
                    $accounts = $ban ? 'uj_ditahan_bans' : 'uj_ditahans';
                    $details = $ban ? 'uj_ditahan_ban_details' : 'uj_ditahan_details';
                    $foreign = $ban ? 'uj_ditahan_ban_id' : 'uj_ditahan_id';
                    $account = DB::table($accounts)->where('id', $audit->account_id)->lockForUpdate()->first();
                    $original = DB::table($details)->where('id', $audit->detail_id)->first();
                    if (! $account || ! $original || (int) $original->{$foreign} !== (int) $account->id) {
                        throw new RuntimeException('Referensi pembatalan tidak konsisten.');
                    }
                    $reverseId = DB::table($details)->insertGetId([$foreign => $account->id, 'jenis' => 'masuk', 'nominal' => $audit->nominal, 'payout_origin' => 'payout_reversal', 'keterangan' => mb_substr('Pembatalan pencairan #'.$audit->detail_id.' — '.$audit->reason, 0, 255), 'driver_id' => $original->driver_id, 'created_at' => $audit->created_at, 'updated_at' => $audit->created_at]);
                    DB::table($accounts)->where('id', $account->id)->update(['total_masuk' => (int) $account->total_masuk + (int) $audit->nominal, 'total_keluar' => (int) $account->total_keluar + (int) $audit->nominal]);
                    DB::table('uj_ditahan_payout_cancellations')->where('id', $audit->id)->update(['reversal_detail_id' => $reverseId]);
                });
            }
        });
    }

    public function down(): void
    {
        if (DB::table('uj_ditahan_payout_cancellations')->whereNotNull('reversal_detail_id')->exists()) {
            throw new RuntimeException('Referensi transaksi pembalik tidak boleh dihapus setelah digunakan.');
        }
        Schema::table('uj_ditahan_payout_cancellations', function (Blueprint $t): void {
            $t->dropUnique('uj_payout_reversal_unique');
            $t->dropColumn('reversal_detail_id');
        });
    }
};
