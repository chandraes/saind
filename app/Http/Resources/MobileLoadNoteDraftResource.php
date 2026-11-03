<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileLoadNoteDraftResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'ticket_number' => $this->ticket_number, 'mine_name' => $this->mine_name,
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
