<?php

namespace App\Services;

use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\Legalitas\LegalitasDokumen;
use App\Models\PesanWa;
use App\Models\Rekening;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KasBesarDepositService
{
    /** @return array{cash: KasBesar, message: PesanWa} */
    public function store(int $amount, string $token): array
    {
        return $this->persist($amount, $token, false);
    }

    /** @return array{cash: KasBesar, message: PesanWa} */
    public function withdraw(int $amount, string $token): array
    {
        return $this->persist($amount, $token, true);
    }

    /** @return array{cash: KasBesar, message: PesanWa} */
    private function persist(int $amount, string $token, bool $withdrawal): array
    {
        if ($amount < 1) {
            throw ValidationException::withMessages(['nominal_transaksi' => 'Nominal harus lebih dari nol.']);
        }

        return DB::transaction(function () use ($amount, $token, $withdrawal): array {
            Rekening::orderBy('id')->lockForUpdate()->first();
            $account = Rekening::where('untuk', $withdrawal ? 'withdraw' : 'kas-besar')->orderBy('id')->first();
            if (! $account || blank($account->nama_rekening) || blank($account->nomor_rekening) || blank($account->nama_bank)) {
                throw ValidationException::withMessages(['nominal_transaksi' => ($withdrawal ? 'Rekening pengembalian deposit' : 'Rekening Kas Besar').' belum diatur dengan lengkap. Silakan hubungi admin.']);
            }
            if (KasBesar::where('deposit_request_token', $token)->exists()) {
                throw ValidationException::withMessages(['request_token' => 'Transaksi ini sudah diproses. Periksa rekap sebelum membuat transaksi baru.']);
            }
            $group = GroupWa::where('untuk', 'kas-besar')->first();
            if (! $group || blank($group->nama_group)) {
                throw ValidationException::withMessages(['nominal_transaksi' => 'Grup WA Kas Besar belum diatur. Silakan hubungi admin.']);
            }
            $last = KasBesar::orderByDesc('id')->lockForUpdate()->first();
            $balance = (int) ($last?->saldo ?? 0);
            if ($withdrawal && $amount > $balance) {
                throw ValidationException::withMessages(['nominal_transaksi' => 'Saldo Kas Besar tidak cukup. Saldo tersedia: Rp '.number_format($balance, 0, ',', '.').'.']);
            }
            $number = $withdrawal ? null : (int) KasBesar::max('nomor_kode_deposit') + 1;
            $change = $withdrawal ? -$amount : $amount;
            $cash = KasBesar::create([
                'deposit_request_token' => $token,
                'nomor_kode_deposit' => $number,
                'uraian' => $withdrawal ? 'Withdraw' : 'Deposit',
                'jenis_transaksi_id' => $withdrawal ? 2 : 1,
                'tanggal' => now()->toDateString(),
                'nominal_transaksi' => $amount,
                'saldo' => $balance + $change,
                'modal_investor' => -$change,
                'modal_investor_terakhir' => (int) ($last?->modal_investor_terakhir ?? 0) - $change,
                'transfer_ke' => substr($account->nama_rekening, 0, 15),
                'bank' => $account->nama_bank,
                'no_rekening' => $account->nomor_rekening,
            ]);
            $message = PesanWa::create(['tujuan' => $group->nama_group, 'pesan' => $this->message($cash), 'status' => 0]);

            return ['cash' => $cash, 'message' => $message];
        }, 3);
    }

    private function message(KasBesar $cash): string
    {
        $withdrawal = (int) $cash->jenis_transaksi_id === 2;
        $border = $withdrawal ? '🔴🔴🔴🔴🔴🔴🔴🔴🔴' : '🔵🔵🔵🔵🔵🔵🔵🔵🔵';
        $title = $withdrawal ? 'Form Pengembalian Deposit' : 'Form Permintaan Deposit';
        $code = $withdrawal ? '' : '*D'.sprintf('%02d', $cash->nomor_kode_deposit)."*\n\n";
        $message = $border."\n*".$title."*\n".$border."\n\n".$code.
            'Nilai :  *Rp. '.number_format($cash->nominal_transaksi, 0, ',', '.')."*\n\n".
            "Ditransfer ke rek:\n\nBank     : ".$cash->bank."\nNama    : ".$cash->transfer_ke."\nNo. Rek : ".$cash->no_rekening."\n\n".
            "==========================\nSisa Saldo Kas Besar : \nRp. ".number_format($cash->saldo, 0, ',', '.')."\n\n".
            "Total Modal Investor : \nRp. ".number_format($cash->modal_investor_terakhir, 0, ',', '.')."\n\nTerima kasih 🙏🙏🙏\n";
        $documents = LegalitasDokumen::whereNotNull('tanggal_expired')->where('tanggal_expired', '<', now()->addDays(45))->get();
        if ($documents->isNotEmpty()) {
            $message .= "\n==========================\nWARNING : \n";
            foreach ($documents as $index => $document) {
                $message .= ($index + 1).'. '.$document->nama.' - '.date('d-m-Y', strtotime($document->tanggal_expired))."\n";
            }
        }

        return $message;
    }
}
