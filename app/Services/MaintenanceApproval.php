<?php

namespace App\Services;

use App\Models\AkiGantiInvoice;
use App\Models\BanGantiInvoice;
use App\Models\FilterOliGantiInvoice;
use App\Models\KasBesar;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Setujui atau tolak pengajuan ganti ban luar, aki, dan filter & oli mesin.
 * Dipakai bersama halaman Otorisasi Maintenance web dan API mobile, supaya
 * histori, Kas Besar, hutang Kas Vendor, dan UJ ditahan diproses sama persis.
 */
class MaintenanceApproval
{
    public const TYPES = ['tire', 'battery', 'filter-oil'];

    public function __construct(
        private KasBesarService $kasBesar,
        private KasVendorService $kasVendor,
        private MaintenanceLogWriter $logs,
    ) {}

    /** @return class-string<BanGantiInvoice|AkiGantiInvoice|FilterOliGantiInvoice> */
    public static function model(string $type): string
    {
        return match ($type) {
            'tire' => BanGantiInvoice::class,
            'battery' => AkiGantiInvoice::class,
            'filter-oil' => FilterOliGantiInvoice::class,
        };
    }

    /**
     * Mencatat histori dan pembayaran dalam satu transaksi. Invoice yang
     * sudah diproses ditolak, sehingga ketukan ulang tidak memotong saldo dua kali.
     *
     * [$fingerprint] (mobile): rincian yang ditinjau pengguna; bila invoice berubah
     * sejak dibuka, persetujuan ditolak agar ditinjau ulang. Web mengirim null.
     *
     * @return array{0: BanGantiInvoice|AkiGantiInvoice|FilterOliGantiInvoice, 1: ?KasBesar}
     */
    public function approve(string $type, int $id, User $user, ?string $fingerprint = null): array
    {
        return DB::transaction(fn (): array => match ($type) {
            'tire' => $this->approveTire($id, $fingerprint),
            'battery' => $this->approveBattery($id, $user, $fingerprint),
            'filter-oil' => $this->approveFilterOil($id, $user, $fingerprint),
        }, $type === 'battery' ? 3 : 1);
    }

    public function reject(string $type, int $id, User $user, ?string $fingerprint = null): Model
    {
        return DB::transaction(function () use ($type, $id, $user, $fingerprint): Model {
            $invoice = self::model($type)::with('details')->whereKey($id)->lockForUpdate()->firstOrFail();
            $this->ensurePending($invoice, $fingerprint);
            $invoice->update(['status' => 'rejected'] + ($type === 'tire' ? [] : ['authorized_by' => $user->id, 'authorized_at' => now()]));

            return $invoice;
        }, $type === 'battery' ? 3 : 1);
    }

