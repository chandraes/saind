<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileVehicleResource extends JsonResource
{
    /** @return array{id: int, nomor_lambung: string, vendor_id: int, status: string} */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'nomor_lambung' => $this->nomor_lambung,
            'vendor_id' => $this->vendor_id, 'status' => $this->status];
    }
}
