<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MobileUnloadingNoteResource;
use App\Models\LoadNoteDraft;
use App\Models\Transaksi;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;
use App\Services\MobileNotePayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnloadingNoteController extends Controller
{
    public function index(Request $request, MobileNotePayment $payments): AnonymousResourceCollection
    {
        $this->authorize('viewAny', LoadNoteDraft::class);
        $filters = $request->validate(['status' => ['sometimes', 'string', 'in:waiting,completed,all'],
            'search' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);
        $query = $this->journeys($request);
        if (($filters['status'] ?? 'waiting') !== 'all') {
            $query->where('status', ($filters['status'] ?? 'waiting') === 'waiting' ? 2 : 3);
        }
        $query->select('transaksis.*')->addSelect([
            'mobile_sparepart' => UjDitahanDetail::selectRaw('COALESCE(SUM(nominal), 0)')->whereColumn('transaksi_id', 'transaksis.id')->where('jenis', 'masuk'),
            'mobile_tyres' => UjDitahanBanDetail::selectRaw('COALESCE(SUM(nominal), 0)')->whereColumn('transaksi_id', 'transaksis.id')->where('jenis', 'masuk'),
        ]);
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->where(fn (Builder $query): Builder => $query->where('nota_muat', 'like', $term)->orWhere('nota_bongkar', 'like', $term)
                ->orWhereHas('kas_uang_jalan.vehicle', fn (Builder $query): Builder => $query->where('nomor_lambung', 'like', $term))
                ->orWhereHas('kas_uang_jalan.rute', fn (Builder $query): Builder => $query->where('nama', 'like', $term)));
        }

        $items = $query->latest('id')->paginate(20);
        $items->getCollection()->each(function (Transaksi $transaction) use ($payments): void {
            $gross = $transaction->kas_uang_jalan?->nominal_transaksi;
            $transaction->setAttribute('mobile_payment', $payments->breakdown('final', $gross === null ? null : (int) $gross, (int) $transaction->mobile_sparepart, (int) $transaction->mobile_tyres));
        });

        return MobileUnloadingNoteResource::collection($items);
    }

    public function show(Request $request, int $transaction, MobileNotePayment $payments): MobileUnloadingNoteResource
    {
        $this->authorize('viewAny', LoadNoteDraft::class);

        $journey = $this->journeys($request)->findOrFail($transaction);
        $detail = $payments->finalDetail($journey);
        $journey->setAttribute('mobile_payment', $detail['payment']);
        $journey->setAttribute('mobile_transfer_destination', $detail['transfer_destination']);

        return new MobileUnloadingNoteResource($journey);
    }

    private function journeys(Request $request): Builder
    {
        return Transaksi::query()->where('void', 0)->whereIn('status', [2, 3])
            ->whereHas('kas_uang_jalan', fn (Builder $query): Builder => $query->where('vendor_id', $request->user()->vendor_id))
            ->with(['kas_uang_jalan:id,vendor_id,vehicle_id,customer_id,rute_id,nomor_uang_jalan,nominal_transaksi',
                'kas_uang_jalan.vehicle:id,nomor_lambung,driver_id,vendor_id', 'kas_uang_jalan.vehicle.driver:id,nama', 'kas_uang_jalan.rute:id,nama', 'kas_uang_jalan.customer:id,nama']);
    }
}
