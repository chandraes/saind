<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileVendorResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = ['id' => $this->id, 'nama' => $this->nama, 'nickname' => $this->nickname,
            'perusahaan' => $this->perusahaan, 'status' => $this->status];
        if ($request->routeIs('api.v1.database.vendors.show')) {
            foreach (['tipe', 'jabatan', 'npwp', 'alamat', 'no_hp', 'email', 'bank', 'no_rekening', 'nama_rekening', 'pembayaran'] as $field) {
                $data[$field] = $this->resource->getAttribute($field);
            }
            $data['jumlah_vehicle'] = $this->vehicle_count;
        }

        return $data;
    }
}
