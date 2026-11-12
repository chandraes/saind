<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Support\MobileVendorScope;
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
        $search = trim($filters['search'] ?? '');
        // Akun vendor: kendaraannya sendiri; operasional: seluruh vendor atau filter vendor_id.
        $page = MobileVendorScope::apply(Vehicle::query(), $request)->with('vendor:id,nama,nickname')->where('status', '!=', 'nonaktif')
            ->when($search !== '', fn ($query) => $query->where('nomor_lambung', 'like', '%'.$search.'%'))
            ->orderBy('nomor_lambung')->orderBy('id')->paginate(25, ['id', 'vendor_id', 'nomor_lambung', 'nopol', 'status']);

        return response()->json([
            'data' => $page->getCollection()->map(fn (Vehicle $vehicle): array => $vehicle->only(['id', 'nomor_lambung', 'nopol', 'status'])
                + ['vendor_name' => $vehicle->vendor?->nickname ?: $vehicle->vendor?->nama])->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }
}
