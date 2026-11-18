<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UjDitahan;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;
use App\Models\Vehicle;
use App\Services\DriverHeldCash;
use App\Services\UjDitahanCutoff;
use App\Services\UjDitahanPayout;
use App\Services\UjDitahanPayoutCancellationService;
use App\Services\UjDitahanWhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Admin & SU (mobile): cutoff saldo UJ Ditahan satu periode dan batalkan
 * pencairan manual, sama dengan web (UjDitahanCutoff, UjDitahanBanPayoutService,
 * UjDitahanPayoutCancellationService), lalu WA ke grup & driver.
 */
class MobileHeldCashActionController extends Controller
{
    private const WA_WARNING = 'Transaksi sudah tersimpan, tetapi sebagian notifikasi WA belum berhasil dikirim. Jangan ulangi.';

    public function cutoff(Request $request, int $vehicle, string $fund, UjDitahanCutoff $cutoff): JsonResponse
    {
        $data = $request->validate(['month' => ['required', 'integer', 'between:1,12'], 'year' => ['required', 'integer', 'between:2000,2100'],
            'balance' => ['required', 'integer'], 'request_token' => ['required', 'uuid']]);
        $model = Vehicle::findOrFail($vehicle);
        $account = (new DriverHeldCash(null, (int) $model->id, $fund))->account($data['month'], $data['year']);
        abort_if($account === null, 404);
        if ((int) $account->saldo !== $data['balance']) {
            throw ValidationException::withMessages(['balance' => 'Saldo periode berubah sejak dibuka. Muat ulang dan periksa kembali.']);
        }
        $detail = $cutoff->cutoff($account, $data['request_token']);

        return $this->done($account, $detail, 'Cutoff berhasil. Saldo periode menjadi nol dan dicairkan ke rekening driver.');
    }

    /** Pencairan manual dengan bukti PDF atau foto (foto dijadikan PDF, seperti bukti web). */
    public function withdraw(Request $request, int $vehicle, string $fund, UjDitahanPayout $payout): JsonResponse
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'], 'year' => ['required', 'integer', 'between:2000,2100'], 'balance' => ['required', 'integer'],
            'nominal' => ['required', 'integer', 'min:1', 'max:9999999999999'], 'keterangan' => ['required', 'string', 'max:255'],
            'bank' => ['required', 'string', 'max:50'], 'no_rekening' => ['required', 'string', 'max:50'], 'nama_rekening' => ['required', 'string', 'max:100'],
            'request_token' => ['required', 'uuid'], 'bukti' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
        ]);
        $model = Vehicle::findOrFail($vehicle);
        $account = (new DriverHeldCash(null, (int) $model->id, $fund))->account($data['month'], $data['year']);
        abort_if($account === null, 404);
        if ((int) $account->saldo !== $data['balance']) {
            throw ValidationException::withMessages(['balance' => 'Saldo periode berubah sejak dibuka. Muat ulang dan periksa kembali.']);
        }
        $detail = $payout->withdraw($account, $data, $payout->proofPdf($request->file('bukti')));

        return $this->done($account, $detail, 'Pencairan UJ Ditahan berhasil dicatat.');
    }

    public function cancel(Request $request, string $fund, int $detail, UjDitahanPayoutCancellationService $service): JsonResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:500']])['reason'];
        $cancellation = $service->cancel($fund, $detail, $request->user(), $reason);
        $account = $fund === 'ban' ? UjDitahanBan::findOrFail($cancellation->account_id) : UjDitahan::findOrFail($cancellation->account_id);
        $original = $fund === 'ban' ? UjDitahanBanDetail::findOrFail($detail) : UjDitahanDetail::findOrFail($detail);
        $response = ['message' => 'Pencairan dibatalkan. Saldo dikembalikan ke periode asal dan histori tetap tersimpan.'];
        try {
            app(UjDitahanWhatsAppService::class)->payoutCancellation($account, $original, $cancellation);
        } catch (Throwable $exception) {
            report($exception);
            $response['warning'] = self::WA_WARNING;
        }

        return response()->json($response);
    }

    private function done(UjDitahan|UjDitahanBan $account, UjDitahanDetail|UjDitahanBanDetail $detail, string $message): JsonResponse
    {
        $response = ['message' => $message];
        try {
            app(UjDitahanWhatsAppService::class)->payout($account->refresh(), $detail);
        } catch (Throwable $exception) {
            report($exception);
            $response['warning'] = self::WA_WARNING;
        }

        return response()->json($response);
    }
}
