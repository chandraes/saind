<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use App\Services\CustomerBilling;
use App\Support\MaintenanceHistoryGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Admin & SU (mobile): Nota Tagihan → Invoice Tagihan, sama dengan web lewat
 * CustomerBilling: centang nota fisik, ubah nota, keranjang, dan buat invoice.
 */
class MobileCustomerBillingController extends Controller
{
    public function physicalNote(Request $request, Customer $customer, int $transaction, CustomerBilling $billing): JsonResponse
    {
        $checked = $request->validate(['checked' => ['required', 'boolean']])['checked'];
        // Melepas centang nota fisik memakai password konfirmasi, seperti di web.
        if (! $checked && ! MaintenanceHistoryGuard::passwordMatches($request)) {
            throw ValidationException::withMessages(['password' => $request->filled('password') ? 'Password yang anda masukan Salah' : 'Masukkan password konfirmasi.']);
        }
        $billing->setPhysicalNote($transaction, (bool) $checked, $customer);

        return response()->json(['message' => $checked ? 'Nota fisik dicentang.' : 'Centang nota fisik dilepas.']);
    }

    public function update(Request $request, Customer $customer, int $transaction, CustomerBilling $billing): JsonResponse
    {
        $data = $request->validate([
            'nota_muat' => ['required', 'string', 'max:100'], 'nota_bongkar' => ['required', 'string', 'max:100'],
            'tanggal_muat' => ['required', 'date_format:Y-m-d'], 'tanggal_bongkar' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_muat'],
            'tonase' => ['required', 'numeric', 'min:0'], 'timbangan_bongkar' => ['required', 'numeric', 'min:0'],
            'gross_muat' => ['nullable', 'numeric', 'min:0'], 'tarra_muat' => ['nullable', 'numeric', 'min:0'],
            'gross_bongkar' => ['nullable', 'numeric', 'min:0'], 'tarra_bongkar' => ['nullable', 'numeric', 'min:0'],
        ]);
        $billing->updateNote($transaction, $data, $customer);

        return response()->json(['message' => 'Nota tagihan berhasil diubah.']);
    }

    public function cart(Customer $customer, CustomerBilling $billing): JsonResponse
    {
        $cart = $billing->cart($customer)->sortBy([['tanggal', 'asc'], ['kas_uang_jalan_id', 'asc']])->values();
        $additionals = $billing->additionals($customer);
        $weight = (int) $customer->tagihan_dari === 2 ? 'timbangan_bongkar' : 'tonase';

        return response()->json([
            'data' => $cart->map(fn (Transaksi $note, int $index): array => [
                'number' => $index + 1, 'id' => (int) $note->id,
                'vehicle_number' => (string) $note->kas_uang_jalan?->vehicle?->nomor_lambung,
                'money_out_number' => 'UJ'.sprintf('%02d', $note->kas_uang_jalan?->nomor_uang_jalan),
                'route_name' => (string) $note->kas_uang_jalan?->rute?->nama,
                'vendor_name' => (string) ($note->kas_uang_jalan?->vendor?->nickname ?: $note->kas_uang_jalan?->vendor?->nama),
                'weight' => $note->{$weight} === null ? null : (float) $note->{$weight},
                'price' => (int) $note->harga_customer, 'bill' => (int) $note->nominal_tagihan,
            ]),
            'meta' => [
                'customer' => ['id' => (int) $customer->id, 'name' => (string) $customer->nama, 'ppn' => (int) $customer->ppn === 1, 'pph' => (int) $customer->pph === 1],
                'transaction_ids' => $cart->pluck('id')->map(fn ($id): int => (int) $id)->values(),
                'additionals' => collect(CustomerBilling::ADDITIONALS)->filter(fn (string $kind): bool => $additionals->has($kind))
                    ->map(fn (string $kind): array => ['kind' => $kind, 'label' => TransaksiAdditional::JENIS[$kind] ?? $kind, 'amount' => (int) round((float) $additionals->get($kind)->nominal)])->values(),
                'total_bill' => (int) round((float) $cart->sum('nominal_tagihan')),
            ],
        ]);
    }

    public function add(Request $request, Customer $customer, CustomerBilling $billing): JsonResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array', 'min:1', 'max:500'], 'ids.*' => ['integer', 'distinct']])['ids'];
        $count = $billing->addToCart($customer, $ids);

        return response()->json(['message' => $count.' nota dimasukkan ke keranjang tagihan.']);
    }

    public function remove(Customer $customer, int $transaction, CustomerBilling $billing): JsonResponse
    {
        $billing->removeFromCart($customer, $transaction);

        return response()->json(['message' => 'Nota dikeluarkan dari keranjang.']);
    }

    public function checkout(Request $request, Customer $customer, CustomerBilling $billing): JsonResponse
    {
        $data = $request->validate([
            'penyesuaian' => ['required', 'integer', 'min:0'], 'penalty' => ['required', 'integer', 'min:0'], 'penalty_akhir' => ['required', 'integer', 'min:0'],
            'tanggal_hardcopy' => ['required', 'date_format:Y-m-d'], 'estimasi_pembayaran' => ['required', 'date_format:Y-m-d'],
            'no_resi' => ['required', 'string', 'max:100'], 'no_validasi' => ['required', 'string', 'max:100'], 'ppn_dipungut' => ['required', 'boolean'],
            'transaction_ids' => ['required', 'array', 'min:1'], 'transaction_ids.*' => ['integer', 'distinct'],
        ]);
        $ids = $data['transaction_ids'];
        unset($data['transaction_ids']);
        $data['ppn_dipungut'] = (int) $data['ppn_dipungut'];
        $invoice = $billing->checkout($customer, $data, $ids);

        return response()->json(['message' => 'Invoice tagihan '.$invoice->periode.' berhasil dibuat.', 'invoice_id' => (int) $invoice->id]);
    }
}
