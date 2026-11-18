<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MaintenanceApproval;
use App\Services\MaintenanceAuthorizationQueue;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Otorisasi Maintenance (ban luar, aki, filter & oli mesin) untuk Admin & SU
 * di mobile. Antrian dan pemrosesan memakai class yang sama dengan web.
 */
class MobileMaintenanceAuthorizationController extends Controller
{
    private const DONE = [
        'tire' => 'Otorisasi berhasil. Log Ban diperbarui.',
        'battery' => 'Otorisasi berhasil. Histori aki dan pembayaran telah dicatat.',
        'filter-oil' => 'Otorisasi berhasil. Histori penggantian filter & oli mesin telah dicatat.',
    ];

    private const REJECTED = [
        'tire' => 'Invoice penggantian ban berhasil ditolak/dibatalkan.',
        'battery' => 'Invoice penggantian aki ditolak. Saldo tidak dipotong.',
        'filter-oil' => 'Penggantian filter & oli mesin ditolak.',
    ];

    public function __construct(private MaintenanceAuthorizationQueue $queue) {}

    public function index(Request $request, string $type): JsonResponse
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);
        $page = $this->queue->pending($type, $data['search'] ?? '')->paginate(20);
        $info = $this->queue->describe($type, $page->getCollection());

        return response()->json([
            'data' => $page->getCollection()->map(fn ($invoice) => $this->queue->row($type, $invoice, $info[$invoice->id]))->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }

    public function show(string $type, int $id): JsonResponse
    {
        return response()->json(['data' => $this->queue->detail($type, $id)]);
    }

    public function approve(Request $request, string $type, int $id, MaintenanceApproval $approval): JsonResponse
    {
        try {
            [$invoice, $cash] = $approval->approve($type, $id, $request->user(), $this->fingerprint($request));
        } catch (ValidationException|ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            // KasBesarService melempar Exception biasa berpesan untuk pengguna (mis. saldo kurang).
            $userFacing = get_class($exception) === Exception::class;
            if (! $userFacing) {
                report($exception);
            }

            return response()->json(['message' => $userFacing ? $exception->getMessage() : 'Otorisasi gagal. Periksa saldo pembayaran dan coba kembali.'], 422);
        }

        return response()->json(['message' => self::DONE[$type]] + ($approval->notify($type, $invoice, $cash) ? [] : [
            'warning' => 'Sebagian notifikasi WA belum berhasil dikirim. Transaksi sudah tersimpan; jangan ulangi persetujuan.',
        ]));
    }

    public function reject(Request $request, string $type, int $id, MaintenanceApproval $approval): JsonResponse
    {
        $approval->reject($type, $id, $request->user(), $this->fingerprint($request));

        return response()->json(['message' => self::REJECTED[$type]]);
    }

    /** Sidik rincian dari layar detail; wajib agar yang disetujui sama dengan yang ditinjau. */
    private function fingerprint(Request $request): string
    {
        return $request->validate(['fingerprint' => ['required', 'string', 'size:64']])['fingerprint'];
    }
}
