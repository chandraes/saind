<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceAddVendor;
use App\Models\KasVendor;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Kas Vendor versi mobile: mutasi kas vendor akun per bulan, hanya untuk akun vendor. */
class MobileVendorCashController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->canViewVendorFinance(), 403);
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12', 'required_with:year'],
            'year' => ['nullable', 'integer', 'between:2000,2100', 'required_with:month'],
        ]);
        $vendorId = (int) $request->user()->vendor_id;
        $start = CarbonImmutable::create((int) ($filters['year'] ?? now()->year), (int) ($filters['month'] ?? now()->month), 1);
        $rows = KasVendor::query()->with('vehicle:id,nomor_lambung')->where('vendor_id', $vendorId)
            ->whereBetween('tanggal', [$start->toDateString(), $start->endOfMonth()->toDateString()])
            ->orderBy('tanggal')->orderBy('id')->get();
        // Saldo awal = sisa mutasi terakhir sebelum bulan ini (bulan apa pun sebelumnya).
        $opening = (int) (KasVendor::lastBefore($vendorId, $start->year, $start->month)?->sisa ?? 0);
        $firstDate = KasVendor::query()->where('vendor_id', $vendorId)->min('tanggal');
        $firstYear = $firstDate ? min(CarbonImmutable::parse($firstDate)->year, $start->year) : $start->year;
        // Baris pelunasan tertaut ke invoice, sama seperti tautan di halaman web.
        $additionalKinds = InvoiceAddVendor::query()->where('vendor_id', $vendorId)
            ->whereIn('id', $rows->pluck('invoice_add_vendor_id')->filter())->pluck('jenis', 'id');
        $loan = (int) $rows->sum('pinjaman');
        $payment = (int) $rows->sum('bayar');

        return response()->json([
            'data' => $rows->map(fn (KasVendor $row): array => [
                'id' => $row->id, 'date' => CarbonImmutable::parse($row->tanggal)->toDateString(), 'description' => $row->uraian,
                'vehicle_number' => $row->vehicle?->nomor_lambung, 'loan' => (int) $row->pinjaman, 'payment' => (int) $row->bayar,
                'balance' => (int) $row->sisa,
                'invoice' => match (true) {
                    $row->invoice_bayar_id !== null => ['kind' => 'utama', 'id' => (int) $row->invoice_bayar_id],
                    isset($additionalKinds[$row->invoice_add_vendor_id]) => ['kind' => $additionalKinds[$row->invoice_add_vendor_id], 'id' => (int) $row->invoice_add_vendor_id],
                    default => null,
                },
            ])->values(),
            'meta' => [
                'month' => $start->month, 'year' => $start->year, 'years' => range(max($start->year, (int) now()->year), $firstYear),
                'opening_balance' => $opening, 'total_loan' => $loan, 'total_payment' => $payment,
                'closing_balance' => $rows->isEmpty() ? $opening : (int) $rows->last()->sisa,
            ],
        ]);
    }
}
