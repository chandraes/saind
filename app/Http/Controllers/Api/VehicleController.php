<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\MobileVehicleResource;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VehicleController extends BaseController
{
    private function vehicles(Request $request): Builder
    {
        $query = Vehicle::query()->select(['id', 'nomor_lambung', 'vendor_id', 'status']);
        if ($request->user()->role === 'vendor-operational') {
            $query->where('vendor_id', $request->user()->vendor_id);
        }

        return $query;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $query = $this->vehicles($request);
        if (! empty($data['search'])) {
            $query->where('nomor_lambung', 'like', '%'.$data['search'].'%');
        }

        return MobileVehicleResource::collection($query->orderBy('nomor_lambung')->orderBy('id')->paginate($data['per_page'] ?? 25));
    }

    public function show(Request $request, int $vehicle): MobileVehicleResource
    {
        return new MobileVehicleResource($this->vehicles($request)->findOrFail($vehicle));
    }
}
