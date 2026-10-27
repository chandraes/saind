<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileDatabaseVehicleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = ['id' => $this->id, 'nomor_lambung' => $this->nomor_lambung,
            'nopol' => $this->nopol, 'tipe' => $this->tipe, 'tahun' => $this->tahun, 'status' => $this->status,
            'vendor' => $this->vendor ? ['id' => $this->vendor->id, 'nama' => $this->vendor->nama, 'perusahaan' => $this->vendor->perusahaan] : null];
        if ($request->routeIs('api.v1.database.vehicles.show')) {
            foreach (['nama_stnk', 'no_rangka', 'no_mesin', 'tanggal_pajak_stnk', 'tanggal_kir', 'tanggal_kimper', 'tanggal_sim'] as $field) {
                $data[$field] = $this->resource->getAttribute($field);
            }
        }

        return $data;
    }
}
