<?php

namespace App\Services;

use App\Models\UjDitahan;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * Pencairan manual UJ Ditahan dengan bukti PDF. Sparepart di sini; Ban lewat
 * UjDitahanBanPayoutService. Dipakai halaman web UJ Ditahan dan API mobile
 * (Admin & SU). Foto bukti dari aplikasi diubah menjadi PDF lebih dulu
 * ([proofPdf]) sehingga web tetap menampilkan bukti PDF seperti biasa.
 */
class UjDitahanPayout
{
    public function __construct(private UjDitahanBanPayoutService $ban) {}

    /**
     * @param  array{nominal: int|string, keterangan: string, bank: string, no_rekening: string, nama_rekening: string, request_token?: string}  $data
     */
    public function withdraw(UjDitahan|UjDitahanBan $account, array $data, UploadedFile $proof): UjDitahanDetail|UjDitahanBanDetail
    {
        if ($account instanceof UjDitahanBan) {
            return $this->ban->withdraw($account, $data, $proof);
        }
        $path = null;
        try {
            return DB::transaction(function () use ($account, $data, $proof, &$path): UjDitahanDetail {
                // Kunci baris periode agar tidak ada pencairan ganda bersamaan (seperti web).
                $master = UjDitahan::whereKey($account->id)->lockForUpdate()->firstOrFail();
                $older = UjDitahan::where('vehicle_id', $master->vehicle_id)->where('saldo', '>', 0)
                    ->where(function ($query) use ($master): void {
                        $query->where('tahun', '<', $master->tahun)->orWhere(function ($query) use ($master): void {
                            $query->where('tahun', $master->tahun)->where('bulan', '<', $master->bulan);
                        });
                    })->sum('saldo');
                if ($older > 0) {
                    throw ValidationException::withMessages(['nominal' => 'Silahkan gunakan Saldo UJ Ditahan pada bulan sebelumnya terlebih dahulu.']);
                }
                $nominal = (int) $data['nominal'];
                if ($nominal <= 0 || $nominal > $master->saldo) {
                    throw ValidationException::withMessages(['nominal' => 'Nominal pencairan melebihi sisa saldo bulan ini!']);
                }
                // Lokasi sama dengan web: storage/app/public/uj_ditahan_pdf.
                $path = $proof->storeAs('public/uj_ditahan_pdf', time().'_'.$proof->getClientOriginalName());
                if (! $path) {
                    throw new RuntimeException('Bukti pencairan gagal disimpan.');
                }
                $detail = UjDitahanDetail::create([
                    'uj_ditahan_id' => $master->id, 'jenis' => 'keluar', 'payout_origin' => 'manual',
                    'driver_id' => $master->vehicle?->driver_id, 'nominal' => $nominal, 'keterangan' => $data['keterangan'],
                    'bank' => $data['bank'], 'no_rekening' => $data['no_rekening'], 'nama_rekening' => $data['nama_rekening'], 'file_pdf' => $path,
                ]);
                $master->decrement('saldo', $nominal);
                $master->increment('total_keluar', $nominal);

                return $detail;
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::delete($path);
            }
            throw $exception;
        }
    }

    /** PDF apa adanya; foto (JPG/PNG/WEBP) dijadikan PDF satu halaman A4. */
    public function proofPdf(UploadedFile $file): UploadedFile
    {
        if (strtolower((string) $file->getClientOriginalExtension()) === 'pdf' || $file->getMimeType() === 'application/pdf') {
            return $file;
        }
        $image = 'data:'.$file->getMimeType().';base64,'.base64_encode((string) file_get_contents($file->getRealPath()));
        $html = '<html><body style="margin:0"><img src="'.$image.'" style="max-width:100%;max-height:100%"></body></html>';
        $target = tempnam(sys_get_temp_dir(), 'bukti').'.pdf';
        Pdf::loadHTML($html)->setPaper('a4')->save($target);

        return new UploadedFile($target, 'bukti-pencairan.pdf', 'application/pdf', null, true);
    }
}
