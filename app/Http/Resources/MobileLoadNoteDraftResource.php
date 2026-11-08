<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileLoadNoteDraftResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['transfer_destination' => $this->whenHas('mobile_transfer_destination', fn () => $this->mobile_transfer_destination), 'payment' => $this->whenHas('mobile_payment', fn () => $this->mobile_payment), 'id' => $this->id, 'transaction_id' => $this->transaksi_id, 'customer_id' => $this->customer_id, 'rute_id' => $this->rute_id,
            'header_name' => $this->header_name, 'unit_code' => $this->unit_code, 'route_reference' => $this->route_reference, 'date_source' => $this->date_source, 'ticket_number' => $this->ticket_number, 'mine_name' => $this->mine_name,
            'vehicle_driver_name' => $this->whenLoaded('vehicle', fn () => $this->vehicle?->driver?->nama),
            'vehicle_id' => $this->vehicle_id, 'vehicle_number' => $this->vehicle_number,
            'loaded_on' => $this->loaded_on->format('Y-m-d'), 'driver_name' => $this->driver_name,
            'gross_weight' => number_format($this->gross_kg / 1000, 3, '.', ''),
            'tare_weight' => number_format($this->tare_kg / 1000, 3, '.', ''),
            'net_weight' => number_format($this->net_kg / 1000, 3, '.', ''),
            'status' => $this->status, 'notes' => $this->notes, 'has_photo' => true,
            'created_by' => $this->whenLoaded('creator', fn () => ['id' => $this->creator->id, 'name' => $this->creator->name]),
            'created_at' => $this->created_at->toIso8601String()];
    }
}
