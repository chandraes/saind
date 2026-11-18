<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Isi Nota Bongkar (status 2 → 3): nominal tagihan, bayar, CSR, bonus, dan
 * profit dihitung ulang, lalu DO nolam bertambah. Dipakai bersama halaman web
 * Nota Bongkar dan API mobile (Admin & SU) agar hasilnya sama persis.
 */
class UnloadingNoteCompletion
{
    /**
     * Ketukan ulang aman: nota yang sudah diisi (bukan status 2) ditolak,
     * sehingga DO nolam tidak bertambah dua kali.
     *
     * @param  array{nota_bongkar: string, timbangan_bongkar: float|int|string, tanggal_bongkar: string, gross_bongkar?: mixed, tarra_bongkar?: mixed}  $input
     */
    public function complete(int $id, array $input): Transaksi
    {
        return DB::transaction(function () use ($id, $input): Transaksi {
            $transaksi = Transaksi::with(['kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'kas_uang_jalan.vendor'])
                ->lockForUpdate()->findOrFail($id);
            if ((int) $transaksi->status !== 2 || (int) $transaksi->void !== 0) {
                throw ValidationException::withMessages(['transaksi' => 'Nota bongkar ini sudah diisi atau dibatalkan.']);
            }
            $data = [
                'nota_bongkar' => $input['nota_bongkar'],
                'timbangan_bongkar' => $input['timbangan_bongkar'],
                'gross_bongkar' => $input['gross_bongkar'] ?? null,
                'tarra_bongkar' => $input['tarra_bongkar'] ?? null,
                'tanggal_bongkar' => Transaksi::unloadingTimestamp($input['tanggal_bongkar']),
                'status' => 3,
            ];
            if (strtotime($data['tanggal_bongkar']) < strtotime($transaksi->tanggal_muat)) {
                throw ValidationException::withMessages(['tanggal_bongkar' => 'Tanggal bongkar tidak boleh lebih kecil dari tanggal muat!!']);
            }
            if (Transaksi::whereKeyNot($transaksi->id)->where('nota_bongkar', $data['nota_bongkar'])->exists()) {
                throw ValidationException::withMessages(['nota_bongkar' => 'Terdapat nota yang sama!!']);
            }

            $cash = $transaksi->kas_uang_jalan;
            $jarak = $cash->rute->jarak;
            // Dasar tagihan customer: 1 = tonase muat, 2 = timbangan bongkar.
            $weight = match ((int) $cash->customer->tagihan_dari) {
                1 => $transaksi->tonase,
                2 => $data['timbangan_bongkar'],
                default => throw ValidationException::withMessages(['transaksi' => 'Dasar tagihan customer belum diatur.']),
            };
            $data['nominal_tagihan'] = $weight * $jarak * $transaksi->harga_customer;
            $data['nominal_bayar'] = $weight * $jarak * $transaksi->harga_vendor;
            $penalty = $weight * $jarak * $cash->customer->nominal_penalty;
            $data['nominal_csr'] = $cash->customer->csr == 1 ? $data['timbangan_bongkar'] * $transaksi->harga_csr : 0;
            $data['nominal_bonus'] = $data['timbangan_bongkar'] * ($jarak > 50 ? 1000 : 500);
            $data['profit'] = ($data['nominal_tagihan'] * 0.98) - $data['nominal_bayar'] - $data['nominal_bonus'] - $data['nominal_csr'] - $penalty;

            $transaksi->update($data);
            $vehicle = Vehicle::whereKey($cash->vehicle_id)->lockForUpdate()->firstOrFail();
            $vehicle->update(['do_count' => $vehicle->do_count + 1, 'status' => 'aktif']);

            return $transaksi;
        });
    }
}
