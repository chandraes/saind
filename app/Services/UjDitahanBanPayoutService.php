<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class UjDitahanBanPayoutService
{
    /** @param array{nominal: int|string, keterangan: string, bank: string, no_rekening: string, nama_rekening: string, request_token: string} $data */
    public function withdraw(UjDitahanBan $account, array $data, UploadedFile $proof): UjDitahanBanDetail
    {
        $path = null;
        try {
            return DB::transaction(function () use ($account, $data, $proof, &$path): UjDitahanBanDetail {
                Vehicle::whereKey($account->vehicle_id)->lockForUpdate()->firstOrFail();
                $locked = UjDitahanBan::whereKey($account->id)->lockForUpdate()->firstOrFail();
                $this->assertUnusedToken($data['request_token']);
                $older = UjDitahanBan::where('vehicle_id', $locked->vehicle_id)->where('saldo', '>', 0)
                    ->where(function ($query) use ($locked): void {
                        $query->where('tahun', '<', $locked->tahun)->orWhere(function ($query) use ($locked): void {
                            $query->where('tahun', $locked->tahun)->where('bulan', '<', $locked->bulan);
                        });
                    })->exists();
                if ($older) {
                    throw ValidationException::withMessages(['nominal' => 'Habiskan saldo UJ Ditahan Ban bulan sebelumnya terlebih dahulu.']);
                }
                $nominal = (int) $data['nominal'];
                if ($nominal <= 0 || $nominal > $locked->saldo) {
                    throw ValidationException::withMessages(['nominal' => 'Nominal pencairan harus positif dan tidak melebihi saldo UJ Ditahan Ban.']);
                }
                $path = $proof->store('uj-ditahan-ban-bukti', 'local');
                if (! $path) {
                    throw new RuntimeException('Bukti pencairan gagal disimpan.');
                }
                $detail = $locked->details()->create([
                    'jenis' => 'keluar', 'nominal' => $nominal, 'driver_id' => $locked->vehicle->driver_id,
                    'keterangan' => $data['keterangan'], 'bank' => $data['bank'],
                    'no_rekening' => $data['no_rekening'], 'nama_rekening' => $data['nama_rekening'],
                    'request_token' => $data['request_token'], 'file_pdf' => $path,
                ]);
                $locked->decrement('saldo', $nominal);
                $locked->increment('total_keluar', $nominal);

                return $detail;
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function cutoff(UjDitahanBan $account, string $token): UjDitahanBanDetail
    {
        return DB::transaction(function () use ($account, $token): UjDitahanBanDetail {
            $vehicle = Vehicle::whereKey($account->vehicle_id)->lockForUpdate()->firstOrFail();
            $locked = UjDitahanBan::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $this->assertUnusedToken($token);
            if ($locked->saldo <= 0) {
                throw ValidationException::withMessages(['saldo' => 'Cutoff hanya dapat dilakukan jika saldo UJ Ditahan Ban lebih dari nol.']);
            }
            $driver = $vehicle->driver_id ? Driver::whereKey($vehicle->driver_id)->lockForUpdate()->first() : null;
            foreach (['bank', 'no_rek', 'nama_rek'] as $field) {
                $value = trim((string) $driver?->{$field});
                if ($value === '' || in_array($value, ['-', '0'], true)) {
                    throw ValidationException::withMessages(['rekening' => 'Lengkapi driver, bank, nomor rekening, dan nama pemilik rekening driver sebelum cutoff UJ Ditahan Ban.']);
                }
            }
            $nominal = $locked->saldo;
            $detail = $locked->details()->create([
                'driver_id' => $driver->id, 'jenis' => 'keluar', 'nominal' => $nominal,
                'keterangan' => 'Cutoff', 'bank' => $driver->bank, 'no_rekening' => $driver->no_rek,
                'nama_rekening' => $driver->nama_rek, 'request_token' => $token,
            ]);
            $locked->update(['saldo' => 0, 'total_keluar' => $locked->total_keluar + $nominal]);

            return $detail;
        });
    }

    private function assertUnusedToken(string $token): void
    {
        if (UjDitahanBanDetail::where('request_token', $token)->exists()) {
            throw ValidationException::withMessages(['request_token' => 'Permintaan ini sudah diproses. Muat ulang halaman sebelum melanjutkan.']);
        }
    }
}
