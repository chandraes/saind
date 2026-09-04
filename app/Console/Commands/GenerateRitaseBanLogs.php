<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BanLog;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class GenerateRitaseBanLogs extends Command
{
    protected $signature = 'ban:generate-ritase';
    protected $description = 'Generate dan recalculate ritase ban logs berdasarkan rentang waktu pemakaian serta mencatat riwayat transaksinya';

    public function handle()
    {
        $this->info('Memulai kalkulasi ritase data ban...');
        BanLog::where('posisi_ban_id', 11)->update(['ritase' => 0]); // Reset ritase untuk ban luar (posisi_ban_id = 11)
        $banLogs = BanLog::orderBy('created_at', 'asc')->whereNot('posisi_ban_id', 11)->get();
        $bar = $this->output->createProgressBar(count($banLogs));

        foreach ($banLogs as $banLog) {
            // Cari ban pengganti berikutnya di posisi dan kendaraan yang sama
            $nextBanLog = BanLog::where('vehicle_id', $banLog->vehicle_id)
                ->where('posisi_ban_id', $banLog->posisi_ban_id)
                ->where('created_at', '>', $banLog->created_at)
                ->orderBy('created_at', 'asc')
                ->first();

            $startDate = $banLog->created_at;
            $endDate   = $nextBanLog ? $nextBanLog->created_at : now();

            // Ambil semua transaksi valid (void = 0) dalam rentang waktu pasang ban tersebut
            $transaksis = Transaksi::select('transaksis.id', 'rutes.jarak')
                ->join('kas_uang_jalans', 'transaksis.kas_uang_jalan_id', '=', 'kas_uang_jalans.id')
                ->join('rutes', 'kas_uang_jalans.rute_id', '=', 'rutes.id')
                ->where('kas_uang_jalans.vehicle_id', $banLog->vehicle_id)
                ->where('transaksis.void', 0)
                ->whereBetween('transaksis.created_at', [$startDate, $endDate])
                ->get();

            // Bersihkan data pivot lama untuk ban ini agar tidak duplikat saat di-generate ulang
            DB::table('ban_log_transaksis')->where('ban_log_id', $banLog->id)->delete();

            $totalRitase = 0;
            $pivotData = [];
            $now = now();

            foreach ($transaksis as $trx) {
                // Tentukan nilai ritase berdasarkan jarak
                $jarak = (float) $trx->jarak;
                $nilaiRitase = ($jarak > 50) ? 1.0 : 0.5;

                $totalRitase += $nilaiRitase;

                // Siapkan data untuk riwayat ke tabel pivot
                $pivotData[] = [
                    'ban_log_id'   => $banLog->id,
                    'transaksi_id' => $trx->id,
                    'nilai_ritase' => $nilaiRitase,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            // 1. Update total ritase pada tabel ban_logs
            $banLog->update(['ritase' => $totalRitase]);

            // 2. Simpan histori transaksi ke tabel pivot secara massal (Bulk Insert)
            // Dipecah per 500 baris agar aman dari limitasi string query database
            if (!empty($pivotData)) {
                foreach (array_chunk($pivotData, 500) as $chunk) {
                    DB::table('ban_log_transaksis')->insert($chunk);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Generate ritase dan sinkronisasi histori transaksi berhasil diselesaikan!');
    }
}
