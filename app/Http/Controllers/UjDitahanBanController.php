<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithdrawUjDitahanBanRequest;
use App\Models\GroupWa;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Services\UjDitahanBanPayoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class UjDitahanBanController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        return $this->accounts($request, false);
    }

    public function recap(Request $request): View|JsonResponse
    {
        return $this->accounts($request, true);
    }

    private function accounts(Request $request, bool $recap): View|JsonResponse
    {
        $request->validate(['bulan' => ['nullable', 'integer', 'between:1,12'], 'tahun' => ['nullable', 'integer', 'between:2000,2100']]);
        $bulan = $request->integer('bulan') ?: now()->month;
        $tahun = $request->integer('tahun') ?: now()->year;
        $query = UjDitahanBan::query()->where('bulan', $bulan)->where('tahun', $tahun)->where('saldo', $recap ? '=' : '!=', 0);
        if (! $request->ajax()) {
            $activeBalanceMonths = UjDitahanBan::where('tahun', $tahun)->where('saldo', '!=', 0)
                ->select('bulan')->distinct()->orderBy('bulan')->pluck('bulan')->map(fn ($month): int => (int) $month)->all();

            return view('billing.uj-ditahan-ban.index', compact('bulan', 'tahun', 'recap', 'activeBalanceMonths'));
        }
        $total = (clone $query)->count();
        $search = mb_substr((string) $request->input('search.value', ''), 0, 100);
        if ($search !== '') {
            $query->whereHas('vehicle', fn ($vehicle) => $vehicle->where('nomor_lambung', 'like', '%'.$search.'%'));
        }
        $filtered = (clone $query)->count();
        $summary = (clone $query)->selectRaw('COALESCE(SUM(total_masuk),0) AS masuk, COALESCE(SUM(total_keluar),0) AS keluar, COALESCE(SUM(saldo),0) AS saldo')->first();
        $start = max(0, $request->integer('start'));
        $rows = $query->with('vehicle:id,nomor_lambung,driver_id', 'vehicle.driver')->orderBy('id')->offset($start)->limit($this->pageLength($request))->get();

        return response()->json([
            'draw' => $request->integer('draw'), 'recordsTotal' => $total, 'recordsFiltered' => $filtered,
            'totals' => ['masuk' => (int) $summary->masuk, 'keluar' => (int) $summary->keluar, 'saldo' => (int) $summary->saldo],
            'data' => $rows->values()->map(fn (UjDitahanBan $row, int $index): array => [
                'id' => $row->id, 'no' => $start + $index + 1, 'kendaraan' => $row->vehicle?->nomor_lambung ?? '-',
                'total_masuk' => $row->total_masuk, 'total_keluar' => $row->total_keluar, 'saldo' => $row->saldo,
                'detail_url' => route('billing.uj-ditahan-ban.show', $row),
                'cutoff' => ! $recap && $row->saldo > 0 && in_array($request->user()?->role, ['admin', 'su'], true) ? [
                    'url' => route('billing.uj-ditahan-ban.cutoff', $row),
                    'token' => (string) Str::uuid(),
                    'periode' => sprintf('%02d/%d', $row->bulan, $row->tahun),
                    'driver' => $row->vehicle?->driver?->nama ?? 'Belum diisi',
                    'bank' => $row->vehicle?->driver?->bank ?: 'Belum diisi',
                    'rekening' => $row->vehicle?->driver?->no_rek ?: 'Belum diisi',
                    'nama' => $row->vehicle?->driver?->nama_rek ?: 'Belum diisi',
                ] : null,
            ]),
        ]);
    }

    public function show(Request $request, UjDitahanBan $account): View|JsonResponse
    {
        $account->load('vehicle.driver');
        if (! $request->ajax()) {
            return view('billing.uj-ditahan-ban.show', compact('account'));
        }
        $query = $account->details();
        $total = (clone $query)->count();
        $search = mb_substr((string) $request->input('search.value', ''), 0, 100);
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('keterangan', 'like', '%'.$search.'%')->orWhere('bank', 'like', '%'.$search.'%')
                    ->orWhere('no_rekening', 'like', '%'.$search.'%')->orWhere('nama_rekening', 'like', '%'.$search.'%');
            });
        }
        $filtered = (clone $query)->count();
        $summary = (clone $query)->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END),0) AS masuk, COALESCE(SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END),0) AS keluar")->first();
        $start = max(0, $request->integer('start'));
        $rows = $query->with('driver:id,nama')->orderByDesc('created_at')->orderByDesc('id')->offset($start)->limit($this->pageLength($request))->get();

        return response()->json([
            'draw' => $request->integer('draw'), 'recordsTotal' => $total, 'recordsFiltered' => $filtered,
            'totals' => ['masuk' => (int) $summary->masuk, 'keluar' => (int) $summary->keluar],
            'data' => $rows->values()->map(fn (UjDitahanBanDetail $row, int $index): array => [
                'no' => $start + $index + 1, 'tanggal' => $row->created_at->format('d-m-Y'),
                'keterangan' => $row->keterangan, 'driver' => $row->driver?->nama ?? '-',
                'nominal_masuk' => $row->jenis === 'masuk' ? $row->nominal : 0,
                'nominal_keluar' => $row->jenis === 'keluar' ? $row->nominal : 0,
                'rekening' => implode(' / ', array_filter([$row->bank, $row->no_rekening, $row->nama_rekening])),
                'proof_url' => $row->file_pdf ? route('billing.uj-ditahan-ban.bukti', $row) : null,
            ]),
        ]);
    }

    public function withdraw(WithdrawUjDitahanBanRequest $request, UjDitahanBan $account, UjDitahanBanPayoutService $payout): RedirectResponse
    {
        try {
            $detail = $payout->withdraw($account, $request->validated(), $request->file('bukti_pdf'));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Pencairan gagal disimpan. Saldo belum dipotong. Silakan coba kembali.');
        }

        return $this->completed($account, $detail, 'Pencairan UJ Ditahan Ban berhasil dicatat.');
    }

    public function cutoff(Request $request, UjDitahanBan $account, UjDitahanBanPayoutService $payout): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'su'], true), 403);
        $data = $request->validate(['request_token' => ['required', 'uuid']]);
        try {
            $detail = $payout->cutoff($account, $data['request_token']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Cutoff gagal disimpan. Saldo belum berubah. Silakan coba kembali.');
        }

        return $this->completed($account, $detail, 'Cutoff UJ Ditahan Ban berhasil dicatat. Saldo periode menjadi nol.');
    }

    private function completed(UjDitahanBan $account, UjDitahanBanDetail $detail, string $message): RedirectResponse
    {
        $response = redirect()->route('billing.uj-ditahan-ban.show', $account)->with('success', $message);
        try {
            $account->refresh()->load('vehicle.driver');
            $messenger = app(GroupWa::class);
            $group = GroupWa::where('untuk', 'kas-uj-ditahan')->firstOrFail();
            $text = "*Pencairan UJ Ditahan Ban*\nNomor Lambung: ".$account->vehicle?->nomor_lambung.
                "\nPeriode: ".$account->bulan.'/'.$account->tahun."\nUraian: ".$detail->keterangan.
                "\nNominal: Rp ".number_format($detail->nominal, 0, ',', '.').
                "\nBank: ".$detail->bank."\nNo. Rekening: ".$detail->no_rekening."\nAtas Nama: ".$detail->nama_rekening.
                "\nSisa saldo periode: Rp ".number_format($account->saldo, 0, ',', '.');
            $messenger->sendWa($group->nama_group, $text);
            $phone = preg_replace('/\D/', '', $account->vehicle?->driver?->no_hp ?? '');
            if (strlen($phone) >= 10) {
                $messenger->sendWa($phone, $text);
            }
        } catch (Throwable $exception) {
            report($exception);
            $response->with('warning', 'Transaksi sudah tersimpan, tetapi notifikasi WA belum berhasil dikirim. Jangan ulangi pencairan.');
        }

        return $response;
    }

    public function proof(UjDitahanBanDetail $detail): StreamedResponse
    {
        abort_unless($detail->file_pdf && str_starts_with($detail->file_pdf, 'uj-ditahan-ban-bukti/') && Storage::disk('local')->exists($detail->file_pdf), 404);

        return Storage::disk('local')->response($detail->file_pdf, 'bukti-uj-ditahan-ban-'.$detail->id.'.pdf', ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function pageLength(Request $request): int
    {
        return min(100, max(1, $request->integer('length', 25)));
    }
}
