<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKasBesarDepositRequest;
use App\Http\Requests\StoreKasBesarWithdrawalRequest;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\PesanWa;
use App\Models\Rekening;
use App\Services\KasBesarDepositService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class FormKasBesarController extends Controller
{
    public function masuk()
    {
        $nomor = (int) KasBesar::max('nomor_kode_deposit') + 1;

        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        return view('billing.kas-besar.masuk', [
            'nomor' => $nomor,
            'rekening' => $rekening,
        ]);
    }

    public function masuk_store(StoreKasBesarDepositRequest $request, KasBesarDepositService $service): RedirectResponse
    {
        $result = $service->store((int) $request->validated('nominal_transaksi'), $request->validated('request_token'));

        return $this->depositResponse($result, false);
    }

    /** @param array{cash: KasBesar, message: PesanWa} $result */
    private function depositResponse(array $result, bool $withdrawal): RedirectResponse
    {
        $label = $withdrawal ? 'Pengembalian deposit' : 'Deposit';
        try {
            $sent = (new GroupWa)->sendRecordedMessage($result['message']);
        } catch (\Throwable $exception) {
            $sent = false;
            Log::warning('Pengiriman WA deposit mengalami error.', ['pesan_wa_id' => $result['message']->id, 'exception_type' => $exception::class]);
        }
        $response = redirect()->route('billing.index')->with('success', $label.' berhasil disimpan.');
        if (! $sent) {
            Log::warning('WA deposit belum terkirim.', ['pesan_wa_id' => $result['message']->id, 'kas_besar_id' => $result['cash']->id]);
            $response->with('warning', $label.' sudah tersimpan, tetapi notifikasi WA belum berhasil dikirim. Silakan kirim ulang melalui Histori WA; jangan input ulang transaksi.');
        }

        return $response;
    }

    public function keluar()
    {

        $rekening = Rekening::where('untuk', 'withdraw')->first();

        return view('billing.kas-besar.keluar', [
            'rekening' => $rekening,
            'saldo' => (new KasBesar)->saldoTerakhir(),
        ]);
    }

    public function keluar_store(StoreKasBesarWithdrawalRequest $request, KasBesarDepositService $service): RedirectResponse
    {
        $result = $service->withdraw((int) $request->validated('nominal_transaksi'), $request->validated('request_token'));

        return $this->depositResponse($result, true);
    }
}