    /**
     * Notifikasi WA setelah commit. False bila notifikasi yang tidak boleh
     * diulang gagal: transaksi sudah tersimpan, jangan setujui ulang.
     */
    public function notify(string $type, Model $invoice, ?KasBesar $cash): bool
    {
        $label = ['tire' => 'BAN', 'battery' => 'AKI', 'filter-oil' => 'FILTER & OLI MESIN'][$type];
        try {
            if (in_array($invoice->pembayaran, [BanGantiInvoice::PEMBAYARAN_UJ_DITAHAN_BAN, AkiGantiInvoice::PEMBAYARAN_UJ_DITAHAN], true)) {
                $type === 'tire'
                    ? app(MaintenanceBanUjDitahanService::class)->notify($invoice)
                    : app(MaintenanceUjDitahanService::class)->notify($invoice);
            } elseif ($cash && $type === 'filter-oil') {
                app(MaintenanceKasBesarWhatsAppService::class)->notify($cash, $label, $invoice->vehicle?->vendor);
            }
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
        // Ban & aki: kegagalan WA Kas Besar cukup dilaporkan, seperti sebelumnya di web.
        if ($cash && $type !== 'filter-oil') {
            try {
                app(MaintenanceKasBesarWhatsAppService::class)->notify($cash, $label, $invoice->vehicle?->vendor);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return true;
    }

    private function approveTire(int $id, ?string $fingerprint): array
    {
        $invoice = BanGantiInvoice::with(['vehicle', 'details'])->lockForUpdate()->findOrFail($id);
        $this->ensurePending($invoice, $fingerprint);
        Vehicle::whereKey($invoice->vehicle_id)->lockForUpdate()->firstOrFail();
        if ($invoice->pembayaran === BanGantiInvoice::PEMBAYARAN_UJ_DITAHAN_BAN) {
            app(MaintenanceBanUjDitahanService::class)->debit($invoice);
        }
        foreach ($invoice->details as $item) {
            $log = $this->logs->tire($invoice->vehicle_id, $item->posisi_ban_id, $item->sumber_ban,
                $item->merk, $item->no_seri, $item->kondisi, $item->created_at, (float) ($item->ritase ?? 0));
            $item->update(['ban_log_id' => $log->id]);
        }
        $nominal = (int) $invoice->total_nominal;
        $cash = null;
        if ($invoice->pembayaran === BanGantiInvoice::PEMBAYARAN_KAS_BESAR && $nominal > 0) {
            $cash = $this->payFromKasBesar($invoice, $nominal, 'Ban Luar', 'Ban', 'ban_ganti_invoice_id');
        }
        $invoice->update(['status' => BanGantiInvoice::STATUS_APPROVED]);

        return [$invoice, $cash];
    }

    private function approveBattery(int $id, User $user, ?string $fingerprint): array
    {
        $invoice = AkiGantiInvoice::with(['vehicle', 'details'])->lockForUpdate()->findOrFail($id);
        $this->ensurePending($invoice, $fingerprint);
        if ($invoice->pembayaran === AkiGantiInvoice::PEMBAYARAN_UJ_DITAHAN) {
            app(MaintenanceUjDitahanService::class)->debit($invoice);
        }
        foreach ($invoice->details as $item) {
            $log = $this->logs->battery($invoice->vehicle_id, $item->posisi_aki_id, $item->merk, $item->no_seri, $item->kondisi, $item->created_at);
            $item->update(['aki_log_id' => $log->id]);
        }
        $cash = $invoice->pembayaran === AkiGantiInvoice::PEMBAYARAN_KAS_BESAR
            ? $this->payFromKasBesar($invoice, (int) $invoice->total_nominal, 'Aki', 'Aki', 'aki_ganti_invoice_id')
            : null;
        $invoice->update(['status' => AkiGantiInvoice::STATUS_APPROVED, 'authorized_by' => $user->id, 'authorized_at' => now()]);

        return [$invoice, $cash];
    }

    private function approveFilterOil(int $id, User $user, ?string $fingerprint): array
    {
        $invoice = FilterOliGantiInvoice::with(['vehicle', 'details'])->lockForUpdate()->findOrFail($id);
        $this->ensurePending($invoice, $fingerprint);
        if ($invoice->pembayaran === 'uj_ditahan') {
            app(MaintenanceUjDitahanService::class)->debit($invoice);
        }
        foreach ($invoice->details as $detail) {
            $log = $this->logs->filterOil($invoice->vehicle_id, $detail->kategori_filter_oli_mesin_id,
                $detail->merk, $detail->kondisi, $detail->created_at, (float) $detail->ritase);
            $detail->update(['filter_oli_log_id' => $log->id]);
        }
        $this->logs->refreshFilterOil($invoice->vehicle_id);
        $cash = $invoice->pembayaran === 'kas_besar'
            ? $this->payFromKasBesar($invoice, (int) $invoice->total_nominal, 'Filter & Oli Mesin', 'Filter & Oli Mesin', 'filter_oli_ganti_invoice_id')
            : null;
        $invoice->update(['status' => FilterOliGantiInvoice::STATUS_APPROVED, 'authorized_by' => $user->id, 'authorized_at' => now()]);

        return [$invoice, $cash];
    }

    /** Kas Besar keluar ke rekening tujuan, dan hutang Kas Vendor bila nolam milik vendor. */
    private function payFromKasBesar(Model $invoice, int $nominal, string $unitLabel, string $vendorLabel, string $key): KasBesar
    {
        $cash = $this->kasBesar->potongSaldo([
            'nominal_transaksi' => $nominal,
            'uraian' => 'Penggantian '.$unitLabel.' Unit '.$invoice->vehicle->nomor_lambung,
            'bank' => $invoice->nama_bank, $key => $invoice->id,
            'no_rekening' => $invoice->nomor_rekening, 'transfer_ke' => $invoice->nama_rekening,
        ]);
        if ($invoice->vehicle->vendor_id) {
            $this->kasVendor->tambahHutang([
                'vendor_id' => $invoice->vehicle->vendor_id, 'vehicle_id' => $invoice->vehicle_id,
                'nominal_transaksi' => $nominal, $key => $invoice->id,
                'uraian' => 'Penggantian '.$vendorLabel.' ('.$invoice->no_invoice.')',
            ]);
        }

        return $cash;
    }

    /**
     * Sidik rincian yang menentukan hasil persetujuan: pembayaran, rekening,
     * dan setiap item (posisi/kategori, merk, no seri, kondisi, tanggal, ritase).
     */
    public static function fingerprint(Model $invoice): string
    {
        $items = $invoice->details->sortBy('id')->values()->map(fn ($detail): array => [
            $detail->id, $detail->posisi_ban_id ?? $detail->posisi_aki_id ?? $detail->kategori_filter_oli_mesin_id,
            $detail->sumber_ban, $detail->merk, $detail->no_seri, (int) $detail->kondisi,
            $detail->created_at?->toDateTimeString(), (float) ($detail->ritase ?? 0),
        ]);

        return hash('sha256', json_encode([
            $invoice->id, $invoice->status, $invoice->pembayaran, (int) $invoice->total_nominal,
            $invoice->nama_bank, $invoice->nomor_rekening, $invoice->nama_rekening, $items,
        ]));
    }

    private function ensurePending(Model $invoice, ?string $fingerprint = null): void
    {
        if ($invoice->status !== 'pending') {
            throw ValidationException::withMessages(['invoice' => 'Invoice ini sudah diproses sebelumnya.']);
        }
        if ($fingerprint !== null && ! hash_equals(self::fingerprint($invoice), $fingerprint)) {
            throw ValidationException::withMessages(['fingerprint' => 'Pengajuan berubah sejak dibuka. Muat ulang dan periksa kembali.']);
        }
    }
}
