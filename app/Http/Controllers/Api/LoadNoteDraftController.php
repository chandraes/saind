<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InspectLoadNoteRequest;
use App\Http\Requests\StoreLoadNoteDraftRequest;
use App\Http\Requests\StoreScannedLoadNoteRequest;
use App\Http\Resources\MobileLoadNoteDraftResource;
use App\Http\Resources\MobileVehicleResource;
use App\Models\LoadNoteDraft;
use App\Models\Vehicle;
use App\Services\LoadNoteDraftService;
use App\Services\LoadNoteMatcher;
use App\Services\MobileNotePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoadNoteDraftController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', LoadNoteDraft::class);
        $filters = $request->validate(['status' => ['sometimes', 'string', 'in:all,unprocessed,draft,rejected,cancelled,processed'], 'search' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);
        $query = LoadNoteDraft::where('vendor_id', $request->user()->vendor_id)->with(['creator:id,name', 'vehicle' => fn ($query) => $query
            ->where('vendor_id', $request->user()->vendor_id)->select('id', 'driver_id')->with('driver:id,nama')]);
        $query->search($filters['search'] ?? '');
        if (($filters['status'] ?? 'all') === 'unprocessed') {
            $query->whereIn('status', ['draft', 'rejected', 'cancelled']);
        } elseif (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }

        return MobileLoadNoteDraftResource::collection($query->latest('id')->paginate(20));
    }

    public function vehicles(Request $request): AnonymousResourceCollection
    {
        $this->authorize('create', LoadNoteDraft::class);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);
        $query = Vehicle::select(['id', 'nomor_lambung', 'vendor_id', 'status'])->where('vendor_id', $request->user()->vendor_id);
        if (($filters['search'] ?? '') !== '') {
            $query->where('nomor_lambung', 'like', '%'.$filters['search'].'%');
        }

        return MobileVehicleResource::collection($query->orderBy('nomor_lambung')->orderBy('id')->paginate(25));
    }

    public function inspect(InspectLoadNoteRequest $request, LoadNoteMatcher $matcher): JsonResponse
    {
        return response()->json(['data' => $matcher->inspect($request->user(), $request->validated()),
            'decision' => ['accepted' => true, 'action' => 'confirm_vehicle']]);
    }

    public function storeScanned(StoreScannedLoadNoteRequest $request, LoadNoteDraftService $service): JsonResponse
    {
        [$draft, $created] = $service->store($request->user(), $request->validated(), $request->file('photo'));

        return (new MobileLoadNoteDraftResource($draft))->response()->setStatusCode($created ? 201 : 200);
    }

    public function store(StoreLoadNoteDraftRequest $request, LoadNoteDraftService $service): JsonResponse
    {
        [$draft, $created] = $service->store($request->user(), $request->validated(), $request->file('photo'));

        return (new MobileLoadNoteDraftResource($draft))->response()->setStatusCode($created ? 201 : 200);
    }

    private function draft(Request $request, int $note): LoadNoteDraft
    {
        $this->authorize('viewAny', LoadNoteDraft::class);

        return LoadNoteDraft::where('vendor_id', $request->user()->vendor_id)->with('creator:id,name')->findOrFail($note);
    }

    public function show(Request $request, int $note, MobileNotePayment $payments): MobileLoadNoteDraftResource
    {
        $draft = $this->draft($request, $note);
        $detail = $payments->estimateDetail($draft);
        $draft->setAttribute('mobile_payment', $detail['payment']);
        $draft->setAttribute('mobile_transfer_destination', $detail['transfer_destination']);

        return new MobileLoadNoteDraftResource($draft);
    }

    public function photo(Request $request, int $note, LoadNoteDraftService $service): StreamedResponse
    {
        return $service->photo($this->draft($request, $note));
    }
}
