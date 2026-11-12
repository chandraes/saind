<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Daftar nolam untuk menu Maintenance (Ban Luar dan Aki) di aplikasi mobile:
 * kendaraan milik vendor akun yang tidak berstatus nonaktif.
 */
class MobileMaintenanceVehicleController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);
        $vendorId = (int) $request->user()->vendor_id;
        abort_if($vendorId <= 0, 403);
        $search = trim($filters['search'] ?? '');
        $page = Vehicle::query()->where('vendor_id', $vendorId)->where('status', '!=', 'nonaktif')
            ->when($search !== '', fn ($query) => $query->where('nomor_lambung', 'like', '%'.$search.'%'))
            ->orderBy('nomor_lambung')->orderBy('id')->paginate(25, ['id', 'nomor_lambung', 'nopol', 'status']);

        return response()->json([
            'data' => $page->getCollection()->map(fn (Vehicle $vehicle): array => $vehicle->only(['id', 'nomor_lambung', 'nopol', 'status']))->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }
}
