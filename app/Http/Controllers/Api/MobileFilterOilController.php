<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FilterOliLog;
use App\Models\KategoriFilterOliMesin;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class MobileFilterOilController extends Controller
{
    /** Same eligibility as the web statistics page: active (non-nonaktif) vehicles with the filter/oil restriction enabled. */
    private function eligibleVehicles(Request $request): Builder
    {
        return Vehicle::query()->where('vehicles.vendor_id', $request->user()->vendor_id)
            ->where('vehicles.status', '!=', 'nonaktif')->where('vehicles.pembatasan_filter_oli', true);
    }

    private function meta(Paginator $page): array
    {
        return ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'has_more' => $page->hasMorePages(), 'next_page' => $page->hasMorePages() ? $page->currentPage() + 1 : null];
    }

    public function vehicles(Request $request): JsonResponse
    {
        $filters = $request->validate(['page' => ['sometimes', 'integer', 'min:1'], 'search' => ['nullable', 'string', 'max:100']]);
        $query = $this->eligibleVehicles($request)->select('id', 'nomor_lambung', 'status');
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $query->where('nomor_lambung', 'like', '%'.$search.'%');
        }
        $page = $query->orderBy('nomor_lambung')->orderBy('id')->simplePaginate(25);

        return response()->json(['data' => $page->getCollection()->map(fn ($vehicle) => [
            'id' => $vehicle->id, 'nomor_lambung' => $vehicle->nomor_lambung, 'status' => $vehicle->status,
        ]), 'meta' => $this->meta($page)]);
    }

    public function show(Request $request, int $vehicle): JsonResponse
    {
        $record = $this->eligibleVehicles($request)->leftJoin('upah_gendongs as ug', 'vehicles.id', '=', 'ug.vehicle_id')
            ->where('vehicles.id', $vehicle)
            ->select('vehicles.id', 'vehicles.nomor_lambung', 'ug.nama_driver', 'ug.nama_pengurus')
            ->firstOrFail();
        $logs = FilterOliLog::query()->where('vehicle_id', $record->id)->where('created_at', '<=', now())
            ->latest()->orderByDesc('id')->get()
            ->unique('kategori_filter_oli_mesin_id')->keyBy('kategori_filter_oli_mesin_id');
        $categories = KategoriFilterOliMesin::query()->orderBy('id')->get()->values()->map(function ($category, $index) use ($logs) {
            $log = $logs->get($category->id);

            return [
                'id' => $category->id,
                'nama' => $category->nama,
                'limit_ritase' => (int) $category->limit_ritase,
                'urutan' => $index + 1,
                'current_log' => $log ? [
                    'id' => $log->id,
                    'merk' => $log->merk,
                    'kondisi' => (int) $log->kondisi,
                    'ritase' => (float) $log->ritase,
                    'tanggal_ganti' => $log->created_at?->toISOString(),
                    'mencapai_limit' => (float) $log->ritase >= (int) $category->limit_ritase,
                ] : null,
            ];
        });

        return response()->json(['data' => [
            'vehicle' => ['id' => $record->id, 'nomor_lambung' => $record->nomor_lambung,
                'nama_driver' => $record->nama_driver, 'pengurus' => $record->nama_pengurus],
            'categories' => $categories,
        ]]);
    }

    public function history(Request $request, int $vehicle, int $category): JsonResponse
    {
        $filters = $request->validate(['page' => ['sometimes', 'integer', 'min:1'], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);
        $record = Vehicle::query()->where('vendor_id', $request->user()->vendor_id)->findOrFail($vehicle);
        $kategori = KategoriFilterOliMesin::query()->findOrFail($category);
        $page = FilterOliLog::query()->where('vehicle_id', $record->id)->where('kategori_filter_oli_mesin_id', $kategori->id)
            ->latest()->orderByDesc('id')->simplePaginate($filters['per_page'] ?? 20);

        return response()->json(['data' => $page->getCollection()->map(fn ($log) => [
            'id' => $log->id, 'merk' => $log->merk, 'kondisi' => (int) $log->kondisi,
            'ritase' => (float) $log->ritase, 'tanggal_ganti' => $log->created_at?->toISOString(),
        ]), 'vehicle' => ['id' => $record->id, 'nomor_lambung' => $record->nomor_lambung],
            'category' => ['id' => $kategori->id, 'nama' => $kategori->nama, 'limit_ritase' => (int) $kategori->limit_ritase],
            'meta' => $this->meta($page)]);
    }

    public function trips(Request $request, int $vehicle, int $log): JsonResponse
    {
        $filters = $request->validate(['page' => ['sometimes', 'integer', 'min:1'], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);
        $record = Vehicle::query()->where('vendor_id', $request->user()->vendor_id)->findOrFail($vehicle);
        $filterLog = FilterOliLog::query()->with('kategori')->where('vehicle_id', $record->id)->findOrFail($log);
        $page = DB::table('filter_oli_log_transaksis as audit')
            ->join('transaksis as t', 'audit.transaksi_id', '=', 't.id')
            ->join('kas_uang_jalans as kuj', 't.kas_uang_jalan_id', '=', 'kuj.id')
            ->join('rutes as r', 'kuj.rute_id', '=', 'r.id')
            ->where('audit.filter_oli_log_id', $filterLog->id)
            ->where('kuj.vendor_id', $request->user()->vendor_id)->where('kuj.vehicle_id', $record->id)
            ->orderBy('t.created_at')->orderBy('t.id')
            ->select('audit.id', 't.id as transaksi_id', 't.created_at as tanggal', 'kuj.nomor_uang_jalan', 'r.nama as rute', 'r.jarak', 'audit.nilai_ritase')
            ->simplePaginate($filters['per_page'] ?? 20);

        return response()->json(['data' => $page->items(),
            'filter' => ['id' => $filterLog->id, 'kategori' => $filterLog->kategori?->nama, 'merk' => $filterLog->merk, 'ritase' => (float) $filterLog->ritase],
            'meta' => $this->meta($page)]);
    }
}
