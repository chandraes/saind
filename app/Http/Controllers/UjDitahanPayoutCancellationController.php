<?php

namespace App\Http\Controllers;

use App\Models\UjDitahan;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;
use App\Services\UjDitahanPayoutCancellationService;
use App\Services\UjDitahanWhatsAppService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UjDitahanPayoutCancellationController extends Controller
{
    public function store(Request $request, string $fund, int $detail, UjDitahanPayoutCancellationService $service): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'su'], true), 403);
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        try {
            $cancellation = $service->cancel($fund, $detail, $request->user(), $data['reason']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->with('error', $exception->validator->errors()->first());
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Pembatalan belum dapat dikonfirmasi. Muat ulang histori sebelum mencoba lagi.');
        }
        $account = $fund === 'ban' ? UjDitahanBan::findOrFail($cancellation->account_id) : UjDitahan::findOrFail($cancellation->account_id);
        $original = $fund === 'ban' ? UjDitahanBanDetail::findOrFail($detail) : UjDitahanDetail::findOrFail($detail);
        $response = back()->with('success', 'Pencairan dibatalkan. Saldo dikembalikan ke periode asal dan histori tetap tersimpan.');
        try {
            app(UjDitahanWhatsAppService::class)->payoutCancellation($account, $original, $cancellation);
        } catch (\Throwable $exception) {
            report($exception);
            $response->with('warning', 'Pembatalan sudah tersimpan, tetapi sebagian notifikasi WA belum berhasil dikirim. Jangan ulangi pembatalan.');
        }

        return $response;
    }
}
