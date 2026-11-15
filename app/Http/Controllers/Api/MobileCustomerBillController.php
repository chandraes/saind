<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Nota Tagihan per customer untuk General Manager (baca saja), dari data yang
 * sama dengan halaman admin (Transaksi::getTagihanData). Ton mengikuti setting
 * "tagihan dari" customer; tanpa profit. Kompensasi JR / Penyesuaian BBM /
 * Achievement (sesuai setting customer) belum punya nominal per baris — DPP
 * diisi admin saat lanjut — sehingga hanya tonase muat/bongkar dan DO fisik.
 */
class MobileCustomerBillController extends Controller
{
    private const PER_PAGE = 25;

    public function customers(): JsonResponse
    {
        return response()->json(['data' => Customer::query()->orderBy('nama')->get(['id', 'nama', 'singkatan'])
            ->map(fn (Customer $customer): array => ['id' => (int) $customer->id, 'name' => (string) $customer->nama, 'short' => $customer->singkatan])->values()]);
    }

    public function index(Request $request, Customer $customer): JsonResponse
    {
        $categories = $this->categories($customer);
        $filters = $request->validate(['kind' => ['sometimes', Rule::in(array_keys($categories))], 'search' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);
        $kind = $filters['kind'] ?? 'utama';
        $weightColumn = (int) $customer->tagihan_dari === 2 ? 'timbangan_bongkar' : 'tonase';
        $notes = $kind === 'utama' ? Transaksi::getTagihanData($customer->id) : $this->additionalNotes($customer, $kind);
        $rows = $notes->sortBy([['tanggal', 'asc'], ['kas_uang_jalan_id', 'asc']])->values()
            ->map(fn (Transaksi $note): array => $kind !== 'utama' ? [
                'id' => (int) $note->id,
                'vehicle_number' => (string) $note->kas_uang_jalan?->vehicle?->nomor_lambung,
                'money_out_number' => 'UJ'.sprintf('%02d', $note->kas_uang_jalan?->nomor_uang_jalan),
                'route_name' => (string) $note->kas_uang_jalan?->rute?->nama,
                'vendor_name' => (string) ($note->kas_uang_jalan?->vendor?->nickname ?: $note->kas_uang_jalan?->vendor?->nama),
                'load_weight' => $note->tonase === null ? null : (float) $note->tonase,
                'unload_weight' => $note->timbangan_bongkar === null ? null : (float) $note->timbangan_bongkar,
                'physical_note' => (int) $note->nota_fisik === 1,
            ] : [
                'id' => (int) $note->id,
                'vehicle_number' => (string) $note->kas_uang_jalan?->vehicle?->nomor_lambung,
                'money_out_number' => 'UJ'.sprintf('%02d', $note->kas_uang_jalan?->nomor_uang_jalan),
                'route_name' => (string) $note->kas_uang_jalan?->rute?->nama,
                'vendor_name' => (string) ($note->kas_uang_jalan?->vendor?->nickname ?: $note->kas_uang_jalan?->vendor?->nama),
                'weight' => $note->{$weightColumn} === null ? null : (float) $note->{$weightColumn},
                'price' => (int) $note->harga_customer,
                'bill' => (int) $note->nominal_tagihan,
            ]);
        $search = mb_strtolower(trim($filters['search'] ?? ''));
        if ($search !== '') {
            $rows = $rows->filter(fn (array $row): bool => collect([$row['vehicle_number'], $row['money_out_number'], $row['route_name'], $row['vendor_name']])
                ->contains(fn (string $value): bool => str_contains(mb_strtolower($value), $search)))->values();
        }
        $page = (int) ($filters['page'] ?? 1);
        $offset = ($page - 1) * self::PER_PAGE;
        $meta = ['current_page' => $page, 'last_page' => max(1, (int) ceil($rows->count() / self::PER_PAGE)), 'total' => $rows->count(),
            'customer' => ['id' => (int) $customer->id, 'name' => (string) $customer->nama, 'short' => $customer->singkatan],
            'kind' => $kind, 'categories' => $categories];
        $data = $rows->slice($offset, self::PER_PAGE)->values()->map(fn (array $row, int $index): array => ['number' => $offset + $index + 1] + $row);
        if ($kind !== 'utama') {
            return response()->json(['data' => $data, 'meta' => $meta + [
                'total_load_weight' => round((float) $rows->sum('load_weight'), 2), 'total_unload_weight' => round((float) $rows->sum('unload_weight'), 2),
            ]]);
        }
        $total = (int) $rows->sum('bill');
        $ppn = (int) $customer->ppn === 1 ? (int) round($total * 0.11) : 0;
        $pph = (int) $customer->pph === 1 ? (int) round($total * 0.02) : 0;

        return response()->json([
            'data' => $data,
            'meta' => $meta + [
                'weight_basis' => $weightColumn === 'tonase' ? 'muat' : 'bongkar',
                'total_weight' => round((float) $rows->sum('weight'), 2),
                'total_bill' => $total, 'ppn' => $ppn, 'pph' => $pph, 'final_bill' => $total + $ppn - $pph,
            ],
        ]);
    }

    /** HPP selalu ada; kategori lain sesuai setting customer, seperti menu admin. */
    private function categories(Customer $customer): array
    {
        return ['utama' => 'HPP'] + array_filter(TransaksiAdditional::JENIS, fn (string $label, string $kind): bool => (bool) $customer->{'is_'.$kind}, ARRAY_FILTER_USE_BOTH);
    }

    /** Nota kategori tambahan yang menunggu ditagih (status 0), seperti halaman admin. */
    private function additionalNotes(Customer $customer, string $kind): Collection
    {
        $ids = TransaksiAdditional::where('customer_id', $customer->id)->where('jenis', $kind)->where('status', 0)->pluck('transaksi_id')->all();

        return (new Transaksi)->getTransaksiAdditionals($customer->id, $ids);
    }
}
