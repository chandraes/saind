<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MobileDatabaseVehicleResource;
use App\Http\Resources\MobileVendorResource;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DatabaseController extends Controller
{
    /** @return array{search?: ?string, page?: int, per_page?: int} */
    private function filters(Request $request): array
    {
        return $request->validate(['search' => ['nullable', 'string', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50']]);
    }

    public function vendors(Request $request): AnonymousResourceCollection
    {
        $filters = $this->filters($request);
        $query = Vendor::query()->select(['id', 'nama', 'nickname', 'perusahaan', 'status']);
        if (($filters['search'] ?? '') !== '') {
            $term = '%'.$filters['search'].'%';
            $query->where(fn ($q) => $q->where('nama', 'like', $term)->orWhere('nickname', 'like', $term)->orWhere('perusahaan', 'like', $term));
        }

        return MobileVendorResource::collection($query->orderBy('nama')->orderBy('id')->paginate($filters['per_page'] ?? 20));
    }

    public function vendor(int $vendor): MobileVendorResource
    {
        return new MobileVendorResource(Vendor::query()->withCount('vehicle')->findOrFail($vendor));
    }

    public function vehicles(Request $request): AnonymousResourceCollection
    {
        $filters = $this->filters($request);
        $query = Vehicle::query()->select(['id', 'vendor_id', 'nomor_lambung', 'nopol', 'tipe', 'tahun', 'status'])->with('vendor:id,nama,perusahaan');
        if (($filters['search'] ?? '') !== '') {
            $term = '%'.$filters['search'].'%';
            $query->where(fn ($q) => $q->where('nomor_lambung', 'like', $term)->orWhere('nopol', 'like', $term));
        }

        return MobileDatabaseVehicleResource::collection($query->orderBy('nomor_lambung')->orderBy('id')->paginate($filters['per_page'] ?? 20));
    }

    public function vehicle(int $vehicle): MobileDatabaseVehicleResource
    {
        return new MobileDatabaseVehicleResource(Vehicle::query()->with('vendor:id,nama,perusahaan')->findOrFail($vehicle));
    }
}
