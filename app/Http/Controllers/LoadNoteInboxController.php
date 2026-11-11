<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecideLoadNoteRequest;
use App\Http\Requests\UpdateLoadNotePaymentRequest;
use App\Http\Requests\UpdateLoadNoteRequest;
use App\Models\Customer;
use App\Models\LoadNoteDraft;
use App\Models\Rute;
use App\Models\Transaksi;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Services\LoadNoteDraftService;
use App\Services\LoadNotePaymentPreview;
use App\Services\LoadNoteReviewService;
use App\Services\MobileNotePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoadNoteInboxController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $canReview = $request->user()->can('reviewAny', LoadNoteDraft::class);
        abort_if(! $canReview && $request->filled('vendor_id') && (string) $request->input('vendor_id') !== (string) $request->user()->vendor_id, 404);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $query = LoadNoteDraft::with(['vendor:id,nama', 'creator:id,name'])->search($filters['search'] ?? '');
        if (! $canReview) {
            $query->where('vendor_id', $request->user()->vendor_id);
        }
        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        $notes = $query->latest('id')->paginate(25)->appends($filters);
        if ($request->expectsJson()) {
            return response()->json([
                'rows' => view('billing.nota-muat.rows', compact('notes', 'filters'))->render(),
                'next' => $notes->nextPageUrl(),
            ]);
        }

        return view('billing.nota-muat.index', [
            'notes' => $notes,
            'canReview' => $canReview,
            'vendors' => $canReview ? Vendor::select(['id', 'nama'])->orderBy('nama')->get() : collect(),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, LoadNoteDraft $note, LoadNotePaymentPreview $payments, MobileNotePayment $notePayments): View
    {
        abort_unless($request->user()->can('readInbox', $note), 404);
        $canReview = $request->user()->can('reviewAny', LoadNoteDraft::class);
        if ($note->status === 'processed') {
            $transaction = Transaksi::whereKey($note->transaksi_id)
                ->whereHas('kas_uang_jalan', fn ($query) => $query->where('vendor_id', $note->vendor_id))->first();
            $final = $transaction ? $notePayments->finalDetail($transaction) : null;
            $money = $final['payment'] ?? null;
            $destination = $final['transfer_destination'] ?? [];
            $paymentPreview = [
                'is_final' => true, 'is_custom' => false, 'system_transfer' => null,
                'held' => ($money['sparepart_withheld'] ?? 0) + ($money['tyre_withheld'] ?? 0) > 0,
                'transfer' => $money['net_amount'] ?? null, 'gross' => $money['gross_amount'] ?? null,
                'sparepart' => $money['sparepart_withheld'] ?? null, 'ban' => $money['tyre_withheld'] ?? null,
                'driver' => $note->driver_name, 'driver_phone' => null, 'account_source' => 'transaction',
                'recipient' => $destination['recipient_name'] ?? null, 'bank' => $destination['bank'] ?? null,
                'account' => $destination['account_number'] ?? null,
                'warnings' => $money === null ? ['Rincian uang jalan transaksi belum tersedia. Hubungi admin untuk pemeriksaan.'] : [],
            ];
        } else {
            $paymentPreview = $payments->forNote($note);
        }

        return view('billing.nota-muat.show', [
            'paymentPreview' => $paymentPreview,
            'paymentFingerprint' => $payments->confirmationHash($note, $paymentPreview),
            'returnFilters' => $request->only(['search', 'vendor_id', 'page']),
            'note' => $note->load(['vendor:id,nama', 'creator:id,name', 'customer:id,nama', 'rute:id,nama', 'events.actor:id,name']),
            'vehicles' => $canReview ? Vehicle::where('vendor_id', $note->vendor_id)->select(['id', 'nomor_lambung'])->orderBy('nomor_lambung')->get() : collect(),
            'customers' => $canReview ? Customer::where('status', 1)->select(['id', 'nama'])->orderBy('nama')->get() : collect(),
            'rutes' => $canReview ? Rute::select(['id', 'nama'])->orderBy('nama')->get() : collect(),
        ]);
    }

    public function update(UpdateLoadNoteRequest $request, LoadNoteDraft $note, LoadNoteReviewService $service): JsonResponse
    {
        $service->update($request->user(), $note, $request->validated());

        return response()->json(['message' => 'Koreksi nota berhasil disimpan.']);
    }

    public function updatePayment(UpdateLoadNotePaymentRequest $request, LoadNoteDraft $note, LoadNoteReviewService $service): JsonResponse
    {
        $service->updatePayment($request->user(), $note, $request->validated());

        return response()->json(['message' => 'Nominal uang jalan nota berhasil disimpan. Belum ada pencairan uang.']);
    }

    public function decide(DecideLoadNoteRequest $request, LoadNoteDraft $note, LoadNoteReviewService $service): JsonResponse
    {
        $service->decide($request->user(), $note, $request->validated());

        return response()->json(['message' => 'Nota berhasil di-void. Kendaraan dapat mengirim nota baru.']);
    }

    public function photo(Request $request, LoadNoteDraft $note, LoadNoteDraftService $service): StreamedResponse
    {
        abort_unless($request->user()->can('readInbox', $note), 404);

        return $service->photo($note);
    }
}
