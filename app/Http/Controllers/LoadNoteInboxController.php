<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecideLoadNoteRequest;
use App\Http\Requests\UpdateLoadNoteRequest;
use App\Models\Customer;
use App\Models\LoadNoteDraft;
use App\Models\Rute;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Services\LoadNoteDraftService;
use App\Services\LoadNoteReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoadNoteInboxController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $query = LoadNoteDraft::with(['vendor:id,nama', 'creator:id,name'])->search($filters['search'] ?? '');
        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        return view('billing.nota-muat.index', [
            'notes' => $query->latest('id')->paginate(25)->appends($filters),
            'vendors' => Vendor::select(['id', 'nama'])->orderBy('nama')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, LoadNoteDraft $note): View
    {
        return view('billing.nota-muat.show', [
            'returnFilters' => $request->only(['search', 'vendor_id', 'page']),
            'note' => $note->load(['vendor:id,nama', 'creator:id,name', 'customer:id,nama', 'rute:id,nama', 'events.actor:id,name']),
            'vehicles' => Vehicle::where('vendor_id', $note->vendor_id)->select(['id', 'nomor_lambung'])->orderBy('nomor_lambung')->get(),
            'customers' => Customer::where('status', 1)->select(['id', 'nama'])->orderBy('nama')->get(),
            'rutes' => Rute::select(['id', 'nama'])->orderBy('nama')->get(),
        ]);
    }

    public function update(UpdateLoadNoteRequest $request, LoadNoteDraft $note, LoadNoteReviewService $service): JsonResponse
    {
        $service->update($request->user(), $note, $request->validated());

        return response()->json(['message' => 'Koreksi nota berhasil disimpan.']);
    }

    public function decide(DecideLoadNoteRequest $request, LoadNoteDraft $note, LoadNoteReviewService $service): JsonResponse
    {
        $updated = $service->decide($request->user(), $note, $request->validated());

        return response()->json(['message' => 'Nota berhasil '.($updated->status === 'rejected' ? 'ditolak.' : 'dibatalkan.')]);
    }

    public function photo(LoadNoteDraft $note, LoadNoteDraftService $service): StreamedResponse
    {
        return $service->photo($note);
    }
}
