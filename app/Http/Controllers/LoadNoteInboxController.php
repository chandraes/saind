<?php

namespace App\Http\Controllers;

use App\Models\LoadNoteDraft;
use App\Models\Vendor;
use App\Services\LoadNoteDraftService;
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

    public function show(LoadNoteDraft $note): View
    {
        return view('billing.nota-muat.show', ['note' => $note->load(['vendor:id,nama', 'creator:id,name'])]);
    }

    public function photo(LoadNoteDraft $note, LoadNoteDraftService $service): StreamedResponse
    {
        return $service->photo($note);
    }
}
